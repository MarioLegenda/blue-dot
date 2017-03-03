<?php

namespace BlueDot\Result\Context;

use BlueDot\Result\SelectQueryResult;

interface ContextInterface
{
    public function makeReport() : SelectQueryResult;
}