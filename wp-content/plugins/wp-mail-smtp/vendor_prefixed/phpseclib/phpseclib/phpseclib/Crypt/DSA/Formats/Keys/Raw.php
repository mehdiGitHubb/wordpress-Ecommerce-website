<?php

/**
 * Raw DSA Key Handler
 *
 * PHP version 5
 *
 * Reads and creates arrays as DSA keys
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */
namespace WPMailSMTP\Vendor\phpseclib3\Crypt\DSA\Formats\Keys;

use WPMailSMTP\Vendor\phpseclib3\Math\BigInteger;
/**
 * Raw DSA Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class Raw
{
    /**
     * Break a public or private key down into its constituent components
     *
     * @param array $key
     * @param string $password optional
     * @return array
     */
    public static function load($key, $password = '')
    {
        if (!\is_array($key)) {
            throw new \UnexpectedValueException('Key should be a array - not a ' . \gettype($key));
        }
        switch (\true) {
            case !isset($key['p']) || !isset($key['q']) || !isset($key['g']):
            case !$key['p'] instanceof \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger:
            case !$key['q'] instanceof \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger:
            case !$key['g'] instanceof \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger:
            case !isset($key['x']) && !isset($key['y']):
            case isset($key['x']) && !$key['x'] instanceof \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger:
            case isset($key['y']) && !$key['y'] instanceof \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger:
                throw new \UnexpectedValueException('Key appears to be malformed');
        }
        $options = ['p' => 1, 'q' => 1, 'g' => 1, 'x' => 1, 'y' => 1];
        return \array_intersect_key($key, $options);
    }
    /**
     * Convert a private key to the appropriate format.
     *
     * @param \phpseclib3\Math\BigInteger $p
     * @param \phpseclib3\Math\BigInteger $q
     * @param \phpseclib3\Math\BigInteger $g
     * @param \phpseclib3\Math\BigInteger $y
     * @param \phpseclib3\Math\BigInteger $x
     * @param string $password optional
     * @return string
     */
    public static function savePrivateKey(\WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $p, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $q, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $g, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $y, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $x, $password = '')
    {
        return \compact('p', 'q', 'g', 'y', 'x');
    }
    /**
     * Convert a public key to the appropriate format
     *
     * @param \phpseclib3\Math\BigInteger $p
     * @param \phpseclib3\Math\BigInteger $q
     * @param \phpseclib3\Math\BigInteger $g
     * @param \phpseclib3\Math\BigInteger $y
     * @return string
     */
    public static function savePublicKey(\WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $p, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $q, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $g, \WPMailSMTP\Vendor\phpseclib3\Math\BigInteger $y)
    {
        return \compact('p', 'q', 'g', 'y');
    }
}
