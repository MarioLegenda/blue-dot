<?php

namespace BlueDot\Configuration;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Finder\ServiceStatementFinder;
use BlueDot\Configuration\Finder\ScenarioStatementFinder;
use BlueDot\Configuration\Finder\SimpleConfigurationFinder;

class ConfigurationCollection
{
    /**
     * @var array $statementFinders
     */
    private $statementFinders = [];
    /**
     * StatementCollection constructor.
     * @param \Generator $statements
     */
    public function __construct(\Generator $statements)
    {
        $this->statementFinders['simple'] = new SimpleConfigurationFinder();
        $this->statementFinders['scenario'] = new ScenarioStatementFinder();
        $this->statementFinders['service'] = new ServiceStatementFinder();

        foreach ($statements as $statement) {
            $this->arrange($statement['key'], $statement['item']);
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

        return $statement instanceof FlowProductInterface;
    }
    /**
     * @param string $name
     * @return FlowProductInterface
     */
    public function getConfiguration(string $name): ?FlowProductInterface
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
            case 'service':
                $this->statementFinders[$type]->add($name, $statement);

                return;
        }

        $message = sprintf('Statement \'%s\' not found in place where it should be found. This is a bug. Please, visit https://github.com/MarioLegenda/blue-dot and submit and issue', $name);
        throw new \RuntimeException($message);
    }
}