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
use Dimple\Container;

/**
 * Container specifications
 */
class ContainerTest extends TestCase
{

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->container = new Container(function($c) {
            // Create scopes first
            $c->createScope('parent');
            $c->createScope('child', 'parent');

            $c['foo'] = $c->auto('Dimple\Tests\Sample\Foo');

            $c->scope('parent');
            $c['bar'] = function($c) {
                return new \Dimple\Tests\Sample\Bar($c['foo']);
            };

            $c->scope('child');
            $c['injection'] = $c->auto('Dimple\Tests\Sample\Injection');
        });
    }

    /**
     * Can set services automatically
     */
    public function testAutomaticServiceSetting()
    {
        $this->assertInstanceOf('Dimple\Tests\Sample\Foo', $this->container['foo']);
    }

    /**
     * Can inject services through doc comments
     */
    public function testInjectingServicesThroughDocComments()
    {
        $this->container->enterScope('child');
        $obj = $this->container['injection'];
        $this->assertInstanceOf('Dimple\Tests\Sample\Bar', $obj->getInjected());
    }

    /**
     * Can auto-inject services lazily by passing a reference to auto
     *
     * @return void
     */
    public function testInjectAutomaticServiceWithLazyClassName()
    {
        $this->container->extend(function($c) {
            $c['injection.class'] = function($c) {
                return 'Dimple\Tests\Sample\Injection';
            };

            $c->scope('child');
            $c['injection'] = $c->auto('injection.class');
        });

        $this->container->enterScope('child');
        $obj = $this->container['injection'];
        $this->assertInstanceOf('Dimple\Tests\Sample\Bar', $obj->getInjected());
    }

}