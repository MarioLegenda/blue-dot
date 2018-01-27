<?php

namespace BlueDot\Repository;

interface RepositoryInterface
{
    /**
     * @param string $resource
     * @return RepositoryInterface
     */
    public function putRepository(string $resource) : RepositoryInterface;
    /**
     * @param string $apiName
     * @return string
     */
    public function useRepository(string $apiName) : string;
    /**
     * @param string $apiName
     * @return bool
     */
    public function hasRepository(string $apiName) : bool;
    /**
     * @return array
     */
    public function getWorkingRepositories() : array;
    /**
     * @return array
     */
    public function getFiles() : array;
    /**
     * @return array
     */
    public function getDirs() : array;
    /**
     * @return string|null
     */
    public function getCurrentlyUsingRepository();
}