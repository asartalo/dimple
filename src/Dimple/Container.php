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
use Pimple;


/**
 * A Dependency Injection Container
 */
class Container implements \ArrayAccess
{
    private $scopes = array();

    private $defaultScope = 'container';

    private $currentDefinitionScope;

    private $currentScope;

    /**
     * Constructor
     *
     * @param callable $callable setup method
     */
    public function __construct($callable)
    {
        $this->createScope($this->defaultScope, null);
        $this->scope($this->defaultScope);
        $callable($this);

        $this->enterScope($this->defaultScope);
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

    public function offsetExists($service)
    {
        return $this->getCurrentScopeContainer()->has($service);
    }

    public function offsetUnset($service)
    {
        $this->getCurrentScopeContainer()->offsetUnset($service);
    }

    /**
     *
     */
    public function offsetGet($service)
    {
        $container = $this->getCurrentScopeContainer();
        while (!$container->has($service)) {
            $container = $container->getParent();
            if (!$container) {
                throw new OutOfScope(
                    "The service '$service' cannot be retrieved in current "
                    . "'{$this->getCurrentScope()}' scope "
                    . "because it is in a lower scope 'parent'."
                );
            }
        }

        return $container->get($service);
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


}