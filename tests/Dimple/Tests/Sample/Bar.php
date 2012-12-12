<?php
/**
 * This file is part of the Dimple library
 *
 * (c) Wayne Duran <asartalo@projectweb.ph>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dimple\Tests\Sample;

/**
 * Sample classes for testing
 */
class Bar
{
    
    private $foo;
    
    /**
     * Constructor
     * 
     * @param Foo $foo
     */
    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }
    
    /**
     * @return Foo
     */
    public function getFoo()
    {
        return $this->foo;
    }
    
}