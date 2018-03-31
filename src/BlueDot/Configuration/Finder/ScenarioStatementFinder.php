<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\FlowProductInterface;

class ScenarioStatementFinder
{
    /**
     * @var array $statements
     */
    private $statements = [];
    /**
     * @param string $name
     * @param FlowProductInterface $statement
     * @return ScenarioStatementFinder
     */
    public function add(string $name, FlowProductInterface $statement): ScenarioStatementFinder
    {
        $this->statements[$name] = $statement;

        return $this;
    }
    /**
     * @param string $name
     * @return FlowProductInterface|null
     */
    public function find(string $name): ?FlowProductInterface
    {
        if (!array_key_exists($name, $this->statements)) {
            return null;
        }

        return $this->statements[$name];
    }
}