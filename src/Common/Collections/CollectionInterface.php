<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Collections;

interface CollectionInterface extends \Iterator
{
    public function getItems() : array;
    public function setItems(array $items);
    public function map(callable $func);
    public function filter(callable $func);
    public function getItemsBy($key, $value) : array;
    public function getItemBy($key, $value);
    public function isEmpty() : bool;
}
