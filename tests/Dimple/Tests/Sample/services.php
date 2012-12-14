<?php


// Start definitions
$container['foo'] = function($container) {
    return new \Dimple\Tests\Sample\Foo;
};