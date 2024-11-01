<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Options;

interface OptionInterface
{
    public function getName() : string;
    public function getPrefix() : string;
    public function getNameWithoutPrefix() : string;
    public function getValue($default = null);
    public function setValue($value) : void;
}
