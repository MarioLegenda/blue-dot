<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Scenario\Scenario;
use BlueDot\Database\Scenario\ScenarioCollection;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;

class StatementExecution
{
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
        $this->scenario = $scenario;
    }

    public function execute() : StatementExecution
    {
        if ($this->scenario->get('type') === 'simple') {
            $this->realExecute($this->scenario);

            return $this;
        }

        if ($this->scenario->get('type') === 'scenario') {
            $configuration = $this->scenario->get('configuration');

            foreach ($configuration as $scenario) {
                die("kreten");
            }
        }
    }

    private function realExecute(StorageInterface $scenario) : StatementExecution
    {
        $connection = $scenario->get('connection');
        $configuration = $scenario->get('configuration');
        $statementType = $configuration->get('type');
        $sql = $scenario->get('configuration')->get('sql');

        $this->pdoStatement = $connection->prepare($sql);

        if ($statementType !== 'table' and $statementType !== 'database') {

            if ($scenario->has('user_parameters')) {
                $this->bindParameters($scenario->get('user_parameters'));
            }
        }

        $this->pdoStatement->execute();

        return $this;
    }

    public function getResult()
    {
        $configuration = $this->scenario->get('configuration');

        if ($configuration->get('type') === 'select') {
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