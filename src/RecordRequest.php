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
use Yii;
// record each url request basic info for debug.
class RecordRequest extends Record
{
    public $request_method; // 请求方式
    public $request_url;    // 请求完整地址
    public $request_params; // 请求参数
    public $request_time;   // 请求时间
    public $user_ip;        // 用户请求IP
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_REQUEST;
    }
    public function log(){
        $this->request_time = date('Y-m-d H:i:s',time());
        $this->request_url = Yii::$app->request->getHostInfo().Yii::$app->request->url;
        $this->request_method = Yii::$app->request->method;
        if(Yii::$app->request->isGet){
            $this->request_params = Yii::$app->request->get;
        }else if(Yii::$app->request->isPost){
            $this->request_params = Yii::$app->request->post;
        }
        $this->user_ip = Yii::$app->request->userIP;
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
        $o->user_ip = $this->user_ip;
        $this->data = $o;
        parent::write($stream);
    }
    public function read(BinaryStream $stream,$byte_count){
        $data = $stream->readStringClean($byte_count);
        $this->data = json_decode($data);
    }
}