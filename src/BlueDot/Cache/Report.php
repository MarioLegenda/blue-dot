<?php

namespace BlueDot\Cache;

use BlueDot\Common\StorageInterface;
use BlueDot\Exception\CommonInternalException;

class Report
{
    private $reports;

    /**
     * @param string $name
     * @param $value
     * @param bool $overwrite
     * @throws CommonInternalException
     * @return $this
     */
    public function add(string $name, $value, bool $overwrite = false) : StorageInterface
    {
        if ($this->has($name) and $overwrite === false) {
            throw new CommonInternalException(Report::class.' already contains an argument with name '.$name);
        }

        $this->reports[$name] = $value;

        return $this;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->reports);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new CommonInternalException(Report::class.' does not contain an argument with name '.$name);
        }

        return $this->reports[$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        if (!$this->has($name)) {
            return false;
        }

        unset($this->reports[$name]);

        return true;
    }
}