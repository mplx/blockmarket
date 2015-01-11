<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

function bm_COOKIE($key, $alt = false)
{
    if (isset($_COOKIE[$key])) {
        return $_COOKIE[$key];
    } else {
        return $alt;
    }
}

function bm_GET($key, $alt = false)
{
    if (isset($_GET[$key])) {
        return $_GET[$key];
    } else {
        return $alt;
    }
}

function bm_POST($key, $alt = false)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } else {
        return $alt;
    }
}

function bm_curl_get($url, $header = false, $body = true, $timeout = null, $useragent = null)
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

function toCoins($sum)
{
    $platinum = floor($sum / 100);
    $gold = floor($sum - $platinum * 100);
    $copper = round(($sum - $platinum * 100 - $gold) * 100);
    return array(
        'sum' => $sum,
        'platinum' => $platinum,
        'gold' => $gold,
        'copper' => $copper
    );
}

function toCoinsString($sum, $format = 'long')
{
    $txt = toCoins(abs($sum));
    switch ($format) {
        case 'short':
        default:
            $symbol_platinum = 'p';
            $symbol_gold = 'g';
            $symbol_copper = 'c';
            break;
        case 'long':
            $symbol_platinum = ' Platinum';
            $symbol_gold = ' Gold';
            $symbol_copper = ' Copper';
            break;
        case 'images':
            $symbol_platinum = 'P';
            $symbol_gold = 'G';
            $symbol_copper = 'C';
            break;
    }
    return sprintf(
        $txt['platinum'] . '%s ' . $txt['gold'] . '%s ' . $txt['copper'] . '%s',
        $symbol_platinum,
        $symbol_gold,
        $symbol_copper
    );
}
