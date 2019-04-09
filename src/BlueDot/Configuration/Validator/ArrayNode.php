<?php

namespace BlueDot\Configuration\Validator;

use BlueDot\Exception\ConfigurationException;

class ArrayNode implements \IteratorAggregate, \Countable
{
    /**
     * @var string $nodeName
     */
    private $nodeName;
    /**
     * @var bool $conditionalIgnore
     */
    private $conditionalIgnore = false;
    /**
     * @var array $nodeName
     */
    private $parentNode;
    /**
     * @var array $workingNode
     */
    private $workingNode;
    /**
     * ArrayNode constructor.
     * @param string $rootNode
     * @param array $workingNode
     * @param ArrayNode|null $parentNode
     * @throws ConfigurationException
     */
    public function __construct(string $rootNode, array $workingNode, ArrayNode $parentNode = null)
    {
        $this->nodeName = $rootNode;

        if (empty($workingNode)) {
            throw new ConfigurationException('Node has to be a non empty array for parent \''.$rootNode.'\'');
        }

        $this->workingNode = $workingNode;

        if ($parentNode instanceof ArrayNode) {
            $this->parentNode = $parentNode;
        }
    }
    /**
     * @return string|string
     */
    public function getNodeName() : string
    {
        return $this->nodeName;
    }
    /**
     * @param string $nodeName
     * @return $this|ArrayNode
     * @throws ConfigurationException
     */
    public function stepInto(string $nodeName) : ArrayNode
    {
        if (!empty($this->workingNode)) {
            if (!array_key_exists($nodeName, $this->workingNode)) {
                throw new ConfigurationException('\''.$nodeName.'\' not found and cannot step into');
            }

            return new ArrayNode(
                $nodeName,
                $this->workingNode[$nodeName],
                new ArrayNode($nodeName, $this->workingNode)
            );
        }

        throw new ConfigurationException('\''.$nodeName.'\' not found and cannot step into');
    }

    public function stepIntoIfExists(string $nodeName)
    {
        if (!array_key_exists($nodeName, $this->workingNode)) {
            $this->conditionalIgnore = true;

            return $this;
        }

        if (array_key_exists($nodeName, $this->workingNode)) {
            return new ArrayNode(
                $nodeName,
                $this->workingNode[$nodeName],
                new ArrayNode($nodeName, $this->workingNode)
            );
        }

        return $this;
    }

    /**
     * @return $this|array|ArrayNode
     * @throws ConfigurationException
     */
    public function stepOut()
    {
        if ($this->conditionalIgnore === true) {
            $this->conditionalIgnore = false;

            return $this;
        }

        if ($this->conditionalIgnore === false) {
            if (!$this->parentNode instanceof ArrayNode) {
                throw new ConfigurationException('Nowhere to step out to');
            }

            $parent = $this->getParent();

            if (!$parent instanceof ArrayNode) {
                throw new ConfigurationException('Nowhere to step out to');
            }

            return $parent;
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @param \Closure $closure
     * @return $this
     */
    public function closureValidator(string $nodeName, \Closure $closure) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            $closure->__invoke($nodeName, $this);
        }

        return $this;
    }
    /**
     * @param array $childNodes
     * @param \Closure $closure
     * @return $this
     * @throws ConfigurationException
     */
    public function applyToSubelementsOf(array $childNodes, \Closure $closure) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            foreach ($childNodes as $childNode) {
                if ($this->internalKeyExists($childNode, $this->workingNode)) {
                    $closure->__invoke($childNode, new ArrayNode($childNode, $this->workingNode[$childNode]));
                }
            }
        }

        return $this;
    }
    /**
     * @param array $childNodes
     * @param \Closure $closure
     * @return $this
     * @throws ConfigurationException
     */
    public function applyToSubelementsIfTheyExist(array $childNodes, \Closure $closure) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            foreach ($childNodes as $childNode) {
                if (!array_key_exists($childNode, $this->workingNode)) {
                    continue;
                }

                $closure->__invoke($childNode, new ArrayNode($childNode, $this->workingNode[$childNode]));
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @param array $node
     * @return ArrayNode
     * @throws ConfigurationException
     */
    public function keyExists(string $nodeName, array $node = array()) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            if (!empty($node)) {
                return $this->internalKeyExists($nodeName, $node);
            }

            return $this->internalKeyExists($nodeName, $this->workingNode);
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @param array $node
     * @return ArrayNode
     * @throws ConfigurationException
     */
    public function mandatoryKeyExists(string $nodeName, array $node = []): ArrayNode
    {
        if (!empty($node)) {
            return $this->internalKeyExists($nodeName, $node);
        }

        return $this->internalKeyExists($nodeName, $this->workingNode);
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function cannotBeEmpty(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            if (!array_key_exists($nodeName, $this->workingNode)) {
                throw new ConfigurationException('Node \''.$nodeName.'\' does not exist and and cannot be empty for parent node \''.$this->getNodeName().'\'');
            }

            if (is_bool($this->workingNode[$nodeName])) {
                return $this;
            }

            if (empty($this->workingNode[$nodeName])) {
                throw new ConfigurationException('Node \''.$nodeName.'\' cannot be empty for parent node \''.$this->getNodeName().'\'');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function cannotBeEmptyIfExists(string $nodeName) : ArrayNode
    {
        if (array_key_exists($nodeName, $this->workingNode)) {
            if (is_bool($this->workingNode[$nodeName])) {
                return $this;
            }

            if (empty($this->workingNode[$nodeName])) {
                throw new ConfigurationException('If \''.$nodeName.'\' exists, it cannot be empty');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isString(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            $this->internalKeyExists($nodeName, $this->workingNode);

            if (!is_string($this->workingNode[$nodeName])) {
                throw new ConfigurationException('\''.$nodeName.'\' should be a string');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isStringIfExists(string $nodeName) : ArrayNode
    {
        if (array_key_exists($nodeName, $this->workingNode)) {
            if (!is_string($this->workingNode[$nodeName])) {
                throw new ConfigurationException('If \''.$nodeName.'\' exists, it should be a string');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isArray(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            $this->internalKeyExists($nodeName, $this->workingNode);

            if (!is_array($this->workingNode[$nodeName])) {
                throw new ConfigurationException('\''.$nodeName.'\' has to be an array');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isArrayIfExists(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            if (array_key_exists($nodeName, $this->workingNode)) {
                if (!is_array($this->workingNode[$nodeName])) {
                    throw new ConfigurationException('If exists, \''.$nodeName.'\' has to be an array for parent \''.$this->getNodeName().'\'');
                }
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isBoolean(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            $this->internalKeyExists($nodeName, $this->workingNode);

            if (!is_bool($this->workingNode[$nodeName])) {
                throw new ConfigurationException('\''.$nodeName.'\' has to be a boolean');
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isBooleanIfExists(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            if (array_key_exists($nodeName, $this->workingNode)) {
                if (!is_bool($this->workingNode[$nodeName])) {
                    throw new ConfigurationException('If exists, \''.$nodeName.'\' has to be a boolean for parent \''.$this->getNodeName().'\'');
                }
            }
        }

        return $this;
    }
    /**
     * @param string $nodeName
     * @return $this
     * @throws ConfigurationException
     */
    public function isAssociativeStringArray(string $nodeName) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            if (!is_array($this->workingNode[$nodeName])) {
                throw new ConfigurationException('\''.$nodeName.'\' has to be a associative array with string keys');
            }

            $keys = array_keys($this->workingNode[$nodeName]);

            foreach ($keys as $key) {
                if (!is_string($key)) {
                    throw new ConfigurationException('\''.$nodeName.'\' has to be a associative array with string keys');
                }
            }
        }

        return $this;
    }
    /**
     * @param $nodeName
     * @param array $values
     * @return ArrayNode
     * @throws ConfigurationException
     */
    public function hasToBeOneOf($nodeName, array $values) : ArrayNode
    {
        if ($this->conditionalIgnore === false) {
            $this->internalKeyExists($nodeName, $this->workingNode);

            if (in_array($this->workingNode[$nodeName], $values) === false) {
                throw new ConfigurationException('One of values '.implode(', ', $values).' in node \''.$nodeName.'\' has to be present');
            }
        }

        return $this;
    }
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->workingNode);
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->workingNode);
    }
    /**
     * @return array|ArrayNode|null
     */
    private function getParent()
    {
        return $this->parentNode;
    }
    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->workingNode);
    }
    /**
     * @param string $nodeName
     * @param array $node
     * @return $this
     * @throws ConfigurationException
     */
    private function internalKeyExists(string $nodeName, array $node)
    {
        if (!array_key_exists($nodeName, $node)) {
            throw new ConfigurationException('Invalid configuration. \''.$nodeName.'\' does not exist for parent node \''.$this->getNodeName().'\'');
        }

        return $this;
    }
}