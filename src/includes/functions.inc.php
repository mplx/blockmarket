<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

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
