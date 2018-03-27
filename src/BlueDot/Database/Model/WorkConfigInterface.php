<?php

namespace BlueDot\Database\Model;

interface WorkConfigInterface
{
    /**
     * @return string
     */
    public function getSql(): string;
    /**
     * @return array|null
     */
    public function getConfigParameters(): ?array;
    /**
     * @return Model|null
     */
    public function getModel(): ?Model;
    /**
     * @return array
     */
    public function getUserParameters(): array;
    /**
     * @param array $userParameters
     * @void
     */
    public function injectUserParameters(array $userParameters);
}