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
        $this->container = new Pimple;
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
        $this->scopes[$scope] = array($parentScope, array());
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
        return $this->hasScope($scope) ? $this->scopes[$scope][0] : '';
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
    
    public function offsetSet($offset, $value)
    {
        $this->scopes[$this->getCurrentDefinitionScope()][1][] = $offset;
        $this->container[$offset] = $this->container->share($value);
    }
    
    public function offsetExists($offset)
    {
        return $this->container->offsetExists($offset);
    }
    
    public function offsetUnset($offset)
    {
        $this->container->offsetUnset($offset);
    }
    
    public function offsetGet($offset)
    {
        if (!$this->isInScope($offset)) {
            throw new OutOfScope(
                "The service '$offset' cannot be retrieved in current '{$this->getCurrentScope()}' scope "
                . "because it is in a lower scope 'parent'."
            );
        }
        return $this->container[$offset];
    }
    
    public function isInScope($service)
    {
        $currentScopeServices = $this->scopes[$this->getCurrentScope()][1];
        return in_array($service, $currentScopeServices);
    }


}