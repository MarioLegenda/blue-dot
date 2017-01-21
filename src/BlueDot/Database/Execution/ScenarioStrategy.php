<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Exception\BlueDotRuntimeException;

class ScenarioStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var ArgumentBag $statements
     */
    private $statements;
    /**
     * @var StorageInterface $entity
     */
    private $entity;
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface
    {
        $this->connection->connect();

        $rootConfig = $this->statement->get('root_config');

        if ($rootConfig->get('atomic') === true) {
            $this->connection->getConnection()->beginTransaction();
        }

        $this->statements = $this->statement->get('statements');

        foreach ($this->statements as $statement) {
            if ($statement->has('multi_insert')) {
                if ($statement->has('foreign_key') or $statement->has('use_option')) {
                    throw new BlueDotRuntimeException('If you provide a statement with multiple parameters for a multi insert, then that statement cannot have \'use\' or \'foreign_key\' options. Statement: '.$statement->get('resolved_statement_name'));
                }
            }

            try {
                if ($statement->has('foreign_key')) {
                    $foreignKey = $statement->get('foreign_key');
                    $foreignKeyStatement = $this->statements->get($statement->get('scenario_name').'.'.$foreignKey->getName());

                    if (!$this->resultReport->has($foreignKeyStatement->get('resolved_statement_name'))) {
                        $this->singleStatementRecursiveExecution($foreignKeyStatement);
                    }
                }

                if ($statement->has('use_option')) {
                    $useOption = $statement->get('use_option');
                    $useStatement = $this->statements->get($statement->get('scenario_name').'.'.$useOption->getName());

                    if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                        $this->singleStatementRecursiveExecution($useStatement);
                    }
                }

                $this->realSingleStatementExecution($statement);
            } catch (\PDOException $e) {
                throw new BlueDotRuntimeException('A PDOException has been thrown for statement '.$statement->get('resolved_statement_name').' with message \''.$e->getMessage().'\'');
            }
        }

        if ($rootConfig->get('atomic') === true) {
            $this->connection->getConnection()->commit();
        }

        return $this;
    }

    public function getResult() : StorageInterface
    {
        $returnEntities = $this->statement->get('root_config')->get('return_entity')->getAllReturnData();
        $scenarioName = $this->statement->get('root_config')->get('scenario_name');

        $entity = new Entity();

        foreach ($returnEntities as $returnEntity) {
            $statementName = $returnEntity->getStatementName();
            $resolvedStatementName = 'scenario.'.$scenarioName.'.'.$statementName;

            if ($this->resultReport->has($resolvedStatementName)) {
                $resultEntity = $this->resultReport->get($resolvedStatementName);

                if (!$resultEntity instanceof $resultEntity) {
                    throw new BlueDotRuntimeException('Return result specified in \'return_entity\' has to be a select sql type for '.$resolvedStatementName);
                }

                if (!$returnEntity->hasColumnName()) {
                    $entity->add($statementName, $resultEntity);

                    continue;
                }

                $resultEntityColumnName = $returnEntity->getColumnName();
                $resultValue = $resultEntity->get($resultEntityColumnName);

                if ($returnEntity->hasAlias()) {
                    $alias = $returnEntity->getAlias();

                    $entity->add($alias, $resultValue);

                    continue;
                }

                $entity->add($resultEntityColumnName, $resultValue);
            }
        }

        return $entity;
    }

    private function realSingleStatementExecution(ArgumentBag $statement)
    {
        $this->pdoStatement = $this->connection->getConnection()->prepare($statement->get('sql'));

        if ($statement->has('foreign_key')) {
            $this->bindForeignKeyParameters($statement);
        }

        if ($statement->has('use_option')) {
            $this->bindUseOptionParameters($statement);
        }

        if (!$statement->has('multi_insert')) {
            if ($statement->has('parameters')) {
                $parameters = $statement->get('parameters');
                $this->bindParameterCollection($parameters);
            }

            $this->pdoStatement->execute();
        } else if ($statement->has('multi_insert')) {
            $parameters = $statement->get('parameters');

            foreach ($parameters as $parameterCollection) {
                $this->bindParameterCollection($parameterCollection);
                $this->pdoStatement->execute();
            }
        }

        if ($statement->get('sql_type') === 'select') {
            $this->saveResult($statement);
        } else if ($statement->get('sql_type') === 'insert') {
            $this->saveLastInsertId($statement);
        }
    }

    private function singleStatementRecursiveExecution(ArgumentBag $statement)
    {
        if ($statement->has('foreign_key')) {
            $foreignKey = $statement->get('foreign_key');
            $foreignKeyStatement = $this->statements->get($statement->get('scenario_name').'.'.$foreignKey->getName());

            if (!$this->resultReport->has($foreignKeyStatement->get('resolved_statement_name'))) {
                $this->singleStatementRecursiveExecution($foreignKeyStatement);
            }
        }

        if ($statement->has('use_option')) {
            $useOption = $statement->get('use_option');
            $useStatement = $this->statements->get($statement->get('statement_name').'.'.$useOption->getName());

            if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                $this->singleStatementRecursiveExecution($useStatement);
            }
        }

        $this->realSingleStatementExecution($statement);
    }

    private function bindUseOptionParameters(ArgumentBag $statement)
    {
        $useOption = $statement->get('use_option');
        $useOptionValues = $useOption->getValues();
        $optionStatementName = $statement->get('scenario_name').'.'.$useOption->getName();

        if (!$this->resultReport->has($optionStatementName)) {
            throw new BlueDotRuntimeException('\'use\' option '.$optionStatementName.' has not been executed but it should have been. This could be a bug so please, contact whitepostmail@gmail.com or post an issue on https://github.com/MarioLegenda/blue-dot');
        }

        $entity = $this->resultReport->get($optionStatementName);
        $parameterCollection = new ParameterCollection();

        foreach ($useOptionValues as $key => $value) {
            $exploded = explode('.', $key);

            $columnName = (array_key_exists(1, $exploded)) ? $exploded[1] : $exploded[0];

            if ($entity instanceof EntityCollection) {
                throw new BlueDotRuntimeException('Invalid entity selection for '.$statement->get('resolved_statement_name').'. You can only select a single result in a \'use\' option. Multiple results given');
            }

            if (!$entity->has($columnName)) {
                throw new BlueDotRuntimeException('Selected entity for statement '.$optionStatementName.' does not contain a column \''.$columnName.'\'. If you specify a column in the return_entity configuration, then that column has to be fetched from the database');
            }

            $entityValue = $entity->get($columnName);

            $parameterCollection->addParameter(new Parameter($value, $entityValue));
        }

        $this->bindParameterCollection($parameterCollection);
    }

    private function bindForeignKeyParameters(ArgumentBag $statement)
    {
        $foreignKey = $statement->get('foreign_key');
        $bindTo = $foreignKey->getBindTo();
        $foreignKeyStatementName = $statement->get('scenario_name').'.'.$foreignKey->getName();

        if (!$this->resultReport->has($foreignKeyStatementName)) {
            throw new BlueDotRuntimeException('\'foreign_key\' option '.$foreignKeyStatementName.' has not been executed but it should have been. This could be a bug so please, contact whitepostmail@gmail.com or post an issue on https://github.com/MarioLegenda/blue-dot');
        }


        $insertStatements = $this->resultReport->get($foreignKeyStatementName);
        $statementLastInsertId = $insertStatements[count($insertStatements) - 1];

        $parameterCollection = new ParameterCollection();

        $parameterCollection->addParameter(new Parameter($bindTo, (int) $statementLastInsertId->get('last_insert_id')));

        $this->bindParameterCollection($parameterCollection);
    }

    private function bindParameterCollection(ParameterCollection $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->bindSingleParameter($parameter);
        }
    }

    private function bindSingleParameter(Parameter $parameter)
    {
        $this->pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }

    private function createEntity(array $result) : StorageInterface
    {
        $resultCount = count($result);

        switch ($resultCount) {
            case 0:
                $this->entity = new Entity();

                return $this->entity;
            case 1:
                $this->entity = new Entity($result[0]);

                return $this->entity;
            default:
                $this->entity = new EntityCollection($result);

                return $this->entity;
        }
    }

    private function saveResult(StorageInterface $statement)
    {
        if (!$this->resultReport instanceof ArgumentBag) {
            $this->resultReport = new ArgumentBag();
        }

        $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$this->resultReport->has($statement->get('resolved_statement_name'))) {
            $this->resultReport->add($statement->get('resolved_statement_name'), $this->createEntity($result));
        }
    }

    private function saveLastInsertId(ArgumentBag $statement)
    {
        if (!$this->resultReport instanceof ArgumentBag) {
            $this->resultReport = new ArgumentBag();
        }

        $lastInsertIdStorage = new ArgumentBag();
        $lastInsertIdStorage->add('last_insert_id', $this->connection->getConnection()->lastInsertId());

        $this->resultReport->append($statement->get('resolved_statement_name'), $lastInsertIdStorage);
    }
}