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
        $stmt = $this->connection->prepare($this->specificConfiguration->getStatement());

        foreach ($this->parameters as $parameter) {
            foreach ($parameter as $key => $value) {
                $stmt->bindValue(
                    $key,
                    $value,
                    ($this->isValueResolvable($value)) ? $this->resolveParameterValue($value) : \PDO::PARAM_STR);
            }
        }

        foreach ($this->parameters as $parameter) {
            $stmt->execute($parameter);
        }

        if ($this->specificConfiguration->getType() === 'select') {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
}