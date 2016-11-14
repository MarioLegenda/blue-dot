<?php

namespace BlueDot\Configuration\Compound;

class CompoundStatementCollection
{
    /**
     * @var array $compoundStatements
     */
    private $compoundStatements = array();
    /**
     * @param CompoundStatement $compoundStatement
     * @return CompoundStatementCollection
     */
    public function add(string $name, CompoundStatement $compoundStatement) : CompoundStatementCollection
    {
        $this->compoundStatements[$name][] = $compoundStatement;

        return $this;
    }
    /**
     * @param string $name
     * @param string $compoundName
     * @return bool
     */
    public function hasCompound(string $name, string $compoundName) : bool
    {
        if (!array_key_exists($name, $this->compoundStatements)) {
            return false;
        }

        foreach ($this->compoundStatements[$name] as $compondStatement) {
            if ($compondStatement->getName() === $compoundName) {
                return true;
            }
        }

        return false;
    }
}