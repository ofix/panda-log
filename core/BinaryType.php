<?php
/*
 * This file is part of panda-log.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    code lighter
 * @copyright https://github.com/ofix
 * @we-chat   981326632
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @Date      2017/11/29
 * @Time      17:23
 *
 * @desc binary packet is a data storage convention
 */
namespace ofix\PandaLog\core;
class BinaryType{
    const STRING           = 'a';   // NUL-padded string
    const STRING2          = 'A';   // SPACE-padded string
    const HEX_STRING_LOW   = 'h';   // Hex string, low nibble first
    const HEX_STRING_HIGH  = 'H';   // Hex string, high nibble first
    const SIGNED_CHAR      = 'c';   // signed char
    const UNSIGNED_CHAR    = 'C';   // unsigned char
    const SIGNED_SHORT     = 's';   // signed short (always 16 bit, machine byte order)
    const UNSIGNED_SHORT   = 'n';   // unsigned short (always 16 bit, big endian byte order)
    const UNSIGNED_LONG    = 'N';	// unsigned long (always 32 bit, big endian byte order)
    const UNSIGNED_LONG_LONG = 'J'; // unsigned long long (always 64 bit, big endian byte order)
    public static function getTypeByteCount($str){
        switch($str){
            case self::STRING:
            case self::STRING2:
            case self::HEX_STRING_LOW:
            case self::HEX_STRING_HIGH:
                return strlen($str);
            case self::SIGNED_CHAR:
            case self::UNSIGNED_CHAR:
                return 1;
            case self::SIGNED_SHORT:
            case self::UNSIGNED_SHORT:
                return 2;
            case self::UNSIGNED_LONG:
                return 4;
            case self::UNSIGNED_LONG_LONG:
                return 8;
            default:
                return 0;

        }
    }
}
