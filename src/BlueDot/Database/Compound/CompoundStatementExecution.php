<?php

namespace BlueDot\Database\Compound;

use BlueDot\Configuration\Compound\CompoundStatementCollection;
use BlueDot\Database\AbstractStatementExecution;

class CompoundStatementExecution extends AbstractStatementExecution
{
    public function execute()
    {
        $compoundStatementCollection = new CompoundStatementCollection($this->specificConfiguration);

        foreach ($compoundStatementCollection as $compound) {

        }
    }
}