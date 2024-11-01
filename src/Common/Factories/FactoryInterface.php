<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Factories;

use Sparkair\SparkPlugins\SparkWoo\Common\Models\ModelInterface;
interface FactoryInterface
{
    public function default() : array;
    public function create(array $data) : ModelInterface;
}
