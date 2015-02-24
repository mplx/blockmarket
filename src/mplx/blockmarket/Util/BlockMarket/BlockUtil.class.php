<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Util\BlockMarket;

/**
* Blockmarket utilities
*/
class BlockUtil
{
    /**
    * Convert amount to coins (array)
    *
    * @param mixed $sum
    * @return array
    */
    public static function toCoinsArray($sum)
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

    /**
    * Convert amount to coins (string)
    *
    * @param mixed $sum
    * @param string $format
    * @return string
    */
    public static function toCoinsString($sum, $format = 'long')
    {
        $txt = self::toCoinsArray(abs($sum));
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
}
