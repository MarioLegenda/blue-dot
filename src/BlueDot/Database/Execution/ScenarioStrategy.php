<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\QueryException;

class ScenarioStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var ArgumentBag $statements
     */
    private $statements;
    /**
     * @var array $resultReport
     */
    private $resultReport = array();
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

        $this->connection->getConnection()->beginTransaction();

        $this->statements = $this->statement->get('statements');

        foreach ($this->statements as $statement) {
            try {
                if ($statement->has('use_option')) {
                    $useOption = $statement->get('use_option');
                    $useStatement = $this->statements->get($statement->get('scenario_name').'.'.$useOption->getName());

                    if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                        $this->singleStatementRecursiveExecution($useStatement);
                    }
                }

                $this->realSingleStatementExecution($statement);
            } catch (\PDOException $e) {
                throw new QueryException('A PDOException has been thrown for statement '.$statement->get('resolved_statement_name').' with message \''.$e->getMessage().'\'');
            }
        }

        $this->connection->getConnection()->commit();

        return $this;
    }

    public function getResult() : StorageInterface
    {

    }

    private function realSingleStatementExecution(ArgumentBag $statement)
    {
        $this->pdoStatement = $this->connection->getConnection()->prepare($statement->get('sql'));

        if ($statement->has('use_option')) {
            $this->bindUseOptionParameters($statement);
        }

        if ($statement->has('parameters')) {
            $parameters = $statement->get('parameters');
            $this->bindParameterCollection($parameters);
        }

        $this->pdoStatement->execute();

        if ($statement->get('sql_type') === 'select') {
            $this->saveResult($statement);
        } else if ($statement->get('sql_type') === 'insert') {
            $this->saveLastInsertId($statement);
        }

        var_dump($this->resultReport);
        die();
    }

    private function singleStatementRecursiveExecution(ArgumentBag $statement)
    {
        if ($statement->has('use_option')) {
            $useOption = $statement->get('use_option');
            $useStatement = $this->statements->get($statement->get('statement_name').'.'.$useOption->getName());

            if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                $this->realSingleStatementExecution($useStatement);
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
            throw new CommonInternalException('\'use\' option '.$optionStatementName.' has not been executed but it should have been. This could be a bug so please, contact whitepostmail@gmail.com or post an issue on https://github.com/MarioLegenda/blue-dot');
        }

        $entity = $this->resultReport->get($optionStatementName);
        $parameterCollection = new ParameterCollection();

        foreach ($useOptionValues as $key => $value) {
            $exploded = explode('.', $key);

            $columnName = (array_key_exists(1, $exploded)) ? $exploded[1] : $exploded[0];

            if (!$entity->has($columnName)) {
                throw new QueryException('Selected entity for statement '.$optionStatementName.' does not contain a column '.$valueName);
            }

            $entityValue = $entity->get($columnName);

            $parameterCollection->addParameter(new Parameter($value, $entityValue));
        }

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

    private function saveResult(StorageInterface $statement)
    {
        if (!$this->resultReport instanceof ArgumentBag) {
            $this->resultReport = new ArgumentBag();
        }

        $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

        $this->resultReport->add($statement->get('resolved_statement_name'), $this->createEntity($result));
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

    private function saveLastInsertId(ArgumentBag $statement)
    {
        if (!$this->resultReport instanceof ArgumentBag) {
            $this->resultReport = new ArgumentBag();
        }

        $this->resultReport->add($statement->get('resolved_statement_name'), $this->statement->lastInsertId());
    }
}