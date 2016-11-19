<?php

namespace BlueDot\Entity;

use BlueDot\Common\AbstractArgumentBag;

class EntityCollection extends AbstractArgumentBag
{
    public function __construct(array $resultCollection)
    {
        foreach ($resultCollection as $numericKey => $value) {
            $this->add($numericKey, new Entity($value));
        }
    }
}