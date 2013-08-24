<?php
/**
 * This file is part of the Dimple library
 *
 * (c) Wayne Duran <asartalo@projectweb.ph>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dimple;

use Dimple\Exception\OutOfScope;
use ReflectionClass;
use Anodoc;


/**
 * A Dependency Injection Container
 */
class Container implements \ArrayAccess
{
    private $scopes = array();

    private $defaultScope = 'container';

    private $currentDefinitionScope;

    private $currentScope;

    private $docParser;

    /**
     * Constructor
     *
     * @param mixed $services container setup service definitions
     */
    public function __construct($services)
    {
        $this->createScope($this->defaultScope, null);
        $this->scope($this->defaultScope);
        $this->extend($services);
        $this->enterScope($this->defaultScope);
    }

    /**
     * Extends the service definitions
     *
     * @param mixed $services container setup service definitions
     */
    public function extend($services)
    {
        if (!$services instanceof \Closure && file_exists($services)) {
            $callable = function($container) use ($services) {
                include $services;
            };
        } else {
            $callable = $services;
        }

        $callable($this);
    }

    /**
     * Checks to see if a scope is defined
     *
     * @param string $scope the name of the scope
     *
     * @return boolean whether the scope is defined or not
     */
    public function hasScope($scope)
    {
        return array_key_exists($scope, $this->scopes);
    }

    /**
     * Creates a scope
     *
     * @param string $scope       the name of the scope to be created
     * @param string $parentScope the name of the parent scope (default 'container')
     */
    public function createScope($scope, $parentScope = '')
    {
        if (empty($parentScope) && $parentScope === '') {
            $parentScope = $this->defaultScope;
        }

        if ($this->hasScope($parentScope)) {
            $this->scopes[$scope] = new Scope($scope, $this, $this->scopes[$parentScope]);
        } else {
            $this->scopes[$scope] = new Scope($scope, $this);
        }
    }

    /**
     * Returns the parent scope of the scope specified
     *
     * @param string $scope the name of the scope
     *
     * @return string the name of the parent scope
     */
    public function getParentScope($scope)
    {
        if ($this->hasScope($scope)) {
            $scopeObject = $this->scopes[$scope];

            return is_null($scopeObject->getParent()) ? null : $scopeObject->getParent()->getName();
        }
    }

    /**
     * Sets the current scope to be used for the next definitions
     *
     * @param string $scope the name of the scope
     */
    public function scope($scope)
    {
        $this->currentDefinitionScope = $scope;
    }

    /**
     * Get current definition scope
     *
     * @return string returns the current definition scope
     */
    public function getCurrentDefinitionScope()
    {
        return $this->currentDefinitionScope;
    }

    /**
     * Enters the scope for retrieval purpose only
     *
     * @param string $scope the name of the scope to enter to
     */
    public function enterScope($scope)
    {
        $this->currentScope = $scope;
    }

    /**
     * Leave the scope
     */
    public function leaveScope()
    {
        $this->getCurrentScopeContainer()->clear();
        $parentScope = $this->getCurrentScopeContainer()->getParent()->getName();
        $this->currentScope = $parentScope;
    }

    /**
     * Gets the current retrieval scope
     *
     * @return string the name of the current scope
     */
    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    private function getCurrentDefinitionScopeContainer()
    {
        return $this->scopes[$this->getCurrentDefinitionScope()];
    }

    private function getCurrentScopeContainer()
    {
        return $this->scopes[$this->getCurrentScope()];
    }

    /**
     * Defines a service or parameter
     *
     * @param string $service the service name
     * @param mixed  $value   a factory defined as a closure or a straight value
     */
    public function offsetSet($service, $value)
    {
        $container = $this->getCurrentDefinitionScopeContainer();
        $container->set($service, $value);
    }

    /**
     * Check if a service exists through array access
     *
     * @param string $service the service name
     *
     * @return boolean wether the service exists
     */
    public function offsetExists($service)
    {
        return $this->getCurrentScopeContainer()->has($service);
    }

    /**
     * Removes a service through array access
     *
     * @param string $service the service name
     */
    public function offsetUnset($service)
    {
        $this->getCurrentScopeContainer()->offsetUnset($service);
    }

    /**
     * Automatically defines a service using doc comments
     *
     * @param string $name either a class name or a reference to a service that
     *                     obtains a class name
     *
     * @todo See if this should be cached
     *
     * @return Closure
     */
    public function auto($name)
    {
        return function($container) use ($name) {
            $name = $container->offsetExists($name) ? $this->get($name) : $name;
            $reflector = $container->getReflection($name);
            $dependencies = $container->getDependencies($name);

            return $reflector->newInstanceArgs(
                $container->getInstances($container, $dependencies)
            );
        };
    }

    protected function getReflection($name)
    {
        return new ReflectionClass($name);
    }

    protected function getDependencies($name)
    {
        $constructorDoc = $this->getDocParser()
                               ->getDoc($name)
                               ->getMethodDoc('__construct');
        $injects = $constructorDoc->getTags('inject');
        $dependencies = array();
        foreach ($injects as $depedency) {
            $dependencies[] = $depedency->getValue();
        }

        return $dependencies;
    }

    /**
     * Loads an array of dependencies
     *
     * @param mixed $container    a container object
     * @param mixed $dependencies a collection of dependencies
     *
     * @return array array as dependencies
     */
    public function getInstances($container, $dependencies)
    {
        $instances = array();
        foreach ($dependencies as $serviceName) {
            $instances[] = $container[$serviceName];
        }

        return $instances;
    }


    /**
     * Retrieves a service or parameter
     *
     * @param string $service the name of the service
     *
     * @return mixed the service or parameter to be retrieved
     */
    public function get($service)
    {
        $container = $this->getCurrentScopeContainer();
        while (!$container->has($service)) {
            $container = $container->getParent();
            if (!$container) {
                throw new OutOfScope(
                    "The service '$service' cannot be retrieved in current "
                    . "'{$this->getCurrentScope()}' scope."
                );
            }
        }

        return $container->get($service);
    }

    /**
     * Retrieves a service or parameter using array access
     *
     * @param string $service the name of the service or parameter
     *
     * @return mixed the service or parameter to be retrieved
     */
    public function offsetGet($service)
    {
        return $this->get($service);
    }

    /**
     * See if the service or parameter is accessible within the current scope
     *
     * @param string $service the name of the service or the parameter
     *
     * @return boolean
     */
    public function isInScope($service)
    {
        $currentScopeServices = $this->scopes[$this->getCurrentScope()][1];

        return in_array($service, $currentScopeServices);
    }

    private function getDocParser()
    {
        if (!$this->docParser) {
            $this->docParser = Anodoc::getNew();
        }

        return $this->docParser;
    }


}