<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\ArgumentBag;

class ScenarioStatementFinder
{
    /**
     * @var array $statementKeys
     */
    private $statementKeys = [];
    /**
     * @var array $statements
     */
    private $statements = [];
    /**
     * @param string $name
     * @param ArgumentBag $statement
     * @return ScenarioStatementFinder
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function add(string $name, ArgumentBag $statement): ScenarioStatementFinder
    {
        $statements = $statement->get('statements');

        $temp = [];
        /** @var ArgumentBag $singleStatement */
        foreach ($statements as $singleStatement) {
            $temp[] = $singleStatement->get('resolved_statement_name');
        }

        $this->statementKeys[] = $temp;
        $this->statements[] = $statement;

        return $this;
    }
    /**
     * @param string $name
     * @return ArgumentBag|null
     */
    public function find(string $name): ?ArgumentBag
    {
        foreach ($this->statementKeys as $key => $statements) {
            if (in_array($name, $statements)) {
                return $this->statements[$key];
            }
        }

        return null;
    }
}