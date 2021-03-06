<?php

/*
 * This file is part of the puli/repository package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Repository\Resource;

use Assert\Assertion;
use Puli\Repository\Api\ResourceNotFoundException;
use Puli\Repository\Resource\Collection\FilesystemResourceCollection;
use Webmozart\Glob\Iterator\RecursiveDirectoryIterator;

/**
 * Represents a directory on the file system.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DirectoryResource extends AbstractFilesystemResource
{
    /**
     * {@inheritdoc}
     */
    public function __construct($filesystemPath, $path = null)
    {
        Assertion::directory($filesystemPath);

        parent::__construct($filesystemPath, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getChild($relPath)
    {
        // Use attached repository if possible
        if ($this->getRepository()) {
            return $this->getRepository()->get($this->getRepositoryPath().'/'.$relPath);
        }

        $filesystemPath = $this->getFilesystemPath().'/'.$relPath;

        if (!file_exists($filesystemPath)) {
            throw ResourceNotFoundException::forPath($this->getPath().'/'.$relPath);
        }

        return is_dir($filesystemPath)
            ? new DirectoryResource($filesystemPath)
            : new FileResource($filesystemPath);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($relPath)
    {
        // Use attached repository if possible
        if ($this->getRepository()) {
            return $this->getRepository()->contains($this->getRepositoryPath().'/'.$relPath);
        }

        return file_exists($this->getFilesystemPath().'/'.$relPath);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        // Use attached repository if possible
        if ($this->getRepository()) {
            return $this->getRepository()->hasChildren($this->getRepositoryPath());
        }

        $iterator = new RecursiveDirectoryIterator($this->getFilesystemPath());
        $iterator->rewind();

        return $iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function listChildren()
    {
        $children = new FilesystemResourceCollection();

        // Use attached repository if possible
        if ($this->getRepository()) {
            foreach ($this->getRepository()->listChildren($this->getRepositoryPath()) as $child) {
                $children[$child->getName()] = $child;
            }

            return $children;
        }

        $iterator = new RecursiveDirectoryIterator(
            $this->getFilesystemPath(),
            RecursiveDirectoryIterator::CURRENT_AS_FILE
        );

        // We can't use glob() here, because glob() doesn't list files starting
        // with "." by default
        foreach ($iterator as $path => $name) {
            $children[$name] = is_dir($path)
                ? new DirectoryResource($path)
                : new FileResource($path);
        }

        return $children;
    }
}
