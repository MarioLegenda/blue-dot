<?php

namespace Test;

use BlueDot\Common\AbstractCallable;
use BlueDot\Entity\Entity;

class CallableService extends AbstractCallable
{
    /**
     * @void
     * @return Entity
     */
    public function run()
    {
        return new Entity();
    }
}