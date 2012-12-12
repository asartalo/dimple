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
 * A scope container
 */
class Scope extends Pimple
{
    private $name;

    private $parent;

    /**
     * Constructor
     *
     * @param string $name   the name of the scope
     * @param Scope  $parent the parent scope
     */
    public function __construct($name, Scope $parent = null)
    {
        $this->name = $name;
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

    public function offsetGet($id)
    {
        if (!$this->offsetExists($id) && $this->getParent()) {
            return $this->getParent()->offsetGet($id);
        }

        return parent::offsetGet($id);
    }

}