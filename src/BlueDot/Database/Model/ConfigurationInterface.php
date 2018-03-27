<?php

namespace BlueDot\Database\Model;


interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getName(): string;
    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;
    /**
     * @param array|null $userParameters
     * @void
     */
    public function injectUserParameters(array $userParameters = null);
    /**
     * @return WorkConfigInterface
     */
    public function getWorkConfig(): WorkConfigInterface;
}