<?php

namespace BlueDot\Database\Execution\LowLevelStrategy;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Connection;
use BlueDot\Database\Execution\SimpleStrategy;
use BlueDot\Database\Execution\StrategyInterface;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Exception\BlueDotRuntimeException;

class BasicStatementExecution extends SimpleStrategy
{
    /**
     * BasicStatementExecution constructor.
     * @param ArgumentBag $statement
     * @param ArgumentBag $resultReport
     * @param Connection $connection
     */
    public function __construct(ArgumentBag $statement, ArgumentBag $resultReport, Connection $connection)
    {
        $this->statement = $statement;
        $this->connection = $connection;
        $this->resultReport = $resultReport;
    }

    public function execute(ArgumentBag $statements = null): StrategyInterface
    {
        if ($this->statement->has('use_option')) {
            $useOption = $this->statement->get('use_option');
            $useStatement = $statements->get($this->statement->get('scenario_name').'.'.$useOption->getName());

            if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                $basicStatementExecution = new BasicStatementExecution(
                    $useStatement,
                    $this->resultReport,
                    $this->connection
                );

                $result = $basicStatementExecution->execute($statements)->getResult();

                $this->resultReport->add($useStatement->get('resolved_statement_name'), $result, true);
            }

            $useOptionResult = $this->resultReport->get($useStatement->get('resolved_statement_name'));

            foreach ($useOption->getValues() as $key => $value) {
                $exploded = explode('.', $key);

                $statementName = $exploded[0];
                $parameterKey = $exploded[1];

                $parameterValue = $useOptionResult->get($parameterKey);
            }
        }


        $result = parent::execute()->getResult();

        $this->resultReport->add($this->statement->get('resolved_statement_name'), $result, true);

        return $this;
    }
}