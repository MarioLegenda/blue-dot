<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Configuration\ConfigurationInterface;

class SimpleStatement implements ConfigurationInterface
{
    /**
     * @var ArgumentBag $argumentBag
     */
    private $arguments;
    /**
     * @param StorageInterface $arguments
     */
    public function __construct(StorageInterface $arguments)
    {
        $this->arguments = $arguments;
    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->arguments->get('name');
    }
    /**
     * @return string
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
     * @return string
     */
    public function getType() : string
    {
        return $this->arguments->get('type');
    }
}