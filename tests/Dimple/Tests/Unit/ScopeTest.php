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
        $this->parent = new Scope('parent');
        $this->child = new Scope('child', $this->parent);
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

}