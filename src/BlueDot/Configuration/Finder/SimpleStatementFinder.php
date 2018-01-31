<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\ArgumentBag;

class SimpleStatementFinder
{
    /**
     * @var array $statements
     */
    private $statements = [];
    /**
     * @param string $name
     * @param ArgumentBag $statement
     * @return SimpleStatementFinder
     */
    public function add(string $name, ArgumentBag $statement): SimpleStatementFinder
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
        if (array_key_exists($name, $this->statements)) {
            return $this->statements[$name];
        }

        return null;
    }
}