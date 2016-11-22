<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Exception\ConfigurationException;

class ScenarioReturnEntity
{
    /**
     * @var array $returns
     */
    private $returns = array();
    /**
     * @param array $returns
     * @throws ConfigurationException
     */
    public function __construct(array $returns)
    {
        foreach ($returns as $return) {
            $isAliasType = preg_match('#^.*(\s+as\s+).*$#', $return);

            if ($isAliasType === 1) {
                $aliasTypeExploded = explode(' as ', $return);

                $count = count($aliasTypeExploded);

                if ($count !== 2) {
                    throw new ConfigurationException('Invalid return statement. Check the documentation for scenario \'return_entity\' configuration');
                }

                $statementNameUnresolved = $aliasTypeExploded[0];

                $statementNameExploded = explode('.', $statementNameUnresolved);

                if (count($statementNameExploded) !== 2) {
                    throw new ConfigurationException('Invalid return statement. Check the documentation for scenario \'return_entity\' configuration');
                }

                $statementName = trim($statementNameExploded[0]);
                $columnName = trim($statementNameExploded[1]);
                $alias = trim($aliasTypeExploded[1]);

                $this->returns[] = new ReturnData($statementName, $columnName, $alias);

                continue;
            }

            if ($isAliasType === 0) {
                $statementTypeExploded = explode('.', $return);

                $count = count($statementTypeExploded);

                if ($count === 1) {
                    $statementNameOnly = trim($statementTypeExploded[0]);

                    $this->returns[] = new ReturnData($statementNameOnly);

                    continue;
                }

                if ($count === 2) {
                    $statementName = trim($statementTypeExploded[0]);
                    $columnName = trim($statementTypeExploded[1]);

                    $this->returns[] = new ReturnData($statementName, $columnName);
                }
            }
        }
    }
    /**
     * @param string $statementName
     * @return bool
     */
    public function hasReturnData(string $statementName) : bool
    {
        return array_key_exists($statementName, $this->returns);
    }
    /**
     * @param string $statementName
     * @return mixed
     */
    public function getReturnData(string $statementName) : ReturnData
    {
        return $this->returns[$statementName];
    }
    /**
     * @return array
     */
    public function getAllReturnData() : array
    {
        return $this->returns;
    }
}