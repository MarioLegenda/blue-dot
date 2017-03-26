<?php

namespace BlueDot\Cache;

use BlueDot\Common\ArgumentBag;

class HashTable
{
    private $table = array();

    public function get(ArgumentBag $statement)
    {
        $name = $this->createName($statement);

        if (array_key_exists($name, $this->table)) {
            return $this->table[$name];
        }

        return '';
    }

    public function add(ArgumentBag $statement)
    {
        $createdName = $this->createName($statement);

        $this->table[$createdName] = md5($createdName);
    }

    private function createName(ArgumentBag $statement)
    {
        $resolvedStatementName = $statement->get('resolved_statement_name');

        if ($statement->has('parameters')) {
            $parameters = $statement->get('parameters');
            foreach ($parameters as $key => $value) {
                $imploded.=$key.'='.$value;
            }

            $resolvedStatementName.=':'.$imploded;
        }

        return $resolvedStatementName;
    }
}