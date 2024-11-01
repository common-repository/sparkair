<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Utils;

use Sparkair\MathPHP\LinearAlgebra\Vector;
class PredictionHelperFunctions
{
    public static function getTopNFromArray(array $A, int $N)
    {
        \arsort($A);
        $top = \array_slice($A, 0, $N, \true);
        return $top;
    }
    public static function cosineSimilarity($v, $u)
    {
        $V = new Vector($v);
        $U = new Vector($u);
        return $V->dotProduct($U) / ($V->l2Norm() * $U->l2Norm() + 1.0E-6);
    }
}
