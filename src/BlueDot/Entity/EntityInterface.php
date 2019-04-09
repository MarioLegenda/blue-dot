<?php


namespace BlueDot\Entity;

interface EntityInterface
{
    public function getType(): string;
    public function toArray(): array;
}