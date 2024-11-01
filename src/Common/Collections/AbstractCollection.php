<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Collections;

use Countable;
use JsonSerializable;
use Traversable;
abstract class AbstractCollection implements JsonSerializable, Countable
{
    protected array $items;
    public function __construct($items)
    {
        $this->items = \is_array($items) ? $items : \iterator_to_array($items);
    }
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        return \reset($this->items);
    }
    #[\ReturnTypeWillChange]
    public function current()
    {
        return \current($this->items);
    }
    #[\ReturnTypeWillChange]
    public function key()
    {
        return \key($this->items);
    }
    #[\ReturnTypeWillChange]
    public function next()
    {
        return \next($this->items);
    }
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return \key($this->items) !== null;
    }
    public function setItems(array $items)
    {
        $this->items = $items;
    }
    public function getItems() : array
    {
        return $this->items;
    }
    public function count() : int
    {
        return \count($this->items);
    }
    public function getItemsBy($key, $value) : array
    {
        return \array_filter($this->items, function ($item) use($key, $value) {
            $item->{$key} == $value;
        });
    }
    public function getItemBy($key, $value)
    {
        foreach ($this->items as $item) {
            if ($item->{$key} == $value) {
                return $item;
            }
        }
        return null;
    }
    public function jsonSerialize() : array
    {
        $json = array();
        foreach ($this->items as $item) {
            $json[] = $item;
        }
        return $json;
    }
    public function map(callable $func)
    {
        return \array_map($func, $this->items);
    }
    public function filter(callable $func)
    {
        $this->items = \array_filter($this->items, $func);
        return $this;
    }
    public function isEmpty() : bool
    {
        return empty($this->items);
    }
}
