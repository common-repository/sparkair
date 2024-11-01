<?php

namespace Sparkair\MathPHP\LinearAlgebra\Decomposition;

use Sparkair\MathPHP\LinearAlgebra\NumericMatrix;
abstract class Decomposition
{
    /**
     * @param NumericMatrix $M
     * @return static
     */
    public static abstract function decompose(NumericMatrix $M);
}
