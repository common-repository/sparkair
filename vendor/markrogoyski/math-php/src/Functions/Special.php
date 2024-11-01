<?php

namespace Sparkair\MathPHP\Functions;

use Sparkair\MathPHP\Probability\Combinatorics;
use Sparkair\MathPHP\Functions\Map\Single;
use Sparkair\MathPHP\Exception;
class Special
{
    /**
     * Sign function (signum function) - sgn
     * Extracts the sign of a real number.
     * https://en.wikipedia.org/wiki/Sign_function
     *
     *          { -1 if x < 0
     * sgn(x) = {  0 if x = 0
     *          {  1 if x > 0
     *
     * @param float $x
     *
     * @return int
     */
    public static function signum(float $x) : int
    {
        return $x <=> 0;
    }
    /**
     * Sign function (signum function) - sgn
     * Convenience wrapper for signum function.
     *
     * @param float $x
     *
     * @return int
     */
    public static function sgn(float $x) : int
    {
        return self::signum($x);
    }
    /**
     * Gamma function convenience method
     *
     * @param float $n
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function Γ(float $n) : float
    {
        return self::gamma($n);
    }
    /**
     * Gamma function - Lanczos' approximation
     * https://en.wikipedia.org/wiki/Gamma_function
     * https://en.wikipedia.org/wiki/Lanczos_approximation
     *
     * For postive integers:
     *  Γ(n) = (n - 1)!
     *
     * If z is < 0.5, use reflection formula:
     *
     *                   π
     *  Γ(1 - z)Γ(z) = ------
     *                 sin πz
     *
     *  therefore:
     *
     *                π
     *  Γ(z) = -----------------
     *         sin πz * Γ(1 - z)
     *
     * otherwise:
     *              __  /        1 \ z+½
     *  Γ(z + 1) = √2π | z + g + -  |    e^-(z+g+½) A(z)
     *                  \        2 /
     *
     *  use pre-computed p coefficients: g = 7, n = 9
     *
     * @param float $z
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function gammaLanczos(float $z) : float
    {
        // Basic integer/factorial cases
        if ($z == 0) {
            return \INF;
        }
        // Negative integer, or negative int as a float
        if (\abs($z - \round($z)) < 1.0E-5 && $z < 0) {
            return -\INF;
        }
        // Positive integer, or positive int as a float (Ex: from beta(0.1, 0.9) since it will call Γ(x + y))
        if (\abs($z - \round($z)) < 1.0E-5 && $z > 0) {
            return Combinatorics::factorial((int) \round($z) - 1);
        }
        // p coefficients: g = 7, n = 9
        static $p = [0.9999999999998099, 676.5203681218851, -1259.1392167224028, 771.3234287776531, -176.6150291621406, 12.507343278686905, -0.13857109526572012, 9.984369578019572E-6, 1.5056327351493116E-7];
        static $g = 7;
        static $π = \M_PI;
        /**
         * Use reflection formula when z < 0.5
         *                π
         *  Γ(z) = -----------------
         *         sin πz * Γ(1 - z)
         */
        if ($z < 0.5) {
            $Γ⟮1 − z⟯ = self::gammaLanczos(1 - $z);
            return $π / (\sin($π * $z) * $Γ⟮1 − z⟯);
        }
        // Standard Lanczos formula when z ≥ 0.5
        // Compute A(z)
        $z--;
        $A⟮z⟯ = $p[0];
        for ($i = 1; $i < \count($p); $i++) {
            $A⟮z⟯ += $p[$i] / ($z + $i);
        }
        // Compute parts of equation
        $√2π = \sqrt(2 * $π);
        $⟮z ＋ g ＋½⟯ᶻ⁺½ = \pow($z + $g + 0.5, $z + 0.5);
        $ℯ＾−⟮z ＋ g ＋½⟯ = \exp(-($z + $g + 0.5));
        /**
         * Put it all together:
         *   __  /        1 \ z+½
         *  √2π | z + g + -  |    e^-(z+g+½) A(z)
         *       \        2 /
         */
        return $√2π * $⟮z ＋ g ＋½⟯ᶻ⁺½ * $ℯ＾−⟮z ＋ g ＋½⟯ * $A⟮z⟯;
    }
    /**
     * Gamma function - Stirling approximation
     * https://en.wikipedia.org/wiki/Gamma_function
     * https://en.wikipedia.org/wiki/Stirling%27s_approximation
     * https://www.wolframalpha.com/input/?i=Gamma(n)&lk=3
     *
     * For postive integers:
     *  Γ(n) = (n - 1)!
     *
     * For positive real numbers -- approximation:
     *                   ___
     *         __       / 1  /         1      \ n
     *  Γ(n)≈ √2π ℯ⁻ⁿ  /  - | n + ----------- |
     *                √   n  \    12n - 1/10n /
     *
     * @param float $n
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function gammaStirling(float $n) : float
    {
        // Basic integer/factorial cases
        if ($n == 0) {
            return \INF;
        }
        // Negative integer, or negative int as a float
        if (\abs($n - \round($n)) < 1.0E-5 && $n < 0) {
            return -\INF;
        }
        // Positive integer, or postive int as a float
        if (\abs($n - \round($n)) < 1.0E-5 && $n > 0) {
            return Combinatorics::factorial((int) \round($n) - 1);
        }
        // Compute parts of equation
        $√2π = \sqrt(2 * \M_PI);
        $ℯ⁻ⁿ = \exp(-$n);
        $√1／n = \sqrt(1 / $n);
        $⟮n ＋ 1／⟮12n − 1／10n⟯⟯ⁿ = \pow($n + 1 / (12 * $n - 1 / (10 * $n)), $n);
        /**
         * Put it all together:
         *                   ___
         *         __       / 1  /         1      \ n
         *  Γ(n)≈ √2π ℯ⁻ⁿ  /  - | n + ----------- |
         *                √   n  \    12n - 1/10n /
         */
        return $√2π * $ℯ⁻ⁿ * $√1／n * $⟮n ＋ 1／⟮12n − 1／10n⟯⟯ⁿ;
    }
    /**
     * The log of the error term in the Stirling-De Moivre factorial series
     *
     * log(n!) = .5*log(2πn) + n*log(n) - n + δ(n)
     * Where δ(n) is the log of the error.
     *
     * For n ≤ 15, integers or half-integers, uses stored values.
     *
     * For n = 0: infinity
     * For n < 0: NAN
     *
     * The implementation is heavily inspired by the R language's C implementation of stirlerr.
     * It can be considered a reimplementation in PHP.
     * R Project for Statistical Computing: https://www.r-project.org/
     * R Source: https://svn.r-project.org/R/
     *
     * @param float $n
     *
     * @return float log of the error
     *
     * @throws Exception\NanException
     */
    public static function stirlingError(float $n) : float
    {
        if ($n < 0) {
            throw new Exception\NanException("stirlingError NAN for n < 0: given {$n}");
        }
        static $S0 = 0.08333333333333333;
        // 1/12
        static $S1 = 0.002777777777777778;
        // 1/360
        static $S2 = 0.0007936507936507937;
        // 1/1260
        static $S3 = 0.0005952380952380953;
        // 1/1680
        static $S4 = 0.0008417508417508417;
        // 1/1188
        static $sferr_halves = [
            \INF,
            // 0
            0.15342640972002736,
            // 0.5
            0.08106146679532726,
            // 1.0
            0.05481412105191765,
            // 1.5
            0.0413406959554093,
            // 2.0
            0.03316287351993629,
            // 2.5
            0.02767792568499834,
            // 3.0
            0.023746163656297496,
            // 3.5
            0.020790672103765093,
            // 4.0
            0.018488450532673187,
            // 4.5
            0.016644691189821193,
            // 5.0
            0.015134973221917378,
            // 5.5
            0.013876128823070748,
            // 6.0
            0.012810465242920227,
            // 6.5
            0.01189670994589177,
            // 7.0
            0.011104559758206917,
            // 7.5
            0.010411265261972096,
            // 8.0
            0.009799416126158804,
            // 8.5
            0.009255462182712733,
            // 9.0
            0.008768700134139386,
            // 9.5
            0.008330563433362871,
            // 10.0
            0.00793411456431402,
            // 10.5
            0.007573675487951841,
            // 11.0
            0.007244554301320383,
            // 11.5
            0.00694284010720953,
            // 12.0
            0.006665247032707682,
            // 12.5
            0.006408994188004207,
            // 13.0
            0.006171712263039458,
            // 13.5
            0.0059513701127588475,
            // 14.0
            0.0057462165130101155,
            // 14.5
            0.005554733551962801,
        ];
        if ($n <= 15.0) {
            $nn = $n + $n;
            if ($nn == (int) $nn) {
                return $sferr_halves[$nn];
            }
            $M_LN_SQRT_2PI = \log(\sqrt(2 * \M_PI));
            return self::logGamma($n + 1) - ($n + 0.5) * \log($n) + $n - $M_LN_SQRT_2PI;
        }
        $n² = $n * $n;
        if ($n > 500) {
            return ($S0 - $S1 / $n²) / $n;
        }
        if ($n > 80) {
            return ($S0 - ($S1 - $S2 / $n²) / $n²) / $n;
        }
        if ($n > 35) {
            return ($S0 - ($S1 - ($S2 - $S3 / $n²) / $n²) / $n²) / $n;
        }
        // 15 < n ≤ 35
        return ($S0 - ($S1 - ($S2 - ($S3 - $S4 / $n²) / $n²) / $n²) / $n²) / $n;
    }
    /**
     * Gamma function
     * https://en.wikipedia.org/wiki/Gamma_function
     * https://en.wikipedia.org/wiki/Particular_values_of_the_Gamma_function
     *
     * For positive integers:
     *  Γ(n) = (n - 1)!
     *
     * For half integers:
     *
     *             _   (2n)!
     * Γ(½ + n) = √π  -------
     *                 4ⁿ n!
     *
     * For real numbers: use Lanczos approximation
     *
     * Implementation notes:
     * The original MathPHP implementation was based on the textbook mathematical formula, but this led to numerical
     * issues with very large numbers. Last version with this implementation was v2.4.0.
     *
     * The current implementation is heavily inspired by the R language's C implementation of gammafn, which itself is
     * a translation of a Fortran subroutine by W. Fullerton of Los Alamos Scientific Laboratory.
     * It can be considered a reimplementation in PHP.
     * R Project for Statistical Computing: https://www.r-project.org/
     * R Source: https://svn.r-project.org/R/
     *
     * @param float $x
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     * @throws Exception\NanException
     */
    public static function gamma(float $x)
    {
        static $gamcs = [+0.008571195590989331, +0.004415381324841007, +0.05685043681599363, -0.00421983539641856, +0.0013268081812124603, -0.00018930245297988805, +3.606925327441245E-5, -6.056761904460864E-6, +1.0558295463022833E-6, -1.811967365542384E-7, +3.117724964715322E-8, -5.354219639019687E-9, +9.193275519859589E-10, -1.5779412802883398E-10, +2.7079806229349544E-11, -4.64681865382573E-12, +7.97335019200742E-13, -1.368078209830916E-13, +2.3473194865638007E-14, -4.027432614949067E-15, +6.910051747372101E-16, -1.185584500221993E-16, +2.034148542496374E-17, -3.490054341717406E-18, +5.987993856485306E-19, -1.027378057872228E-19, +1.7627028160605298E-20, -3.024320653735306E-21, +5.188914660218398E-22, -8.902770842456576E-23, +1.5274740684933426E-23, -2.620731256187363E-24, +4.496464047830539E-25, -7.714712731336878E-26, +1.323635453126044E-26, -2.2709994129429287E-27, +3.8964189980039913E-28, -6.685198115125953E-29, +1.1469986631400244E-29, -1.9679385863451348E-30, +3.376448816585338E-31, -5.793070335782136E-32];
        static $ngam = 22;
        static $xmin = -170.5674972726612;
        static $xmax = 171.61447887182297;
        static $xsml = 2.2474362225598545E-308;
        static $dxrel = 1.4901161193847656E-8;
        if (\is_nan($x)) {
            throw new Exception\NanException("gamma cannot compute x when NAN");
        }
        // Undefined (NAN) if x ≤ 0
        if ($x == 0 || $x < 0 && $x == \round($x)) {
            throw new Exception\NanException("gamma undefined for x of {$x}");
        }
        $y = \abs($x);
        // Compute gamma for -10 ≤ x ≤ 10
        if ($y <= 10) {
            // First reduce the interval and find gamma(1 + y) for 0 ≤ y < 1
            $n = (int) $x;
            if ($x < 0) {
                --$n;
            }
            $y = $x - $n;
            // n = floor(x) ==> y in [0, 1)
            --$n;
            $value = self::chebyshevEval($y * 2 - 1, $gamcs, $ngam) + 0.9375;
            if ($n == 0) {
                return $value;
                // x = 1.dddd = 1+y
            }
            // Compute gamma(x) for -10 ≤ x < 1
            // Exactly 0 or -n was checked above already
            if ($n < 0) {
                // The argument is so close to 0 that the result would overflow.
                if ($y < $xsml) {
                    return \INF;
                }
                $n = -$n;
                for ($i = 0; $i < $n; $i++) {
                    $value /= $x + $i;
                }
                return $value;
            }
            // gamma(x) for 2 ≤ x ≤ 10
            for ($i = 1; $i <= $n; $i++) {
                $value *= $y + $i;
            }
            return $value;
        }
        // gamma(x) for y = |x| > 10.
        // Overflow (INF is the best answer)
        if ($x > $xmax) {
            return \INF;
        }
        // Underflow (0 is the best answer)
        if ($x < $xmin) {
            return 0;
        }
        if ($y <= 50 && $y == (int) $y) {
            $value = Combinatorics::factorial((int) $y - 1);
        } else {
            // Normal case
            $M_LN_SQRT_2PI = (\M_LNPI + \M_LN2) / 2;
            $value = \exp(($y - 0.5) * \log($y) - $y + $M_LN_SQRT_2PI + (2 * $y == (int) 2 * $y ? self::stirlingError($y) : self::logGammaCorr($y)));
        }
        if ($x > 0) {
            return $value;
        }
        // The answer is less than half precision because the argument is too near a negative integer.
        if (\abs(($x - (int) ($x - 0.5)) / $x) < $dxrel) {
            // Just move on.
        }
        $sinpiy = \sin(\M_PI * $y);
        return -\M_PI / ($y * $sinpiy * $value);
    }
    /**
     * Log Gamma
     *
     * The implementation is heavily inspired by the R language's C implementation of lgammafn, which itself is
     * a translation of a Fortran subroutine by W. Fullerton of Los Alamos Scientific Laboratory.
     * It can be considered a reimplementation in PHP.
     * R Project for Statistical Computing: https://www.r-project.org/
     * R Source: https://svn.r-project.org/R/
     *
     * @param float $x
     *
     * @return float|int
     *
     * @throws Exception\NanException
     * @throws Exception\OutOfBoundsException
     */
    public static function logGamma(float $x)
    {
        static $xmax = 2.5327372760800758E+305;
        if (\is_nan($x)) {
            throw new Exception\NanException("Cannot compute logGamma when x is NAN");
        }
        // Negative integer argument
        if ($x <= 0 && $x == (int) $x) {
            return \INF;
            // INF is the best answer
        }
        $y = \abs($x);
        if ($y < 1.0E-306) {
            return -\log($y);
            // Denormalized range
        }
        if ($y <= 10) {
            return \log(\abs(self::gamma($x)));
        }
        // From this point, y = |x| > 10
        if ($y > $xmax) {
            return \INF;
            // INF is the best answer
        }
        // y = x > 10
        if ($x > 0) {
            if ($x > 1.0E+17) {
                return $x * (\log($x) - 1);
            }
            $M_LN_SQRT_2PI = (\M_LNPI + \M_LN2) / 2;
            if ($x > 4934720) {
                return $M_LN_SQRT_2PI + ($x - 0.5) * \log($x) - $x;
            }
            return $M_LN_SQRT_2PI + ($x - 0.5) * \log($x) - $x + self::logGammaCorr($x);
        }
        $M_LN_SQRT_PId2 = 0.22579135264472744;
        // log(sqrt(pi/2))
        $sinpiy = \abs(\sin(\M_PI * $y));
        return $M_LN_SQRT_PId2 + ($x - 0.5) * \log($y) - $x - \log($sinpiy) - self::logGammaCorr($y);
    }
    /**
     * Beta function convenience method
     *
     * @param  float $x
     * @param  float $y
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function β(float $x, float $y) : float
    {
        return self::beta($x, $y);
    }
    /**
     * Beta function
     *
     * https://en.wikipedia.org/wiki/Beta_function
     *
     * Selects the best beta algorithm for the provided values
     *
     * @param  float $a
     * @param  float $b
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     * @throws Exception\NanException
     */
    public static function beta(float $a, float $b) : float
    {
        static $xmax = 171.61447887182297;
        if (\is_nan($a) || \is_nan($b)) {
            throw new Exception\NanException("Cannot compute beta when a or b is NAN: got a:{$a}, b:{$b}");
        }
        if ($a < 0 || $b < 0) {
            throw new Exception\OutOfBoundsException("a and b must be non-negative for beta: got a:{$a}, b:{$b}");
        }
        if ($a == 0 || $b == 0) {
            return \INF;
        }
        if (\is_infinite($a) || \is_infinite($b)) {
            return 0;
        }
        if ($a + $b < $xmax) {
            return self::betaBasic($a, $b);
        }
        $val = self::logBeta($a, $b);
        return \exp($val);
    }
    /**
     * Beta function
     *
     * https://en.wikipedia.org/wiki/Beta_function
     *
     *           Γ(x)Γ(y)
     * β(x, y) = --------
     *           Γ(x + y)
     *
     * @param  float $x
     * @param  float $y
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    private static function betaBasic(float $x, float $y) : float
    {
        $Γ⟮x⟯ = self::gamma($x);
        $Γ⟮y⟯ = self::gamma($y);
        $Γ⟮x ＋ y⟯ = self::gamma($x + $y);
        return 1 / $Γ⟮x ＋ y⟯ * $Γ⟮x⟯ * $Γ⟮y⟯;
    }
    /**
     * The log of the beta function
     *
     * The implementation is heavily inspired by the R language's C implementation of lbeta, which itself is
     * a translation of a Fortran subroutine by W. Fullerton of Los Alamos Scientific Laboratory.
     * It can be considered a reimplementation in PHP.
     * R Project for Statistical Computing: https://www.r-project.org/
     * R Source: https://svn.r-project.org/R/
     *
     * @param  float $a
     * @param  float $b
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     * @throws Exception\NanException
     */
    public static function logBeta(float $a, float $b) : float
    {
        if (\is_nan($a) || \is_nan($b)) {
            throw new Exception\NanException("Cannot compute logBeta if a or b is NAN: got a:{$a}, b:{$b}");
        }
        $p = $a;
        $q = $a;
        if ($b < $p) {
            $p = $b;
            // min(a,b)
        }
        if ($b > $q) {
            $q = $b;
            // max(a,b)
        }
        // Both arguments must be >= 0
        if ($p < 0) {
            throw new Exception\OutOfBoundsException("p must be non-negative at this point of logBeta calculation: got {$p}");
        }
        if ($p == 0) {
            return \INF;
        }
        if (\is_infinite($q)) {
            return -\INF;
        }
        if ($p >= 10) {
            // p and q are big.
            $corr = self::logGammaCorr($p) + self::logGammaCorr($q) - self::logGammaCorr($p + $q);
            $M_LN_SQRT_2PI = (\M_LNPI + \M_LN2) / 2;
            return \log($q) * -0.5 + $M_LN_SQRT_2PI + $corr + ($p - 0.5) * \log($p / ($p + $q)) + $q * \log1p(-$p / ($p + $q));
        }
        if ($q >= 10) {
            // p is small, but q is big.
            $corr = self::logGammaCorr($q) - self::logGammaCorr($p + $q);
            return self::logGamma($p) + $corr + $p - $p * \log($p + $q) + ($q - 0.5) * \log1p(-$p / ($p + $q));
        }
        // p and q are small: p <= q < 10. */
        if ($p < 1.0E-306) {
            return self::logGamma($p) + (self::logGamma($q) - self::logGamma($p + $q));
        }
        return \log(self::beta($p, $q));
    }
    /**
     * Log gamma correction
     *
     * Compute the log gamma correction factor for x >= 10 so that
     * log(gamma(x)) = .5*log(2*pi) + (x-.5)*log(x) -x + lgammacor(x)
     *
     * The implementation is heavily inspired by the R language's C implementation of lgammacor, which itself is
     * a translation of a Fortran subroutine by W. Fullerton of Los Alamos Scientific Laboratory.
     * It can be considered a reimplementation in PHP.
     * R Project for Statistical Computing: https://www.r-project.org/
     * R Source: https://svn.r-project.org/R/
     *
     * @param float $x
     *
     * @return float
     */
    public static function logGammaCorr(float $x) : float
    {
        static $algmcs = [+0.16663894804518634, -1.384948176067564E-5, +9.81082564692473E-9, -1.809129475572494E-11, +6.221098041892606E-14, -3.399615005417722E-16, +2.683181998482699E-18, -2.868042435334643E-20, +3.9628370610464347E-22, -6.831888753985767E-24, +1.4292273559424982E-25, -3.5475981581010704E-27, +1.025680058010471E-28, -3.401102254316749E-30, +1.276642195630063E-31];
        /**
         * For IEEE double precision DBL_EPSILON = 2^-52 = 2.220446049250313e-16 :
         * xbig = 2 ^ 26.5
         * xmax = DBL_MAX / 48 =  2^1020 / 3
         */
        static $nalgm = 5;
        static $xbig = 94906265.62425156;
        static $xmax = 3.745194030963158E+306;
        if ($x < 10) {
            throw new Exception\OutOfBoundsException("x cannot be < 10: got {$x}");
        }
        if ($x >= $xmax) {
            // allow to underflow below
        } elseif ($x < $xbig) {
            $tmp = 10 / $x;
            return self::chebyshevEval($tmp * $tmp * 2 - 1, $algmcs, $nalgm) / $x;
        }
        return 1 / ($x * 12);
    }
    /**
     * Evaluate a Chebyshev Series with the Clenshaw Algorithm
     * https://en.wikipedia.org/wiki/Clenshaw_algorithm#Special_case_for_Chebyshev_series
     *
     * The implementation is inspired by the R language's C implementation of chebyshev_eval, which itself is
     * a translation of a Fortran subroutine by W. Fullerton of Los Alamos Scientific Laboratory.
     * It can be considered a reimplementation in PHP.
     *
     * @param float   $x
     * @param float[] $a
     * @param int     $n
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    private static function chebyshevEval(float $x, array $a, int $n) : float
    {
        if ($n < 1 || $n > 1000) {
            throw new Exception\OutOfBoundsException("n cannot be < 1 or > 1000: got {$n}");
        }
        if ($x < -1.1 || $x > 1.1) {
            throw new Exception\OutOfBoundsException("x cannot be < -1.1 or greater than 1.1: got {$x}");
        }
        $２x = $x * 2;
        [$b0, $b1, $b2] = [0, 0, 0];
        for ($i = 1; $i <= $n; $i++) {
            $b2 = $b1;
            $b1 = $b0;
            $b0 = $２x * $b1 - $b2 + $a[$n - $i];
        }
        return ($b0 - $b2) * 0.5;
    }
    /**
     * Multivariate Beta function
     * https://en.wikipedia.org/wiki/Beta_function#Multivariate_beta_function
     *
     *                     Γ(α₁)Γ(α₂) ⋯ Γ(αn)
     * B(α₁, α₂, ... αn) = ------------------
     *                      Γ(α₁ + α₂ ⋯ αn)
     *
     * @param float[] $αs
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function multivariateBeta(array $αs) : float
    {
        foreach ($αs as $α) {
            if ($α == 0) {
                return \INF;
            }
        }
        static $xmax = 171.61447887182297;
        $∑α = \array_sum($αs);
        if ($∑α == \INF) {
            return 0;
        }
        if ($∑α < $xmax) {
            // ~= 171.61 for IEEE
            $Γ⟮∑α⟯ = self::Γ($∑α);
            $∏ = 1 / $Γ⟮∑α⟯;
            foreach ($αs as $α) {
                $∏ *= self::Γ($α);
            }
            return $∏;
        }
        $∑ = -self::logGamma($∑α);
        foreach ($αs as $α) {
            $∑ += self::logGamma($α);
        }
        return \exp($∑);
    }
    /**
     * Logistic function (logistic sigmoid function)
     * A logistic function or logistic curve is a common "S" shape (sigmoid curve).
     * https://en.wikipedia.org/wiki/Logistic_function
     *
     *             L
     * f(x) = -----------
     *        1 + ℯ⁻ᵏ⁽ˣ⁻ˣ⁰⁾
     *
     *
     * @param float $x₀ x-value of the sigmoid's midpoint
     * @param float $L  the curve's maximum value
     * @param float $k  the steepness of the curve
     * @param float $x
     *
     * @return float
     */
    public static function logistic(float $x₀, float $L, float $k, float $x) : float
    {
        $ℯ⁻ᵏ⁽ˣ⁻ˣ⁰⁾ = \exp(-$k * ($x - $x₀));
        return $L / (1 + $ℯ⁻ᵏ⁽ˣ⁻ˣ⁰⁾);
    }
    /**
     * Sigmoid function
     * A sigmoid function is a mathematical function having an "S" shaped curve (sigmoid curve).
     * Often, sigmoid function refers to the special case of the logistic function
     * https://en.wikipedia.org/wiki/Sigmoid_function
     *
     *           1
     * S(t) = -------
     *        1 + ℯ⁻ᵗ
     *
     * @param  float $t
     *
     * @return float
     */
    public static function sigmoid(float $t) : float
    {
        $ℯ⁻ᵗ = \exp(-$t);
        return 1 / (1 + $ℯ⁻ᵗ);
    }
    /**
     * Error function (Gauss error function)
     * https://en.wikipedia.org/wiki/Error_function
     *
     * This is an approximation of the error function (maximum error: 1.5×10−7)
     *
     * erf(x) ≈ 1 - (a₁t + a₂t² + a₃t³ + a₄t⁴ + a₅t⁵)ℯ^-x²
     *
     *       1
     * t = ------
     *     1 + px
     *
     * p = 0.3275911
     * a₁ = 0.254829592, a₂ = −0.284496736, a₃ = 1.421413741, a₄ = −1.453152027, a₅ = 1.061405429
     *
     * @param  float $x
     *
     * @return float
     */
    public static function errorFunction(float $x) : float
    {
        if ($x == 0) {
            return 0;
        }
        $p = 0.3275911;
        $t = 1 / (1 + $p * \abs($x));
        $a₁ = 0.254829592;
        $a₂ = -0.284496736;
        $a₃ = 1.421413741;
        $a₄ = -1.453152027;
        $a₅ = 1.061405429;
        $error = 1 - ($a₁ * $t + $a₂ * $t ** 2 + $a₃ * $t ** 3 + $a₄ * $t ** 4 + $a₅ * $t ** 5) * \exp(-\abs($x) ** 2);
        return $x > 0 ? $error : -$error;
    }
    /**
     * Error function (Gauss error function)
     * Convenience method for errorFunction
     *
     * @param  float $x
     *
     * @return float
     */
    public static function erf(float $x) : float
    {
        return self::errorFunction($x);
    }
    /**
     * Complementary error function (erfc)
     * erfc(x) ≡ 1 - erf(x)
     *
     * @param  int|float $x
     *
     * @return float
     */
    public static function complementaryErrorFunction($x) : float
    {
        return 1 - self::erf($x);
    }
    /**
     * Complementary error function (erfc)
     * Convenience method for complementaryErrorFunction
     *
     * @param  float $x
     *
     * @return float
     */
    public static function erfc(float $x) : float
    {
        return 1 - self::erf($x);
    }
    /**
     * Upper Incomplete Gamma Function - Γ(s,x)
     * https://en.wikipedia.org/wiki/Incomplete_gamma_function
     *
     * @param float $s shape parameter > 0
     * @param float $x lower limit of integration
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException if s is ≤ 0
     */
    public static function upperIncompleteGamma(float $s, float $x) : float
    {
        if ($s <= 0) {
            throw new Exception\OutOfBoundsException("S must be > 0. S = {$s}");
        }
        return self::gamma($s) - self::lowerIncompleteGamma($s, $x);
    }
    /**
     * Lower incomplete gamma function - γ(s,t)
     * https://en.wikipedia.org/wiki/Incomplete_gamma_function#Lower_incomplete_Gamma_function
     *
     * This function is exact for all integer multiples of .5
     * using the recurrence relation: γ⟮s+1,x⟯= s*γ⟮s,x⟯-xˢ*e⁻ˣ
     *
     * The function can be evaluated at other points using the series:
     *              zˢ     /      x          x²             x³            \
     * γ(s,x) =  -------- | 1 + ----- + ---------- + --------------- + ... |
     *            s * eˣ   \     s+1    (s+1)(s+2)   (s+1)(s+2)(s+3)      /
     *
     * @param float $s > 0
     * @param float $x ≥ 0
     *
     * @return float
     */
    public static function lowerIncompleteGamma(float $s, float $x) : float
    {
        if ($x == 0) {
            return 0;
        }
        if ($s == 0) {
            return \NAN;
        }
        if ($s == 1) {
            return 1 - \exp(-1 * $x);
        }
        if ($s == 0.5) {
            $√π = \sqrt(\M_PI);
            $√x = \sqrt($x);
            return $√π * self::erf($√x);
        }
        if (\round($s * 2, 0) == $s * 2) {
            return ($s - 1) * self::lowerIncompleteGamma($s - 1, $x) - $x ** ($s - 1) * \exp(-1 * $x);
        }
        $tol = 1.0E-12;
        $xˢ∕s∕eˣ = $x ** $s / \exp($x) / $s;
        $sum = 1;
        $fractions = [];
        $element = 1 + $tol;
        while ($element > $tol) {
            $fractions[] = $x / ++$s;
            $element = \array_product($fractions);
            $sum += $element;
        }
        return $xˢ∕s∕eˣ * $sum;
    }
    /**
     * γ - Convenience method for lower incomplete gamma function
     * https://en.wikipedia.org/wiki/Incomplete_gamma_function#Lower_incomplete_Gamma_function
     *
     * @param float $s > 0
     * @param float $x ≥ 0
     *
     * @return float
     */
    public static function γ(float $s, float $x) : float
    {
        return self::lowerIncompleteGamma($s, $x);
    }
    /**
     * Regularized lower incomplete gamma function - P(s,x)
     * https://en.wikipedia.org/wiki/Incomplete_gamma_function#Regularized_Gamma_functions_and_Poisson_random_variables
     *
     *          γ(s,x)
     * P(s,x) = ------
     *           Γ(s)
     *
     * P(s,x) is the cumulative distribution function for Gamma random variables with shape parameter s and scale parameter 1
     *
     *
     * @param float $s > 0
     * @param float $x ≥ 0
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException
     */
    public static function regularizedLowerIncompleteGamma(float $s, float $x) : float
    {
        $γ⟮s、x⟯ = self::lowerIncompleteGamma($s, $x);
        $Γ⟮s⟯ = self::gamma($s);
        return $γ⟮s、x⟯ / $Γ⟮s⟯;
    }
    /**
     * Incomplete Beta Function - B(x;a,b)
     *
     * Generalized form of the beta function
     * https://en.wikipedia.org/wiki/Beta_function#Incomplete_beta_function
     *
     * @param float $x Upper limit of the integration 0 ≦ x ≦ 1
     * @param float $a Shape parameter a > 0
     * @param float $b Shape parameter b > 0
     *
     * @return float
     *
     * @throws Exception\BadDataException
     * @throws Exception\BadParameterException
     * @throws Exception\OutOfBoundsException
     */
    public static function incompleteBeta(float $x, float $a, float $b) : float
    {
        return self::regularizedIncompleteBeta($x, $a, $b) * self::beta($a, $b);
    }
    /**
     * Regularized incomplete beta function - Iₓ(a, b)
     *
     * https://en.wikipedia.org/wiki/Beta_function#Incomplete_beta_function
     *
     * This function looks at the values of x, a, and b, and determines which algorithm is best to calculate
     * the value of Iₓ(a, b)
     *
     * There are several ways to calculate the incomplete beta function (See: https://dlmf.nist.gov/8.17).
     * This follows the continued fraction form, which consists of a term followed by a converging series of fractions.
     * Lentz's Algorithm is used to solve the continued fraction.
     *
     * The implementation of the continued fraction using Lentz's Algorithm is heavily inspired by Lewis Van Winkle's
     * reference implementation in C: https://github.com/codeplea/incbeta
     *
     * Other implementations used as references in the past:
     *  http://www.boost.org/doc/libs/1_35_0/libs/math/doc/sf_and_dist/html/math_toolkit/special/sf_beta/ibeta_function.html
     *  https://github.com/boostorg/math/blob/develop/include/boost/math/special_functions/beta.hpp
     *
     * @param float $x Upper limit of the integration 0 ≦ x ≦ 1
     * @param float $a Shape parameter a > 0
     * @param float $b Shape parameter b > 0
     *
     * @return float
     *
     * @throws Exception\BadDataException
     * @throws Exception\BadParameterException
     * @throws Exception\FunctionFailedToConvergeException
     * @throws Exception\NanException
     * @throws Exception\OutOfBoundsException
     */
    public static function regularizedIncompleteBeta(float $x, float $a, float $b) : float
    {
        $limits = ['x' => '[0, 1]', 'a' => '(0,∞)', 'b' => '(0,∞)'];
        Support::checkLimits($limits, ['x' => $x, 'a' => $a, 'b' => $b]);
        if ($x == 1 || $x == 0) {
            return $x;
        }
        if ($a == 1) {
            return 1 - (1 - $x) ** $b;
        }
        if ($b == 1) {
            return $x ** $a;
        }
        if ($x > ($a + 1) / ($a + $b + 2)) {
            return 1 - self::regularizedIncompleteBeta(1 - $x, $b, $a);
        }
        // Continued fraction using Lentz's Algorithm.
        $first_term = \exp(\log($x) * $a + \log(1.0 - $x) * $b - (self::logGamma($a) + self::logGamma($b) - self::logGamma($a + $b))) / $a;
        // PHP 7.2.0 offers PHP_FLOAT_EPSILON, but 1.0e-30 is used in Lewis Van Winkle's
        // reference implementation to prevent division-by-zero errors, so we use the same here.
        $ε = 1.0E-30;
        // These starting values are changed from the reference implementation to precalculate $i = 0 and avoid the
        // extra conditional expression inside the loop.
        $d = 1.0;
        $c = 2.0;
        $f = $c * $d;
        $m = 0;
        for ($i = 1; $i <= 200; $i++) {
            if ($i % 2 === 0) {
                // Even term
                $m++;
                $numerator = $m * ($b - $m) * $x / (($a + 2.0 * $m - 1.0) * ($a + 2.0 * $m));
            } else {
                // Odd term
                $numerator = -(($a + $m) * ($a + $b + $m) * $x) / (($a + 2.0 * $m) * ($a + 2.0 * $m + 1));
            }
            // Lentz's Algorithm
            $d = 1.0 + $numerator * $d;
            $d = 1.0 / (\abs($d) < $ε ? $ε : $d);
            $c = 1.0 + $numerator / (\abs($c) < $ε ? $ε : $c);
            $f *= $c * $d;
            if (\abs(1.0 - $c * $d) < 1.0E-8) {
                return $first_term * ($f - 1.0);
            }
        }
        // Series did not converge.
        throw new Exception\FunctionFailedToConvergeException(\sprintf('Continuous fraction series is not converging for x = %f, a = %f, b = %f', $x, $a, $b));
    }
    /**
     * Generalized Hypergeometric Function
     *
     * https://en.wikipedia.org/wiki/Generalized_hypergeometric_function
     *
     *                                       ∞
     *                                      ____
     *                                      \     ∏ap⁽ⁿ⁾ * zⁿ
     * pFq(a₁,a₂,...ap;b₁,b₂,...,bq;z)=      >    ------------
     *                                      /      ∏bq⁽ⁿ⁾ * n!
     *                                      ‾‾‾‾
     *                                       n=0
     *
     * Where a⁽ⁿ⁾ is the Pochhammer Function or Rising Factorial
     *
     * We are evaluating this as a series:
     *
     *               (a + n - 1) * z
     * ∏n = ∏n₋₁  * -----------------
     *               (b + n - 1) * n
     *
     *                  n   (a + n - 1) * z
     *   ₁F₁ = ₁F₁n₋₁ + ∏  -----------------  = ₁F₁n₋₁ + ∏n
     *                  1   (b + n - 1) * n
     *
     * @param int    $p         the number of parameters in the numerator
     * @param int    $q         the number of parameters in the denominator
     * @param float  ...$params a collection of the a, b, and z parameters
     *
     * @return float
     *
     * @throws Exception\BadParameterException if the number of parameters is incorrect
     */
    public static function generalizedHypergeometric(int $p, int $q, float ...$params) : float
    {
        $n = \count($params);
        if ($n !== $p + $q + 1) {
            $expected_num_params = $p + $q + 1;
            throw new Exception\BadParameterException("Number of parameters is incorrect. Expected {$expected_num_params}; got {$n}");
        }
        $a = \array_slice($params, 0, $p);
        $b = \array_slice($params, $p, $q);
        $z = $params[$n - 1];
        $tol = 1.0E-8;
        $n = 1;
        $sum = 0;
        $product = 1;
        do {
            $sum += $product;
            $a_sum = \array_product(Single::add($a, $n - 1));
            $b_sum = \array_product(Single::add($b, $n - 1));
            $product *= $a_sum * $z / $b_sum / $n;
            $n++;
        } while ($product / $sum > $tol);
        return $sum;
    }
    /**
     * Confluent Hypergeometric Function
     *
     * https://en.wikipedia.org/wiki/Confluent_hypergeometric_function
     *         ∞
     *        ____
     *        \     a⁽ⁿ⁾ * zⁿ
     *  ₁F₁ =  >    ---------
     *        /     b⁽ⁿ⁾ * n!
     *        ‾‾‾‾
     *        n=0
     *
     * @param float $a the numerator value
     * @param float $b the denominator value
     * @param float $z
     *
     * @return float
     *
     * @throws Exception\BadParameterException
     */
    public static function confluentHypergeometric(float $a, float $b, float $z) : float
    {
        return self::generalizedHypergeometric(1, 1, $a, $b, $z);
    }
    /**
     * Hypergeometric Function
     *
     * https://en.wikipedia.org/wiki/Hypergeometric_function
     *         ∞
     *        ____
     *        \     a⁽ⁿ⁾ * b⁽ⁿ⁾ * zⁿ
     *  ₂F₁ =  >    ----------------
     *        /         c⁽ⁿ⁾ * n!
     *        ‾‾‾‾
     *        n=0
     *
     * @param float $a the first numerator value
     * @param float $b the second numerator value
     * @param float $c the denominator value
     * @param float $z |z| < 1
     *
     * @return float
     *
     * @throws Exception\OutOfBoundsException if |z| >= 1
     * @throws Exception\BadParameterException
     */
    public static function hypergeometric(float $a, float $b, float $c, float $z) : float
    {
        if (\abs($z) >= 1) {
            throw new Exception\OutOfBoundsException('|z| must be < 1. |z| = ' . \abs($z));
        }
        return self::generalizedHypergeometric(2, 1, $a, $b, $c, $z);
    }
    /**
     * Softmax (normalized exponential)
     * A generalization of the logistic function that "squashes" a K-dimensional
     * vector z of arbitrary real values to a K-dimensional vector σ(z) of real values
     * in the range (0, 1) that add up to 1.
     * https://en.wikipedia.org/wiki/Softmax_function
     *
     *           ℯᶻⱼ
     * σ(𝐳)ⱼ = ------  for j = 1 to K
     *          ᴷ
     *          ∑ ℯᶻᵢ
     *         ⁱ⁼¹
     *
     * @param  float[] $𝐳
     *
     * @return float[]
     */
    public static function softmax(array $𝐳) : array
    {
        $ℯ = \M_E;
        $∑ᴷℯᶻᵢ = \array_sum(\array_map(function ($z) use($ℯ) {
            return $ℯ ** $z;
        }, $𝐳));
        $σ⟮𝐳⟯ⱼ = \array_map(function ($z) use($ℯ, $∑ᴷℯᶻᵢ) {
            return $ℯ ** $z / $∑ᴷℯᶻᵢ;
        }, $𝐳);
        return $σ⟮𝐳⟯ⱼ;
    }
}
