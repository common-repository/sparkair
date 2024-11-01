<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Utils;

class DateFunctions
{
    public static function nowIso()
    {
        return (new \DateTime('now'))->format('c');
    }
}
