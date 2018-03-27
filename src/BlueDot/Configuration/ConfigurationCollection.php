<?php

namespace BlueDot\Configuration;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\Finder\CallableStatementFinder;
use BlueDot\Configuration\Finder\ScenarioStatementFinder;
use BlueDot\Configuration\Finder\SimpleConfigurationFinder;
use BlueDot\Database\Model\ConfigurationInterface;

class ConfigurationCollection
{
    /**
     * @var array $statementFinders
     */
    private $statementFinders = [];
    /**
     * StatementCollection constructor.
     * @param array $statements
     */
    public function __construct(array $statements)
    {
        $this->statementFinders['simple'] = new SimpleConfigurationFinder();
        $this->statementFinders['scenario'] = new ScenarioStatementFinder();
        $this->statementFinders['callable'] = new CallableStatementFinder();

        foreach ($statements as $name => $statement) {
            $this->arrange($name, $statement);
        }
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasConfiguration(string $name): bool
    {
        $type = $this->determineType($name);
        $statement = $this->statementFinders[$type]->find($name);

        return $statement instanceof ConfigurationInterface;
    }
    /**
     * @param string $name
     * @return ConfigurationInterface
     */
    public function getConfiguration(string $name): ?ConfigurationInterface
    {
        if ($this->hasConfiguration($name)) {
            return $this->statementFinders[$this->determineType($name)]->find($name);
        }

        return null;
    }
    /**
     * @param string $name
     * @return string
     */
    private function determineType(string $name): string
    {
        return explode('.', $name)[0];
    }
    /**
     * @param string $name
     * @param ArgumentBag $statement
     */
    private function arrange(string $name, $statement)
    {
        $type = $this->determineType($name);

        switch ($type) {
            case 'simple':
                $this->statementFinders[$type]->add($name, $statement);

                return;
            case 'scenario':
                $this->statementFinders[$type]->add($name, $statement);

                return;
            case 'callable':
                $this->statementFinders[$type]->add($name, $statement);

                return;
        }

        $message = sprintf('Statement \'%s\' not found in place where it should be found. This is a bug. Please, visit https://github.com/MarioLegenda/blue-dot and submit and issue', $name);
        throw new \RuntimeException($message);
    }
}