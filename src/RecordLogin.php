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
// record web backend basic login information for debug.
class RecordLogin extends Record
{
    public static $user_name = ''; // 用户名
    public static $user_pwd = '';  // 用户密码
    public static $company_id = 0; // 公司ID
    public static $company_name = ''; // 公司名称
    public static function write($hFile){

    }
    public static function read($hFile,$offset){

    }
}