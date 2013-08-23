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
class Injection
{
    private $injected;

    /**
     * Constructor
     *
     * @param mixed $injected
     *
     * @inject bar
     */
    public function __construct($injected)
    {
        $this->injected = $injected;
    }

    /**
     * @return mixed the injected value
     */
    public function getInjected()
    {
        return $this->injected;
    }

}