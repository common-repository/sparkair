<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Options;

class StringOption extends AbstractOption implements OptionInterface
{
    public function getValue($default = null)
    {
        return (string) parent::getValue($default);
    }
    protected function sanitize($value)
    {
        return sanitize_text_field($value);
    }
    protected function validate($value)
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException();
        }
        return $value;
    }
}
