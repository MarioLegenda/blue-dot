<?php

namespace BlueDot\Result;

use BlueDot\Result\Context\ContextInterface;
use BlueDot\Result\Context\SelectContext;

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

        switch ($statementType) {
            case 'select':
                return new SelectContext($pdoStatement);

                break;
        }
    }
}