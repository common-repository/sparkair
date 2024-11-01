<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

class ActivationPageGettingStartedItem implements \JsonSerializable
{
    public string $payoff;
    public string $title;
    public string $description;
    public ?string $image;
    public ?ActivationPageButton $button;
    public bool $hidden = \false;
    public function __construct($payoff, $title, $description, $image = null, $button = null)
    {
        $this->payoff = $payoff;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->button = $button;
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
