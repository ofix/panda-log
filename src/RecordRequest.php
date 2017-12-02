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
// record each url request basic info for debug.
class RecordRequest extends Record
{
    public $request_method; // 请求方式
    public $request_url;    // 请求完整地址
    public $request_params; // 请求参数
    public $request_time;   // 请求时间
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_REQUEST;
    }
    public function log(){

    }
    /* 数据格式
     * request_method|request_url|request_params|request_time|
     */
    public function write(BinaryStream $stream){
        $o = new \stdClass();
        $o->request_method = $this->request_method;
        $o->request_url = $this->request_url;
        $o->request_params = $this->request_params;
        $o->request_time = $this->request_time;
    }
    public function read(BinaryStream $stream,$raw_bytes){
        $data = $stream->readStringClean($raw_bytes);
        list($this->login_name, $this->login_pwd,
            $this->user_id, $this->company_id,
            $this->staffname, $this->staff_mobile) = explode(self::EOL, $data);
    }
}