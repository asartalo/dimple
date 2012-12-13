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
     *
     * @return void
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

            // Set scope for next definitions
            $c->scope('parent');

            $c['bar'] = function($scope) {
                return new \Dimple\Tests\Sample\Bar($scope['foo']);
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
     * Get method alias
     */
    public function testGetMethodAlias()
    {
        $this->assertInstanceOf('Dimple\Tests\Sample\Foo', $this->container->get('foo'));
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

    /**
     * Instantiates object inside a scope
     */
    public function testInstantiatesObjectInsideScope()
    {
        $this->container->enterScope('parent');
        $this->assertInstanceOf('Dimple\Tests\Sample\Bar', $this->container['bar']);
    }

    /**
     * Cannot instantiate objects from a different scope without entering the first
     */
    public function testInstantiatesObjectInDifferentScopeThrowsException()
    {
        $this->setExpectedException(
            'Dimple\Exception\OutOfScope',
            "The service 'bar' cannot be retrieved in current 'container' scope."
        );
        $this->container['bar'];
    }

    /**
     * Leaving a child scope returns to the parent scope
     */
    public function testLeavingChildScopeReturnsToParentScope()
    {
        $this->container->enterScope('parent');
        $this->container->enterScope('child');
        $this->container->leaveScope();
        $this->assertEquals('parent', $this->container->getCurrentScope());
    }
    
    /**
     * Leaving a child scope clears cached objects for that scope
     */
    public function testLeavingScopeClearsCachedObjectsForThatScope()
    {
        $this->container->enterScope('parent');
        $bar1 = $this->container['bar'];
        $this->container->leaveScope();
        $this->container->enterScope('parent');
        $bar2 = $this->container['bar'];
        $this->assertNotSame($bar1, $bar2);
    }

    /**
     * Consecutive retrieval of service on same scope returns same object
     */
    public function testGettingSameServiceOnSameScopeReturnsSameObject()
    {
        $this->container->enterScope('parent');
        $bar1 = $this->container['bar'];
        $bar2 = $this->container['bar'];
        $this->assertSame($bar1, $bar2);
    }
}