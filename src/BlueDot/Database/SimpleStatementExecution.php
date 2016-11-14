<?php

namespace BlueDot\Database;

use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Exception\QueryException;
use BlueDot\Result\Result;
use BlueDot\Result\ResultCollection;

class SimpleStatementExecution
{
    /**
     * @var \PDO $connection
     */
    private $connection;
    /**
     * @var ConfigurationInterface $configuration
     */
    private $configuration;
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @param \PDO $connection
     * @param ConfigurationInterface $configuration
     * @param array $parameters
     */
    public function __construct(\PDO $connection, ConfigurationInterface $configuration, $parameters = null)
    {
        $this->connection = $connection;
        $this->configuration = $configuration;

        if ($parameters !== null) {
            $configParamters = $this->configuration->getParameters();

            $givenParameters = ($parameters instanceof ParameterCollectionInterface) ? $parameters->getBindingKeys() : array_keys($parameters);

            if (!empty(array_diff($configParamters, $givenParameters))) {
                throw new QueryException('Given parameters and parameters in configuration are not equal for '.$this->configuration->getType().'.'.$this->configuration->getName());
            }

            if ($parameters instanceof ParameterCollectionInterface) {
                $this->parameters = $parameters->toArray();
            } else {
                $this->parameters = array($parameters);
            }
        }
    }

    public function execute()
    {
        $stmt = $this->connection->prepare($this->configuration->getStatement());


        foreach ($this->parameters as $parameter) {
            $stmt->execute($parameter);
        }

        if ($this->configuration->getType() === 'select') {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) === 1) {
                return new Result($result[0]);
            }

            $resultCollection = new ResultCollection();

            foreach ($result as $res) {
                $resultCollection->add(new Result($res));
            }

            return $resultCollection;
        }
    }
}