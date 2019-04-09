<?php


namespace BlueDot\Entity;

interface EntityInterface
{
    public function getName(): string;
    public function getType(): string;
    public function toArray(): array;
}