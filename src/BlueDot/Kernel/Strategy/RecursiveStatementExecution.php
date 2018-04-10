<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Scenario\ForeignKey;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\UseOption;
use BlueDot\Configuration\Flow\Simple\Enum\InsertSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\UpdateSqlType;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Result\Scenario\KernelResultCollection;

use BlueDot\Kernel\Parameter\Parameter;
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\MultipleInsertQueryResult;
use BlueDot\Result\ResultReportContext;
use BlueDot\Result\SelectQueryResult;

class RecursiveStatementExecution
{
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var Metadata $statement
     */
    private $statement;
    /**
     * @var KernelResultCollection $results
     */
    private $results;
    /**
     * RecursiveStatementExecution constructor.
     * @param Metadata $statement
     * @param KernelResultCollection $results
     * @param Connection $connection
     */
    public function __construct(
        Metadata $statement,
        KernelResultCollection $results,
        Connection $connection
    ) {
        $this->statement = $statement;
        $this->connection = $connection;
        $this->results = $results;
    }
    /**
     * @param array $metadataList
     */
    public function execute(array $metadataList)
    {
        $this->executeReal($metadataList);
    }
    /**
     * @return array|object
     */
    public function getResult()
    {
        $resolvedStatementName = $this->statement->getSingleScenarioName();

        return $this->results->get($resolvedStatementName);
    }
    /**
     * @param Parameter $parameter
     * @param \PDOStatement $pdoStatement
     */
    private function bindSingleParameter(Parameter $parameter, \PDOStatement $pdoStatement)
    {
        $pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }
    /**
     * @param array $metadataList
     * @return $this
     */
    private function executeReal(array $metadataList)
    {
        /** @var ForeignKey $foreignKey */
        $foreignKeys = $this->statement->getForeignKeys();
        /** @var TypeInterface $sqlType */
        $sqlType = $this->statement->getSqlType();

        $resolvedStatementName = $this->statement->getSingleScenarioName();

        try {
            if (!empty($foreignKeys)) {
                /** @var ForeignKey $foreignKey */
                foreach ($foreignKeys as $foreignKey) {
                    $this->executeForeignKeysFirst(
                        $foreignKey,
                        $sqlType,
                        $resolvedStatementName,
                        $metadataList
                    );
                }
            }

            $userParametersType = $this->statement->getUserParametersType();

            if ($userParametersType instanceof MultipleParametersType) {
                $userParameters = $this->statement->getUserParameters();

                foreach ($userParameters as $parameter) {
                    $this->runIndividualStatement($metadataList, $parameter);
                }

                return $this;
            }

            $this->runIndividualStatement($metadataList);

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf(
                'A PDOException was thrown for statement \'%s\' with message \'%s\'',
                $this->statement->getResolvedScenarioStatementName(),
                $e->getMessage()
            );

            throw new \RuntimeException($message);
        }
    }
    /**
     * @param array $metadataList
     * @param array|null $userParameters
     */
    private function runIndividualStatement(array $metadataList, array $userParameters = null)
    {
        $sql = $this->statement->getSql();

        $pdoStatement = $this->connection->getPDO()->prepare($sql);

        $this->handleUseOption($metadataList, $pdoStatement);
        $this->handleForeignKey($metadataList, $pdoStatement);

        if (is_array($userParameters)) {
            foreach ($userParameters as $key => $parameter) {
                $this->bindSingleParameter(
                    new Parameter(
                        $key,
                        $parameter
                    ),
                    $pdoStatement
                );
            }
        }

        if (!is_array($userParameters)) {
            $userParameters = $this->statement->getUserParameters();

            if (!empty($userParameters)) {
                foreach ($userParameters as $key => $parameter) {
                    $this->bindSingleParameter(
                        new Parameter(
                            $key,
                            $parameter
                        ),
                        $pdoStatement
                    );
                }
            }
        }

        $pdoStatement->execute();

        $this->saveResult($pdoStatement);
    }
    /**
     * @param ForeignKey $foreignKey
     * @param TypeInterface $sqlType
     * @param string $resolvedStatementName
     * @param array $metadataList
     */
    private function executeForeignKeysFirst(
        ForeignKey $foreignKey,
        TypeInterface $sqlType,
        string $resolvedStatementName,
        array $metadataList
    ) {
        if ($sqlType->equals(InsertSqlType::fromValue())) {
            if (
                !$sqlType->equals(InsertSqlType::fromValue()) and
                !$sqlType->equals(UpdateSqlType::fromValue())
            ) {
                throw new \RuntimeException(
                    sprintf('Invalid statement type. \'foreign_key\' options can only be used with \'insert\' or \'update\' statement for statement \'%s\'. Try using \'use\' option instead',
                        $resolvedStatementName
                    )
                );
            }

            /** @var Metadata $foreignKeyStatement */
            $foreignKeyStatement = $metadataList[$foreignKey->getStatementName()];

            if (!$this->results->has($foreignKey->getStatementName())) {
                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $foreignKeyStatement,
                    $this->results,
                    $this->connection
                );

                $recursiveStatementExecution->execute($metadataList);

                unset($recursiveStatementExecution);
            }
        }
    }
    /**
     * @param array $metadataList
     * @param \PDOStatement $pdoStatement
     */
    private function handleUseOption(
        array $metadataList,
        \PDOStatement $pdoStatement
    ) {
        $useOption = $this->statement->getUseOption();

        if ($useOption instanceof UseOption) {
            $useOptionStatementName = $this->statement->getUseOptionStatementName();
            $useStatement = $metadataList[$useOption->getStatementName()];

            if (!$this->results->has($useOption->getStatementName())) {
                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $useStatement,
                    $this->results,
                    $this->connection
                );

                $recursiveStatementExecution->execute($metadataList);
                $result = $recursiveStatementExecution->getResult();

                unset($recursiveStatementExecution);

                $this->results->add($useOption->getStatementName(), $result);
            }

            $useOptionResult = $this->results->get($useOption->getStatementName());

            if (!$useOptionResult instanceof SelectQueryResult) {
                throw new \RuntimeException(
                    sprintf(
                        'Invalid use option result in statement \'%s\' that has use option statement \'%s\'. A use option query can only be a select query, cannot be empty and can only return a single row result. In cases where you don\'t know if the result will exist, add an \'if_exists\' option',
                        $this->statement->getResolvedScenarioStatementName(),
                        $useOptionStatementName
                    )
                );
            }

            if (!$useOptionResult->getMetadata()->isOneRow()) {
                throw new \RuntimeException(sprintf(
                    'Invalid use option result. Results of \'use\' statements can only return one row and cannot be empty for statement \'%s\'',
                    $useOptionStatementName
                ));
            }

            foreach ($useOption->getValues() as $key => $parameterKey) {
                $exploded = explode('.', $key);

                $parameterValue = $useOptionResult->getQueryResult()[0][$exploded[1]];

                $this->bindSingleParameter(new Parameter($parameterKey, $parameterValue), $pdoStatement);
            }
        }
    }
    /**
     * @param array $metadataList
     * @param \PDOStatement $pdoStatement
     */
    private function handleForeignKey(
        array $metadataList,
        \PDOStatement $pdoStatement
    ) {
        $foreignKeys = $this->statement->getForeignKeys();

        if (!empty($foreignKeys)) {
            /** @var ForeignKey $foreignKey */
            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey instanceof ForeignKey) {
                    $foreignKeyStatementName = $this->statement->getForeignKeyStatementName($foreignKey);
                    $foreignKeyStatement = $metadataList[$foreignKey->getStatementName()];

                    if (!$this->results->has($foreignKey->getStatementName())) {
                        $recursiveStatementExecution = new RecursiveStatementExecution(
                            $foreignKeyStatement,
                            $this->results,
                            $this->connection
                        );

                        $recursiveStatementExecution->execute($metadataList);

                        $this->results->add(
                            $foreignKey->getStatementName(),
                            $recursiveStatementExecution->getResult()
                        );

                        unset($recursiveStatementExecution);
                    }

                    $foreignKeyResult = $this->results->get($foreignKey->getStatementName());

                    if (!$foreignKeyResult instanceof InsertQueryResult and !$foreignKeyResult instanceof MultipleInsertQueryResult) {
                        throw new \RuntimeException(sprintf(
                            'Results of \'foreign_key\' statements can only return one row and cannot be empty for statement \'%s\'',
                            $foreignKeyStatementName
                        ));
                    }

                    $this->bindSingleParameter(
                        new Parameter(
                            $foreignKey->getBindTo(),
                            $foreignKeyResult->getLastInsertId()
                        ),
                        $pdoStatement
                    );
                }
            }
        }
    }
    /**
     * @param \PDOStatement $pdoStatement
     */
    private function saveResult(\PDOStatement $pdoStatement)
    {
        /** @var TypeInterface $statementType */
        $statementType = $this->statement->getSqlType();

        $resolvedStatementName = $this->statement->getSingleScenarioName();

        $queryResult = ResultReportContext::context(array(
            'statement_type' => (string) $statementType,
            'pdo_statement' => $pdoStatement,
            'connection' => $this->connection,
        ))->makeReport();

        if ($this->statement->getUserParametersType() instanceof MultipleParametersType) {
            $this->results->addTo($resolvedStatementName, $queryResult);

            return;
        }

        $this->results->add($resolvedStatementName, $queryResult);
    }
}