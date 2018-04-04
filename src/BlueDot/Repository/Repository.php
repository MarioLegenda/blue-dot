<?php

namespace BlueDot\Repository;

class Repository implements RepositoryInterface, \Countable, \IteratorAggregate
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
     * @inheritdoc
     */
    public function putRepository(string $resource) : RepositoryInterface
    {
        if (!is_file($resource) and !is_dir($resource)) {
            $message = sprintf(
                'Invalid repository file or directory. \'%s\' is not a file or a directory',
                $resource
            );

            throw new \RuntimeException($message);
        }

        if (is_file($resource)) {
            $this->putFile(new \SplFileInfo($resource));
        } else if (is_dir($resource)) {
            $this->putDir($resource);
        }

        return $this;
    }
    /**
     * @inheritdoc
     * @internal
     */
    public function useRepository(string $repository) : string
    {
        if (!array_key_exists($repository, $this->repositories)) {
            $message = sprintf(
                'Invalid repository name. Repository \'%s\' does not exist',
                $repository
            );

            throw new \RuntimeException($message);
        }

        $this->currentlyUsingRepository = $repository;

        return $this->repositories[$repository];
    }
    /**
     * @inheritdoc
     */
    public function hasRepository(string $repository) : bool
    {
        return array_key_exists($repository, $this->repositories);
    }
    /**
     * @inheritdoc
     */
    public function getWorkingRepositories() : array
    {
        return $this->repositories;
    }
    /**
     * @inheritdoc
     */
    public function getFiles() : array
    {
        return $this->files;
    }
    /**
     * @inheritdoc
     */
    public function getDirs() : array
    {
        return $this->dirs;
    }
    /**
     * @inheritdoc
     */
    public function getCurrentlyUsingRepository(): ?string
    {
        return $this->currentlyUsingRepository;
    }
    /**
     * @inheritdoc
     */
    public function isCurrentlyUsingRepository(string $repository): bool
    {
        return $this->getCurrentlyUsingRepository() === $repository;
    }
    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->repositories);
    }
    /**
     * @param \SplFileInfo $resource
     * @return RepositoryInterface
     * @throws \RuntimeException
     */
    private function putFile(\SplFileInfo $resource) : RepositoryInterface
    {
        $repository = explode('.', $resource->getFilename())[0];

        if (array_key_exists($repository, $this->repositories)) {
            $message = sprintf(
                'Invalid repository configuration. Repository \'%s\' already exists under path \'%s\'',
                $repository,
                $this->repositories[$repository]
            );

            throw new \RuntimeException($message);
        }

        $path = realpath($resource->getPathname());

        $this->files[] = $path;

        $this->repositories[$repository] = $path;

        return $this;
    }
    /**
     * @param string $dir
     * @return RepositoryInterface
     * @throws \RuntimeException
     */
    private function putDir(string $dir) : RepositoryInterface
    {
        if (!is_dir($dir)) {
            $message = sprintf(
                'Invalid repository directory. \'%s\' is not a directory',
                $dir
            );

            throw new \RuntimeException($message);
        }

        foreach (new \DirectoryIterator($dir) as $resource) {
            if ($resource->isFile() and $resource->getExtension() === 'yml') {
                $this->putFile($resource);
            }
        }

        $this->dirs[] = $dir;

        return $this;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getWorkingRepositories());
    }
}