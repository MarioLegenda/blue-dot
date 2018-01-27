<?php

namespace BlueDot\Repository;

use BlueDot\Exception\RepositoryException;

class Repository implements RepositoryInterface, \Countable
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
     * @var null $currentlyUsingRepository
     */
    private $currentlyUsingRepository = null;
    /**
     * @var array $repositories
     */
    private $repositories = array();
    /**
     * @param string $resource
     * @return RepositoryInterface
     * @throws RepositoryException
     */
    public function putRepository(string $resource) : RepositoryInterface
    {
        if (!is_file($resource) and !is_dir($resource)) {
            throw new RepositoryException(
                sprintf('Invalid repository file or directory. \'%s\' is not a file or a directory', $resource)
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
     * @param string $repository
     * @return string
     * @throws RepositoryException
     */
    public function useRepository(string $repository) : string
    {
        if (!array_key_exists($repository, $this->repositories)) {
            throw new RepositoryException(
                sprintf(
                    'Invalid repository name. Repository \'%s\' does not exist', $repository
                )
            );
        }

        $this->currentlyUsingRepository = $repository;

        return $this->repositories[$repository];
    }
    /**
     * @param string $repository
     * @return bool
     */
    public function hasRepository(string $repository) : bool
    {
        return array_key_exists($repository, $this->repositories);
    }
    /**
     * @return array
     */
    public function getWorkingRepositories() : array
    {
        return $this->repositories;
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
     * @return string|null
     */
    public function getCurrentlyUsingRepository(): ?string
    {
        return $this->currentlyUsingRepository;
    }
    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->repositories);
    }
    /**
     * @param \SplFileInfo $resource
     * @return RepositoryInterface
     * @throws RepositoryException
     */
    private function putFile(\SplFileInfo $resource) : RepositoryInterface
    {
        $repository = explode('.', $resource->getFilename())[0];

        if (array_key_exists($repository, $this->repositories)) {
            throw new RepositoryException(
                sprintf(
                    'Invalid repository configuration. Repository \'%s\' already exists under path \'%s\'',
                    $repository,
                    $this->repositories[$repository]
                )
            );
        }

        $path = realpath($resource->getPathname());

        $this->files[] = $path;

        $this->repositories[$repository] = $path;

        return $this;
    }
    /**
     * @param string $dir
     * @return RepositoryInterface
     * @throws RepositoryException
     */
    private function putDir(string $dir) : RepositoryInterface
    {
        if (!is_dir($dir)) {
            throw new RepositoryException(
                sprintf('Invalid repository directory. \'%s\' is not a directory', $dir)
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