<?php

namespace BlueDot\StatementBuilder;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Exception\CompileException;
use BlueDot\Entity\Model;
use BlueDot\Exception\StatementBuilderException;
use BlueDot\Database\Execution\ExecutionContext;
use BlueDot\Entity\Promise;
use BlueDot\Component\TaskRunner\TaskRunnerFactory;
use BlueDot\Database\Validation\SimpleStatementTaskRunner;
use BlueDot\Component\ModelConverter;
use BlueDot\Database\Validation\Simple\SimpleStatementParameterValidation;
use BlueDot\Database\Validation\Simple\SimpleParametersResolver;

class StatementBuilder
{
    /**
     * @var Connection|null
     */
    private $connection = null;
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var array $configParameters
     */
    private $configParameters = array();
    /**
     * @var array $userParameters
     */
    private $userParameters = array();
    /**
     * StatementBuilder constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $statement = new ArgumentBag();

        $statement->add('type', 'simple');

        $this->statement = $statement;
    }
    /**
     * @param string $sql
     * @return StatementBuilder
     */
    public function addSql(string $sql) : StatementBuilder
    {
        $sqlType = $this->resolveSqlType($sql);

        $this->statement
            ->add('statement_type', $sqlType)
            ->add('sql', $sql);

        return $this;
    }
    /**
     * @param string $key
     * @param $value
     * @return StatementBuilder
     * @throws BlueDotRuntimeException
     */
    public function addParameter(string $key, $value) : StatementBuilder
    {
        $this->configParameters[] = $key;

        if (array_key_exists($key, $this->userParameters)) {
            throw new BlueDotRuntimeException(
                sprintf('Invalid statement builder parameters. Parameter with key \'%s\' already exists', $key)
            );
        }

        $this->userParameters[$key] = $value;

        return $this;
    }
    /**
     * @param string $object
     * @param array|null $properties
     * @return StatementBuilder
     * @throws CompileException
     * @throws StatementBuilderException
     */
    public function addModel(string $object, array $properties = null) : StatementBuilder
    {
        if ($this->statement->has('model')) {
            throw new StatementBuilderException(
                sprintf('Duplicate model exception. \'%s\' already added as model',
                    $this->statement->get('model')->getName()
                )
            );
        }

        $properties = (is_array($properties)) ? $properties : array();

        if (!class_exists($object)) {
            throw new CompileException(sprintf('Invalid model options. Object \'%s\' does not exist', $object));
        }

        if (!empty($properties)) {
            foreach ($properties as $key => $value) {
                if (!is_string($key)) {
                    throw new CompileException('Invalid model options. \'properties\' should be a associative array. %s given for value %s', $key, $value);
                }
            }
        }

        $this->statement->add('model', new Model($object, $properties));

        return $this;
    }
    /**
     * @return PromiseInterface
     */
    public function execute() : PromiseInterface
    {
        $name = 'statement_builder_statement';
        $resolvedName = $this->statement->get('statement_type').'.'.$name;
        $resolvedStatementName = 'simple.'.$resolvedName;

        $this->statement->add('resolved_name', $resolvedName);
        $this->statement->add('statement_name', $name);
        $this->statement->add('resolved_statement_name', $resolvedStatementName);
        $this->statement->add('cache', false);

        if (!empty($this->configParameters)) {
            $this->statement->add('config_parameters', $this->configParameters);
        }

        $this->statement->add('connection', $this->connection);

        $context = new ExecutionContext($this->statement, null, false);

        return $context->runTasks()->createPromise();
    }

    private function resolveSqlType(string $sql) : string
    {
        preg_match('#(\w+\s)#i', $sql, $matches);

        if (empty($matches)) {
            throw new CompileException(sprintf(
                'Statement builder could not properly determine sql type for \'%s\'',
                $sql
            ));
        }

        $sqlType = trim(strtolower($matches[1]));

        if ($sqlType === 'create' or $sqlType === 'use') {
            $sqlType = 'table';
        }

        return $sqlType;
    }

}