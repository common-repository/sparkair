<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

abstract class AbstractModel
{
    protected static string $tableName;
    protected static string $tablePrefix;
    public function __construct($data)
    {
        $this->checkTable();
        if (\is_array($data)) {
            /** @var ModelInterface $this */
            $this::fromArray($data, $this);
        }
    }
    public static function getTable() : string
    {
        global $wpdb;
        $prefix = '';
        if (!empty(static::$tablePrefix)) {
            $prefix = static::$tablePrefix;
        }
        return $wpdb->prefix . $prefix . static::$tableName;
    }
    public static function fromArray(array $array, ModelInterface $object = null) : ModelInterface
    {
        $class = \get_called_class();
        if (null === $object) {
            $object = new $class();
        }
        foreach (\get_class_vars($class) as $name => $_) {
            if (!isset($array[$name])) {
                continue;
            }
            $object->set($name, $array[$name]);
        }
        return $object;
    }
    public function toArray() : array
    {
        $array = [];
        foreach (\get_object_vars($this) as $name => $value) {
            $array[$name] = $value;
        }
        return $array;
    }
    public function set(string $property, $value)
    {
        $value = $this->sanitize($property, $value);
        // $value = $this->validate($property, $value);
        $this->{$property} = $value;
    }
    public function get(string $property, $default = null)
    {
        if (null !== $default && !$this->{$property}) {
            return $default;
        }
        return $this->{$property};
    }
    protected function checkTable()
    {
        if (empty($this::$tableName)) {
            throw new \Exception('Table not set');
        }
    }
    public function sanitize(string $property, $value)
    {
        $class = \get_class($this);
        $rp = new \ReflectionProperty($class, $property);
        $type = $rp->getType()->getName();
        if ('array' === $type) {
            // $value = $this->sanitizeArrayProperty($property, $value);
            throw new \Exception('Array not supported');
        } else {
            if ('string' === $type) {
                $value = sanitize_text_field($value);
            } else {
                if ('int' === $type) {
                    $value = \filter_var($value, \FILTER_VALIDATE_INT);
                } else {
                    if ('float' === $type) {
                        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);
                    } else {
                        if ('bool' === $type) {
                            $value = \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                }
            }
        }
        return $value;
    }
}
