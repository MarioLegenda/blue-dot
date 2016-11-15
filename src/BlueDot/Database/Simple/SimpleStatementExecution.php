<?php

namespace BlueDot\Database\Simple;

use BlueDot\Cache\Report;
use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Configuration\MainConfiguration;
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\Entity;

class SimpleStatementExecution
{
    /**
     * @var \PDO $connection
     */
    private $connection;
    /**
     * @var ConfigurationInterface $mainConfiguration
     */
    private $mainConfiguration;
    /**
     * @var ConfigurationInterface $specificConfiguration
     */
    private $specificConfiguration;
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @var Report $report
     */
    private $report;
    /**
     * @param string $name
     * @param \PDO $connection
     * @param MainConfiguration $configuration
     * @param mixed $parameters
     * @param Report $report
     */
    public function __construct(string $name, \PDO $connection, MainConfiguration $configuration, $parameters = null, Report $report)
    {
        $this->connection = $connection;
        $this->mainConfiguration = $configuration;
        $this->specificConfiguration = $this->mainConfiguration->findSimpleByName($name);
        $this->parameters = $parameters;
        $this->report = $report;
    }
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

    private function resolveParameterValue($value)
    {
        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }

        if (is_string($value)) {
            return \PDO::PARAM_STR;
        }

        if ($value === null) {
            return \PDO::PARAM_NULL;
        }

        if (is_int($value)) {
            return \PDO::PARAM_INT;
        }
    }

    private function isValueResolvable($value) : bool
    {
        return is_bool($value) or is_string($value) or $value === null or is_int($value);
    }
}