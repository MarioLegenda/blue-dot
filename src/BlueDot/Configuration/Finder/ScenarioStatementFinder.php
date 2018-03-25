<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\ArgumentBag;

class ScenarioStatementFinder
{
    /**
     * @var array $statements
     */
    private $statements = [];
    /**
     * @param string $name
     * @param ArgumentBag $statement
     * @return ScenarioStatementFinder
     */
    public function add(string $name, ArgumentBag $statement): ScenarioStatementFinder
    {
        $this->statements[$name] = $statement;

        return $this;
    }
    /**
     * @param string $name
     * @return ArgumentBag|null
     */
    public function find(string $name): ?ArgumentBag
    {
        if (!array_key_exists($name, $this->statements)) {
            return null;
        }

        return $this->statements[$name];
    }
}