<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Activation;

use Sparkair\SparkPlugins\SparkWoo\Common\Activation\ActivationPageGettingStartedItem;
use Sparkair\SparkPlugins\SparkWoo\Common\Options\BooleanOption;
class ConsentActivationPageGettingStartedItem extends ActivationPageGettingStartedItem
{
    public function __construct($payoff, $title, $description, BooleanOption $sparkAirDataConsentOption, $image = null, $button = null)
    {
        parent::__construct($payoff, $title, $description, $image, $button);
        $this->hidden = $sparkAirDataConsentOption->getValue(\false);
    }
}
