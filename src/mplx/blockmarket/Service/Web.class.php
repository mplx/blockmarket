<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Service;

/**
* Web service
*/
class Web
{

    /**
    * Get HTTP cookie
    *
    * @param string $key
    * @param mixed $alt
    * @return mixed
    */
    public static function getCookie($key, $alt = false)
    {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return $alt;
        }
    }

    /**
    * Get HTTP GET request
    *
    * @param string $key
    * @param mixed $alt
    * @return mixed
    */
    public static function getRequestGet($key, $alt = false)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        } else {
            return $alt;
        }
    }

    /**
    * Get HTTP POST request
    *
    * @param string $key
    * @param mixed $alt
    * @return mixed
    */
    public static function getRequestPost($key, $alt = false)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        } else {
            return $alt;
        }
    }

    /**
    * Get URL
    *
    * @param string $url
    * @param bool $header
    * @param bool $body
    * @param int $timeout
    * @param string $useragent
    * @return mixed
    */
    public static function getUrl($url, $header = false, $body = true, $timeout = null, $useragent = null)
    {
        $timeout = $timeout == null ? BM_CURL_TIMEOUT : intval($timeout);
        $useragent = $useragent == null ? BM_CURL_USERAGENT : $useragent;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_NOBODY, (!$body));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
