<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Exception\ConfigurationException;

class Rules
{
    private $rules = array(
        'minimal_select_statement' => true,
        'return_entity' => true,
    );

    public function __construct(array $rules)
    {
        $supportedRules = array_keys($this->rules);
        foreach ($rules as $ruleName => $rule) {
            if (!$this->hasRule($ruleName)) {
                throw new ConfigurationException('Rule \''.$ruleName.'\' is not supported. Supported rules are '.implode(', ', $supportedRules));
            }

            if (!is_bool($rule)) {
                throw new ConfigurationException('A \'rule\' has to be a boolean value of true or false. Supported rules are '.implode(', ', $supportedRules));
            }

            $this->rules[$ruleName] = $rule;
        }
    }
    /**
     * @param string $ruleName
     * @return bool
     */
    public function hasRule(string $ruleName) : bool
    {
        return array_key_exists($ruleName, $this->rules);
    }
    /**
     * @param $ruleName
     * @return mixed
     */
    public function getRule($ruleName) : bool
    {
        return $this->rules[$ruleName];
    }
}