<?php

namespace BlueDot\Configuration\Import;

class ImportCollection
{
    /**
     * @var array $imports
     */
    private $imports = array();
    /**
     * @param ImportInterface $import
     * @return ImportCollection
     */
    public function addImport(ImportInterface $import) : ImportCollection
    {
        if ($import instanceof SqlImport) {
            $this->imports['sql_import'] = $import;
        }

        return $this;
    }
    /**
     * @param string $type
     * @return ImportInterface
     */
    public function getImport(string $type) : ImportInterface
    {
        return $this->imports[$type];
    }
    /**
     * @param string $type
     * @return bool
     */
    public function hasImport(string $type) : bool
    {
        return array_key_exists($type, $this->imports);
    }
}