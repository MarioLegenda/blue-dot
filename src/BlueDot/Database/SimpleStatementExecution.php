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
    public function __construct(\PDO $connection, ConfigurationInterface $configuration, array $parameters = null)
    {
        $this->connection = $connection;
        $this->configuration = $configuration;
        $this->parameters = $parameters;
    }

    public function execute()
    {
        $stmt = $this->connection->prepare($this->configuration->getStatement());

        $configParamters = $this->configuration->getParameters();
        $givenParameters = array_keys($this->parameters);

        if (!empty(array_diff($configParamters, $givenParameters))) {
            throw new QueryException('Given parameters and parameters in configuration are not equal');
        }

        foreach ($this->parameters as $key => $parameter) {
            $stmt->bindValue(':'.$key, $parameter);
        }

        $stmt->execute();

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