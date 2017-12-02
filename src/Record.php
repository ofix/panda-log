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
 * @Date      2017/12/2
 * @Time      13:40
 */

namespace common\panda;
/* all record saved format as follows
 * 4 byte record length|1 byte record type|remain bytes are custom
 * different record type data may need different decode method
 */
abstract class Record
{
    static $record_type;
    public static function read($hFile,$offset){

    }
    public static function decode($raw_bytes){

    }
    public static function write($hFile){

    }
}