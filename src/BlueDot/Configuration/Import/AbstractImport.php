<?php

namespace BlueDot\Configuration\Import;

use Symfony\Component\Yaml\Yaml;

abstract class AbstractImport implements ImportInterface
{
    /**
     * @var array $imports
     */
    private $imports = array();
    /**
     * AbstractImport constructor.
     * @param string $file
     */
    public function __construct(string $file)
    {
        $parsed = Yaml::parse(file_get_contents($file));

        if (!is_array($parsed)) {
            return;
        }

        foreach ($parsed as $name => $possibleSql) {
            if (is_string($possibleSql)) {
                $this->imports[$name] = $possibleSql;
            }

            if (is_array($possibleSql)) {
                $this->createImports($name, $possibleSql);
            }
        }
    }
    /**
     * @param string $name
     * @return string
     */
    public function getValue(string $name) : string
    {
        return $this->imports[$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasValue(string $name) : bool
    {
        if (array_key_exists($name, $this->imports)) {
            $import = $this->imports[$name];

            if (is_string($import)) {
                return true;
            }
        }

        return false;
    }

    private function createImports(string $namespace, array $importsArray)
    {
        foreach ($importsArray as $name => $possibleSql) {
            if (is_string($possibleSql)) {
                $trueNamespace = $namespace.'.'.$name;

                $this->imports[$trueNamespace] = $possibleSql;
            }

            if (is_array($possibleSql)) {
                $trueNamespace = $namespace.'.'.$name;

                $this->createImports($trueNamespace, $possibleSql);
            }
        }


    }
}