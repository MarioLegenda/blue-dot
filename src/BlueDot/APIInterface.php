<?php

namespace BlueDot;

interface APIInterface
{
    /**
     * @param string $resource
     * @return APIInterface
     */
    public function putAPI(string $resource) : APIInterface;
    /**
     * @param string $apiName
     * @return string
     */
    public function useAPI(string $apiName) : string;
    /**
     * @return array
     */
    public function getWorkingAPIs() : array;
    /**
     * @return array
     */
    public function getFiles() : array;
    /**
     * @return array
     */
    public function getDirs() : array;
}