<?php

namespace BlueDot\Database;

use BlueDot\Database\Scenario\Scenario;
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
     * @param Scenario $scenario
     */
    public function __construct(Scenario $scenario)
    {
        $this->scenario = $scenario;
    }

    public function execute() : StatementExecution
    {
        $connection = $this->scenario->getArgumentBag()->get('connection');
        $statementType = $this->scenario->getArgumentBag()->get('specific_configuration')->getType();
        $sql = $this->scenario->getSql();

        $this->pdoStatement = $connection->prepare($sql);

        if ($statementType !== 'table' and $statementType !== 'database') {
            if ($this->scenario->hasParameters()) {
                $this->bindParameters($this->scenario->getParameters());
            }
        }

        $this->pdoStatement->execute();

        return $this;
    }

    public function getResult()
    {
        $specificConfiguration = $this->scenario->getArgumentBag()->get('specific_configuration');

        if ($specificConfiguration->getType() === 'select') {
            $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) === 1) {
                return new Entity($result[0]);
            }

            $resultCollection = new EntityCollection();

            foreach ($result as $res) {
                $resultCollection->add(new Entity($res));
            }

            return $resultCollection;        }
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