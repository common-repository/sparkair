<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Options;

class BooleanOption extends AbstractOption implements OptionInterface
{
    public function getValue($default = null)
    {
        return (bool) parent::getValue(\false);
    }
    protected function sanitize($value)
    {
        return rest_sanitize_boolean($value);
    }
    protected function validate($value)
    {
        if (!\is_bool($value)) {
            throw new \InvalidArgumentException();
        }
        return $value;
    }
}
