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
 * @Date      2017/11/24
 * @Time      18:00
 *
 * @desc Note: This log class is aim to help PHP developers to find bugs easily.
 * For the version 0.11, it includes following abilities.
 * 01. format dumping SQL sentences.
 * 02. before Panda log called the first time, it will record necessary information about login user
 *     and its company,so as request parameters.
 * 03. it can also format object and string rely on front-end CodeMore plugin.
 * 04. The panda log will split every request in a block and make it user-friendly for reading.
 */

namespace ofix\PandaLog;


class Panda extends Log
{
    public static function log($name,$content){
        self::instance()->log_2($name,$content);
    }
    public static function flush($flushFilePermission=false){
        self::instance()->log_flush($flushFilePermission);
    }
}