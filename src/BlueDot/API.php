<?php

namespace BlueDot;

use BlueDot\Exception\APIException;

class API implements APIInterface
{
    /**
     * @var array $files
     */
    private $files = array();
    /**
     * @var array $dirs
     */
    private $dirs = array();
    /**
     * @var array $api
     */
    private $api = array();
    /**
     * @param string $resource
     * @return APIInterface
     * @throws APIException
     */
    public function putAPI(string $resource) : APIInterface
    {
        if (!is_file($resource) and !is_dir($resource)) {
            throw new APIException(
                sprintf('Invalid API file or directory. %s is not a file or a directory', $resource)
            );
        }

        if (is_file($resource)) {
            $this->putFile(new \SplFileInfo($resource));
        } else if (is_dir($resource)) {
            $this->putDir($resource);
        }

        return $this;
    }
    /**
     * @param string $apiName
     * @return string
     * @throws APIException
     */
    public function useAPI(string $apiName) : string
    {
        if (!array_key_exists($apiName, $this->api)) {
            throw new APIException(
                sprintf(
                    'Invalid API name. API \'%s\' does not exist', $apiName
                )
            );
        }

        return $this->api[$apiName];
    }
    /**
     * @param string $apiName
     * @return bool
     */
    public function hasAPI(string $apiName) : bool
    {
        return array_key_exists($apiName, $this->api);
    }
    /**
     * @return array
     */
    public function getWorkingAPIs() : array
    {
        return $this->api;
    }
    /**
     * @return array
     */
    public function getFiles() : array
    {
        return $this->files;
    }
    /**
     * @return array
     */
    public function getDirs() : array
    {
        return $this->dirs;
    }
    /**
     * @param \SplFileInfo $resource
     * @return API
     * @throws APIException
     */
    private function putFile(\SplFileInfo $resource) : API
    {
        $apiName = explode('.', $resource->getFilename())[0];

        if (array_key_exists($apiName, $this->api)) {
            throw new APIException(
                sprintf(
                    'Invalid API configuration. API \'%s\' already exists under path %s',
                    $apiName,
                    $this->api[$apiName]
                )
            );
        }

        $path = realpath($resource->getPathname());

        $this->files[] = $path;

        $this->api[$apiName] = $path;

        return $this;
    }
    /**
     * @param string $dir
     * @return API
     * @throws APIException
     */
    private function putDir(string $dir) : API
    {
        if (!is_dir($dir)) {
            throw new APIException(
                sprintf('Invalid API directory. %s is not a directory', $dir)
            );
        }

        foreach (new \DirectoryIterator($dir) as $resource) {
            if ($resource->isFile() and $resource->getExtension() === 'yml') {
                $this->putFile($resource);
            }
        }

        $this->dirs[] = $dir;

        return $this;
    }
}