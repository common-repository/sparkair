<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Factories;

use Sparkair\SparkPlugins\SparkWoo\Common\Models\ModelInterface;
abstract class AbstractFactory
{
    protected static string $model;
    protected function default() : array
    {
        return array();
    }
    public function create(array $data = array()) : ModelInterface
    {
        $this->checkModel();
        $model = static::$model;
        $instance = new $model(\array_merge($this->default(), $data));
        return $instance;
    }
    protected function checkModel()
    {
        if (empty($this::$model)) {
            throw new \Exception('Model not set');
        }
    }
}
