<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Options;

abstract class AbstractOption implements \JsonSerializable
{
    private string $prefix;
    private string $name;
    public function __construct($prefix, $name)
    {
        $this->name = $name;
        $this->prefix = $prefix;
    }
    public function getName() : string
    {
        return $this->prefix . $this->name;
    }
    public function getNameWithoutPrefix() : string
    {
        return $this->name;
    }
    public function getPrefix() : string
    {
        return $this->prefix;
    }
    public function getValue($default = null)
    {
        return get_option($this->getName(), $default);
    }
    public function setValue($value) : void
    {
        try {
            $value = $this->sanitize($value);
            $value = $this->validate($value);
            update_option($this->getName(), $value);
        } catch (\Exception $e) {
            // pass
        }
    }
    public function delete() : void
    {
        delete_option($this->getName());
    }
    public function jsonSerialize() : array
    {
        return array('value' => $this->getValue(), 'name' => $this->getName(), 'prefix' => $this->getPrefix(), 'nameWithoutPrefix' => $this->getNameWithoutPrefix());
    }
    protected abstract function sanitize($value);
    protected abstract function validate($value);
}
