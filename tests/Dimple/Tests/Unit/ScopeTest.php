<?php
/**
 * This file is part of the Dimple library
 *
 * (c) Wayne Duran <asartalo@projectweb.ph>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dimple\Tests\Unit;

use Dimple\Tests\TestCase;
use Dimple\Scope;

/**
 * Tests some basic web application features
 */
class ScopeTest extends TestCase
{

    /**
     * Setup
     */
    public function setUp()
    {
        $this->container = $this->quickMock('Dimple\Container');
        $this->parent = new Scope('parent', $this->container);
        $this->child = new Scope('child', $this->container, $this->parent);
    }

    /**
     * Scopes have no parents by default
     */
    public function testHasNoParentScopeByDefault()
    {
        $this->assertNull($this->parent->getParent());
    }

    /**
     * Sets the parent on construction
     */
    public function testSetsTheParentOnConstruction()
    {
        $this->assertSame($this->parent, $this->child->getParent());
    }

    /**
     * Sets the name of the scope
     */
    public function testSetsTheNameOnConstruction()
    {
        $this->assertEquals('parent', $this->parent->getName());
    }
    
    /**
     * Retrieves a defined service
     */
    public function testRetrievesAService()
    {
        $this->parent->set('foo', function($c){
            return new \Dimple\Tests\Sample\Foo;
        });
        $this->assertInstanceOf('Dimple\Tests\Sample\Foo', $this->parent->get('foo'));
    }
    
    /**
     * Retrieves same instance of service
     */
    public function testRetrievesSameInstanceOfService()
    {
        $this->parent->set('foo', function($c){
            return new \Dimple\Tests\Sample\Foo;
        });
        $this->assertSame($this->parent->get('foo'), $this->parent->get('foo'));
    }
    
    /**
     * Clearing the cache will create new instances for objects
     */
    public function testCreatesNewInstancesWhenClearingCache()
    {
        $this->parent->set('foo', function($c){
            return new \Dimple\Tests\Sample\Foo;
        });
        $foo1 = $this->parent->get('foo');
        $this->parent->clear();
        $this->assertNotSame($foo1, $this->parent->get('foo'));
    }

}