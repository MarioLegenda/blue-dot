<?php

namespace BlueDot\Configuration\Compound;

use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Exception\ConfigurationException;

class CompoundStatementCollection implements \IteratorAggregate
{
    /**
     * @var array $compoundStatements
     */
    private $compoundStatements = array();

    public function __construct(array $compoundStatements = array())
    {
        if (!empty($compoundStatements)) {
            foreach ($compoundStatements as $compound) {
                if (!$compound instanceof ConfigurationInterface) {
                    throw new ConfigurationException('Invalid argument in '.CompoundStatementCollection::class.'. Expected an array of '.CompoundStatement::class);
                }
            }

            $this->compoundStatements = $compoundStatements;
        }
    }
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
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->compoundStatements);
    }
}