<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Activation;

use JsonSerializable;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\OptionInterface;
class ActivationPageContent implements JsonSerializable
{
    /**
     * @var ActivationPageGettingStartedItem[]
     */
    private array $gettingStartedItems = array();
    /**
     * @var ActivationPageButton[]
     */
    private array $buttons;
    private ?OptionInterface $licenseKeyOption = null;
    public function __construct(array $gettingStartedItems, array $buttons, OptionInterface $licenseKeyOption = null)
    {
        $this->gettingStartedItems = \array_filter($gettingStartedItems, function ($item) {
            return !$item->hidden;
        });
        $this->buttons = \array_filter($buttons, function ($item) {
            return !$item->hidden;
        });
        $this->licenseKeyOption = $licenseKeyOption;
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
