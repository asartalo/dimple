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
class Baz
{
    
    private $bar;
    
    /**
     * Constructor
     * 
     * @param Bar $bar
     */
    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
    
    /**
     * @return Bar
     */
    public function getBar()
    {
        return $this->bar;
    }
    
}