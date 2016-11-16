<?php

namespace BlueDot\Database\Simple;

use BlueDot\Database\AbstractStatementExecution;
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\Entity;

class SimpleStatementExecution extends AbstractStatementExecution
{
    /**
     * @return Entity|EntityCollection
     */
    public function execute()
    {
        $connection = $this->argumentsBag->get('connection');
        $specificConfiguration = $this->argumentsBag->get('specific_configuration');
        $parameters = $this->argumentsBag->get('parameters');

        $stmt = $connection->prepare($specificConfiguration->getStatement());

        foreach ($parameters as $parameter) {
            foreach ($parameter as $key => $value) {
                $stmt->bindValue(
                    $key,
                    $value,
                    ($this->isValueResolvable($value)) ? $this->resolveParameterValue($value) : \PDO::PARAM_STR);
            }
        }

        foreach ($parameters as $parameter) {
            $stmt->execute($parameter);
        }
    }
}