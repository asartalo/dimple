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

use Pimple;


/**
 * A Dependency Injection Container
 */
class Container implements \ArrayAccess
{
    private $scopes = array();

    /**
     * Constructor
     * 
     * @param callable $callable setup method
     */
    public function __construct($callable)
    {
        $this->scopes['container'] = null;
        $this->container = new Pimple;
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
    public function createScope($scope, $parentScope = 'container')
    {
        $this->scopes[$scope] = $parentScope;
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
        return $this->hasScope($scope) ? $this->scopes[$scope] : '';
    }
    
    public function offsetSet($offset, $value)
    {
        $this->container[$offset] = $this->container->share($value);
    }
    
    public function offsetExists($offset)
    {
        return;
    }
    
    public function offsetUnset($offset)
    {
        //unset($this->container[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return $this->container[$offset];
    }


}