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
            $c['foo'] = $c->auto('Dimple\Tests\Sample\Foo');
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
     *
     * @return void
     */
    public function testInjectingServicesThroughDocComments()
    {
        $container = new Container(function($c){
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

        $container->enterScope('child');
        $obj = $container['injection'];
        $this->assertInstanceOf('Dimple\Tests\Sample\Bar', $obj->getInjected());
    }

}