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
 * @Time      14:23
 */

namespace common\panda;
// record each request basic info for debug.
class RecordRequest extends Record
{
    public static $request_method; // 请求方式
    public static $request_url; // 请求完整地址
    public static $request_params; // 请求参数
    public static $request_time; // 请求时间
    public static function read($hFile,$offset){

    }
    public static function write($hFile){

    }
}