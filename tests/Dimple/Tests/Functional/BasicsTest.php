<?php
/**
 * This file is part of the Dimple library
 *
 * (c) Wayne Duran <asartalo@projectweb.ph>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dimple\Tests\Functional;

use Dimple\Tests\TestCase;
use Dimple\Container;

/**
 * Tests some basic web application features
 */
class BasicsTest extends TestCase
{
    
    /**
     * Setup
     */
    public function setUp()
    {
        $this->container = new Container(function($c){
            // Create scopes first
            $c->createScope('parent');
            $c->createScope('child', 'parent');
            
            // Start definitions
            $c['foo'] = function($c) {
                return new \Dimple\Tests\Sample\Foo;
            };
        });
    }
    
    /**
     * Test basic instantiation
     */
    public function testBasicInstantiation()
    {
        $this->assertInstanceOf('Dimple\Tests\Sample\Foo', $this->container['foo']);
    }
    
    /**
     * Container returns a single instance
     */
    public function testReturnsSameInstance()
    {
        $foo1 = $this->container['foo'];
        $foo2 = $this->container['foo'];
        $this->assertSame($foo1, $foo2);
    }
    
    /**
     * Container has default container scope
     */
    public function testHasDefaultContainerScope()
    {
        $this->assertTrue($this->container->hasScope('container'));
    }
    
    /**
     * Container can create scopes
     */
    public function testCanCreateScope()
    {
        $this->assertTrue($this->container->hasScope('parent'));
    }
    
    /**
     * Returns false when checking if a non-existent scope exists
     */
    public function testHasDefaultReturnsFalseForUnknownScope()
    {
        $this->assertFalse($this->container->hasScope('fooScope'));
    }
    
    /**
     * New scopes' default parent scope is container
     */
    public function testDefaultParentScopeIsContainer()
    {
        $this->assertEquals('container', $this->container->getParentScope('parent'));
    }
    
    /**
     * Can set parent scope
     */
    public function testSettingParentScope()
    {
        $this->assertEquals('parent', $this->container->getParentScope('child'));
    }
    
}