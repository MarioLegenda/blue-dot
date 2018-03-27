<?php

namespace BlueDot\Database\Model;

interface MetadataInterface
{
    /**
     * @return string
     */
    public function getType(): string;
    /**
     * @return string
     */
    public function getStatementType(): string;
    /**
     * @return string
     */
    public function getResolvedStatementType(): string;
    /**
     * @return string
     */
    public function getStatementName(): string;
    /**
     * @return string
     */
    public function getResolvedStatementName(): string;
}