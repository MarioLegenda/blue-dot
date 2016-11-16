<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Entity\Entity;

class ScenarioResultStorage
{
    /**
     * @var array $storage
     */
    private $storage = array();
    /**
     * @param string $statementName
     * @param Entity $entity
     * @return $this
     */
    public function add(string $statementName, Entity $entity) : ScenarioResultStorage
    {
        $this->storage[$statementName] = $entity;

        return $this;
    }
    /**
     * @param string $statementName
     * @return bool
     */
    public function has(string $statementName) : bool
    {
        return array_key_exists($statementName, $this->storage);
    }
    /**
     * @param string $statementName
     * @return null
     */
    public function get(string $statementName)
    {
        return ($this->has($statementName)) ? $this->storage[$statementName] : null;
    }
}