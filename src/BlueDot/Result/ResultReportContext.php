<?php

namespace BlueDot\Result;

use BlueDot\Result\Context\ContextInterface;
use BlueDot\Result\Context\DeleteContext;
use BlueDot\Result\Context\InsertContext;
use BlueDot\Result\Context\SelectContext;
use BlueDot\Result\Context\UpdateContext;

class ResultReportContext
{
    /**
     * @param array $data
     * @return ContextInterface
     */
    public static function context(array $data) : ContextInterface
    {
        $statementType = $data['statement_type'];
        $pdoStatement = $data['pdo_statement'];
        $connection = $data['connection'];

        switch ($statementType) {
            case 'select':
                return new SelectContext($pdoStatement);
            case 'insert':
                return new InsertContext($pdoStatement, $connection);
            case 'update':
                return new UpdateContext($pdoStatement);
            case 'delete':
                return new DeleteContext($pdoStatement);
        }
    }
}