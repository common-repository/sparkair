<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

class ActivationPageButton implements \JsonSerializable
{
    public string $text;
    public ?string $url = null;
    public ?array $routerObject = null;
    public bool $hidden = \false;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function getText() : string
    {
        return $this->text;
    }
    public function setText(string $text) : void
    {
        $this->text = $text;
    }
    public function getUrl() : string
    {
        return $this->url;
    }
    public function getRouterObject() : array
    {
        return $this->routerObject;
    }
    public function setRouterObject(array $routerObject) : void
    {
        $this->routerObject = $routerObject;
    }
    public function jsonSerialize() : array
    {
        $json = array();
        $class = \get_class($this);
        foreach (\get_class_vars($class) as $name => $_) {
            $json[$name] = $this->{$name};
        }
        return $json;
    }
}
