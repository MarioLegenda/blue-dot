<?php

namespace BlueDot\Configuration\Flow\Simple;

interface MetadataInterface
{
    /**
     * @return string
     */
    public function getStatementType(): string;
    /**
     * @return string
     */
    public function getSqlType(): string;
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