<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Common\StorageUserInterface;
use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Exception\CommonInternalException;

class ScenarioStatement implements ConfigurationInterface, StorageInterface, StorageUserInterface
{
    /**
     * @var ArgumentBag $arguments
     */
    private $arguments;
    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage = null)
    {
        $this->arguments = $storage;
    }
    /**
     * @param StorageInterface $storage
     * @param bool|false $overwrite
     * @throws CommonInternalException
     */
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false) : StorageInterface
    {
        if ($storage instanceof StorageInterface) {
            foreach ($storage as $key => $item) {
                $this->add($key, $item);
            }
        }
    }
    /**
     * @param string $name
     * @param $value
     * @param bool|false $overwrite
     * @return $this
     * @throws CommonInternalException
     */
    public function add(string $name, $value, bool $overwrite = false) : StorageInterface
    {
        if ($this->arguments->has($name) and $overwrite === false) {
            throw new CommonInternalException(ArgumentBag::class.' already contains an argument with name '.$name);
        }

        $this->arguments[$name] = $value;

        return $this;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return $this->arguments->has($name);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!$this->arguments->has($name)) {
            throw new CommonInternalException(ArgumentBag::class.' does not contain an argument with name '.$name);
        }

        return $this->arguments->get($name);
    }
    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        if (!$this->arguments->has($name)) {
            return false;
        }

        $this->arguments->remove($name);

        return true;
    }
    /**
     * @return string|string
     */
    public function getName() : string
    {
        return $this->arguments->get('name');
    }
    /**
     * @return string|string
     */
    public function getStatement() : string
    {
        return $this->arguments->get('sql');
    }
    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->arguments->get('parameters');
    }
    /**
     * @return string|string
     */
    public function getType() : string
    {
        return $this->arguments->get('type');
    }
    /**
     * @param bool $atomic
     * @return ScenarioStatement
     */
    public function setAtomic(bool $atomic) : ScenarioStatement
    {
        $this->arguments->add('atomic', $atomic, true);
        return $this;
    }
    /**
     * @return bool
     */
    public function isAtomic() : bool
    {
        return $this->arguments->get('atomic');
    }
    /**
     * @void
     */
    public function markExecuted()
    {
        $this->arguments->add('executed', true);
    }
    /**
     * @return bool
     */
    public function isExecuted() : bool
    {
        return ($this->arguments->get('executed') === true) ? true : false;
    }
    /**
     * @param UseOption $useOption
     * @return $this
     */
    public function setUseOption(UseOption $useOption) : ScenarioStatement
    {
        $this->arguments->add('use_option', $useOption);

        return $this;
    }
    /**
     * @return bool
     */
    public function hasUseOption() : bool
    {
        return $this->arguments->has('use_option');
    }
    /**
     * @return UseOption
     */
    public function getUseOption() : UseOption
    {
        return $this->arguments->get('use_option');
    }
    /**
     * @param ForeginKey $foreginKey
     * @return $this
     */
    public function setForeignKey(ForeginKey $foreignKey) : ScenarioStatement
    {
        $this->arguments->add('foreign_key', $foreignKey);

        return $this;
    }
    /**
     * @return bool
     */
    public function hasForeignKey() : bool
    {
        return $this->arguments->has('foreign_key');
    }
    /**
     * @return ForeginKey
     */
    public function getForeignKey() : ForeginKey
    {
        return $this->arguments->get('foreign_key');
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }
}