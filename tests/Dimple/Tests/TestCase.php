<?php
/**
 * This file is part of the Dimple library
 *
 * (c) Wayne Duran <asartalo@projectweb.ph>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dimple\Tests;

/**
 * A helper class to wrap common test setups in one class for easier testing.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    protected function quickMock($class, array $methods = array())
    {
        return $this->getMock($class, $methods, array(), '', false);
    }

}