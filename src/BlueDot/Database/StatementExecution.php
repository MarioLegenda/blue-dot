<?php

namespace BlueDot\Database;

use BlueDot\Database\Scenario\Scenario;
use BlueDot\Configuration\Scenario\ScenarioConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Exception\QueryException;
use BlueDot\Database\Parameter;

class StatementExecution
{
    /**
     * @var \PDO $connection
     */
    private $connection;
    /**
     * @var \PDOStatement $statement
     */
    private $pdoStatement;
    /**
     * @var Scenario $scenario
     */
    private $scenario;
    /**
     * @param mixed $scenario
     */
    public function __construct($scenario)
    {
        $this->connection = $scenario->get('connection');
        $this->scenario = $scenario;
    }
    /**
     * @return $this
     * @throws \BlueDot\Exception\CommonInternalException
     */
    public function execute() : StatementExecution
    {
        if ($this->scenario->get('type') === 'simple') {

            $configuration = $this->scenario->get('configuration');

            $this->pdoStatement = $this->connection->prepare($configuration->get('sql'));

            if ($configuration->get('sql_type') !== 'table' and $configuration->get('sql_type') !== 'database') {
                if ($this->scenario->get('configuration')->has('parameters')) {
                    $this->bindParameters($this->scenario->get('user_parameters'));
                }
            }

            $this->realExecute();

            return $this;
        }

        if ($this->scenario->get('type') === 'scenario') {
            $configuration = $this->scenario->get('configuration');

            $useScenarious = $configuration->findConfigurationInUseOption();

            foreach ($useScenarious as $useScenario) {
                $parameters = $this->scenario->get('user_parameters');

                $this->pdoStatement = $this->connection->prepare($useScenario->get('sql'));

                if (!empty($parameters)) {
                    $parameters = $parameters[$useScenario->get('statement_name')];

                    $this->bindParameters($parameters);
                }

                $entity = $this->realExecute()->getInternalResult($useScenario);

                $useScenario->add('executed', true);

                $this->scenario->get('report')->add($useScenario->get('resolved_name'), $entity);
            }

            foreach ($configuration as $scenario) {
                if ($scenario->has('executed')) {
                    if ($scenario->get('executed') === true) {
                        continue;
                    }
                }

                $this->pdoStatement = $this->connection->prepare($scenario->get('sql'));

                if ($scenario->has('use_option')) {
                    $useOption = $scenario->get('use_option');
                    $resolvedOptionName = 'scenario.'.$scenario->get('scenario_name').'.'.$useOption->getName();

                    $report = $this->scenario->get('report');

                    if (!$report->has($resolvedOptionName)) {
                        throw new QueryException('An internal exception occurred. Please, contact whitepostmail@gmail.com');
                    }

                    $entity = $report->get($resolvedOptionName);

                    $this->connection->prepare($scenario->get('sql'));

                    $parameters = Parameter::entityToParameter($entity, $useOption->getValues());

                    if (!$parameters instanceof ParameterCollection) {
                        throw new QueryException('Some values from \'use\' option select query are missing for '.$scenario->get('resolved_name'));
                    }

                    $this->bindParameters($parameters);
                }

                $this->realExecute();
            }
        }
    }

    public function getResult()
    {
        $configuration = $this->scenario->get('configuration');

        if ($configuration->get('sql_type') === 'select') {
            $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) === 1) {
                return new Entity($result[0]);
            }

            $resultCollection = new EntityCollection();

            foreach ($result as $res) {
                $resultCollection->add(new Entity($res));
            }

            return $resultCollection;
        }
    }

    private function getInternalResult(ScenarioConfiguration $scenario)
    {
        if ($scenario->get('sql_type') === 'select') {
            $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) === 1) {
                return new Entity($result[0]);
            }

            $resultCollection = new EntityCollection();

            foreach ($result as $res) {
                $resultCollection->add(new Entity($res));
            }

            return $resultCollection;
        }
    }

    private function realExecute() : StatementExecution
    {
        $this->pdoStatement->execute();

        return $this;
    }

    private function bindParameters(ParameterCollectionInterface $parameters)
    {
        foreach ($parameters as $key => $parameter) {
            if ($parameters->isMultipleValueParameter($key)) {
                foreach ($parameter as $param) {
                    $this->pdoStatement->bindValue(
                        $param->getKey(),
                        $param->getValue(),
                        $param->getType()
                    );
                }
            } else if ($parameter instanceof Parameter) {
                $this->pdoStatement->bindValue(
                    $parameter->getKey(),
                    $parameter->getValue(),
                    $parameter->getType()
                );
            }
        }
    }
}