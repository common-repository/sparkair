<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

interface ModelInterface
{
    public static function getTable() : string;
    public static function fromArray(array $array) : ModelInterface;
    public function toArray() : array;
    public function set(string $property, $value);
    public function get(string $property, $default = null);
}
