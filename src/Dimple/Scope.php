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

use Closure;

/**
 * A scope container
 */
class Scope
{
    private $name;
    
    private $container;

    private $parent;
    
    private $values = array();
    
    private $cache = array();

    /**
     * Constructor
     *
     * @param string    $name      the name of the scope
     * @param Container $container the container
     * @param Scope     $parent    the parent scope
     */
    public function __construct($name, Container $container, Scope $parent = null)
    {
        $this->name = $name;
        $this->container = $container;
        $this->parent = $parent;
    }

    /**
     * Returns the parent of the scope
     *
     * @return Scope the parent scope
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the name of the scope
     *
     * @return string the name of the scope
     */
    public function getName()
    {
        return $this->name;
    }

    public function get($id)
    {
        if (!$this->has($id) && $this->getParent()) {
            return $this->getParent()->get($id);
        }
        
        if (!array_key_exists($id, $this->cache)) {
            if ($this->values[$id] instanceof Closure) {
                $this->cache[$id] = $this->values[$id]($this->container);
            } else {
                $this->cache[$id] = &$this->values[$id];
            }
        }

        return $this->cache[$id];
    }
    
    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }
    
    public function has($id)
    {
        return array_key_exists($id, $this->values);
    }
    
    /**
     * Clears object cache
     */
    public function clear()
    {
        $this->cache = array();
    }

}