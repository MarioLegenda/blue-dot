<?php

namespace BlueDot\Result\Context;

use BlueDot\Result\NullQueryResult;

class NullContext implements ContextInterface
{
    public function makeReport() : NullQueryResult
    {
        return new NullQueryResult();
    }
}