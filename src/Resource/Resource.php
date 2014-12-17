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

use Puli\Repository\ResourceRepository;
use Puli\Repository\UnsupportedResourceException;
use Serializable;

/**
 * A resource.
 *
 * Resources are objects which can be stored in a resource repository. Resources
 * have a path, under which they are stored in the repository.
 *
 * Depending on the implementation, resources may offer additional functionality:
 *
 *  * Resources that are similar to files in that they have a body and a size
 *    should implement {@link FileResource}.
 *  * Resources that contain other resources should implement
 *    {@link DirectoryResource}.
 *
 * Resources can be attached to a repository by calling {@link attachTo()}. They
 * can be detached again by calling {@link detach()}. Use {@link isAttached()}
 * to find out whether a resource is attached and {@link getRepository()} to
 * obtain the attached repository.
 *
 * You can create a reference to a resource by calling {@link createReference()}.
 * References can have different paths than the resource they are referencing.
 * Otherwise, they are identical to the referenced resource. Use
 * {@link isReference()} to check whether a resource is a reference. You can
 * call {@link getRepositoryPath()} to retrieve the path of the referenced
 * resource.
 *
 * If you implement a custom resource, let your test extend
 * {@link AbstractResourceTest} to make sure your resource satisfies the
 * constraints of the interface. Extend {@link AbstractResource} if you want to
 * avoid reimplementing basic functionality.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Resource extends Serializable
{
    /**
     * Returns the path of the resource.
     *
     * For references created with {@link createReference()}, the path returned
     * by this method is the reference path and not the actual repository path
     * of the referenced resource. You should use {@link getRepositoryPath()} if
     * you want to query the repository for a resource.
     *
     * @return string|null The path of the resource. If the resource has no
     *                     path, `null` is returned.
     */
    public function getPath();

    /**
     * Returns the name of the resource.
     *
     * The name is the last segment of the path returned by {@link getPath()}.
     *
     * @return string|null The name of the resource. If the resource has no
     *                     path, `null` is returned.
     */
    public function getName();

    /**
     * Returns the repository that the resource is attached to.
     *
     * Use {@link attachTo()} to attach a resource to a repository. The method
     * {@link detach()} can be used to detach an attached resource.
     *
     * @return ResourceRepository|null The resource repository. If the resource
     *                                 is not attached to any repository, `null`
     *                                 is returned.
     */
    public function getRepository();

    /**
     * Returns the path of the resource in the repository.
     *
     * The repository path is the path that the resource is mapped to once
     * attached to a repository. The result of this method is different from
     * {@link getPath()} for resource references. Resource references return
     * the path of the referenced resource here, while {@link getPath()} returns
     * the path of the reference itself.
     *
     * @return string|null The repository path of the resource. If the resource
     *                     has no repository path, `null` is returned.
     */
    public function getRepositoryPath();

    /**
     * Attaches the resource to a repository.
     *
     * You can optionally change the path of the resource by passing it in the
     * second argument. Beware that this may break the resource if it is still
     * referenced by another repository. Hence you should clone resources that
     * are attached to another repository before attaching them:
     *
     * ```php
     * if ($resource->isAttached()) {
     *     $resource = clone $resource;
     * }
     *
     * $resource->attachTo($repo, '/path/in/repo');
     * ```
     *
     * @param ResourceRepository $repo The repository.
     * @param string|null        $path The path of the resource in the
     *                                 repository. If not passed, the resource
     *                                 will be attached to it current path.
     */
    public function attachTo(ResourceRepository $repo, $path = null);

    /**
     * Detaches the resource from the repository.
     *
     * After calling this method, {@link isAttached()} returns `false`. The
     * method {@link getRepository()} should return `null` after detaching.
     *
     * Neither the path nor the repository path of the resource should be
     * modified when detaching.
     */
    public function detach();

    /**
     * Returns whether the resource is attached to a repository.
     *
     * Resources can be attached to a repository with {@link attachTo()}. The
     * method {@link getRepository()} returns the attached repository.
     *
     * @return bool Whether the resource is attached to a repository.
     */
    public function isAttached();

    /**
     * Overrides another resource with this resource.
     *
     * This method is called when two different resources are added to the same
     * path in the same repository:
     *
     * ```php
     * use Puli\Repository\InMemoryRepository;
     *
     * $repo = new InMemoryRepository();
     * $repo->add('/path', $resource1);
     * $repo->add('/path', $resource2);
     *
     * // $resource2->override($resource1) is called
     * ```
     *
     * Implementations should decide whether and how to change the state of the
     * resource to incorporate the state of the overridden resource.
     *
     * @param Resource $resource The overridden resource.
     *
     * @throws UnsupportedResourceException If the resource cannot be overridden.
     */
    public function override(Resource $resource);

    /**
     * Creates a reference to the resource.
     *
     * References are identical for their referenced resource except for their
     * path. The path of the referenced resource can be obtained by calling
     * {@link getRepositoryPath()}:
     *
     * ```php
     * $resource = new MyResource('/path');
     * $reference = $resource->createReference('/reference');
     *
     * $reference->getPath();
     * // "/reference"
     *
     * $reference->getRepositoryPath();
     * // "/path"
     * ```
     *
     * Use {@link isReference()} to find out whether a resource is a reference.
     *
     * @param string $path The path of the reference.
     *
     * @return static The reference.
     */
    public function createReference($path);

    /**
     * Returns whether a resource is a reference.
     *
     * References are created by calling {@link createReference()}.
     *
     * @return bool Whether the resource is a reference.
     */
    public function isReference();
}