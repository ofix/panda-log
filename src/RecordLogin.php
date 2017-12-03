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
use common\models\Company;
use common\models\User;
use common\models\UserCompany;
use Yii;
// record web backend basic login information for debug.
class RecordLogin extends Record
{
    public $login_name;   // 登录用户名
    public $login_pwd;    // 登录用户密码
    public $user_id;      // 用户ID
    public $company_id;   // 公司ID
    public $company_name; // 公司名称
    public $staff_name;   // 用户名
    public $staff_mobile; // 用户手机
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_LOGIN;
    }
    public function log(){
        $this->user_id = Yii::$app->user->getId();
        $this->company_id = Yii::$app->user->getCompanyId();
        $company = Company::findOne(['id'=>$this->company_id]);
        if($company){
            $this->company_name = $company->name;
        }else{
            $this->company_name = self::EMPTY_PLACE_HOLDER;
        }
        $staff = UserCompany::findOne(['company_id'=>$this->company_id,'user_id'=>$this->user_id]);
        if($staff){
            $this->staff_name   = $staff->staff_name;
            $this->staff_mobile = $staff->staff_mobile;
        }else{
            $this->staff_name   = self::EMPTY_PLACE_HOLDER;
            $this->staff_mobile = self::EMPTY_PLACE_HOLDER;
        }
        $user = User::findOne(['id'=>$this->user_id]);
        if($user){
            $this->login_name = $user->mobile;
            $this->login_pwd  = $user->password_hash;
        }else{
            $this->login_name = self::EMPTY_PLACE_HOLDER;
            $this->login_pwd = self::EMPTY_PLACE_HOLDER;
        }
    }
    /* 数据格式
     * login_name|login_pwd|user_id|company_id|staff_name|staff_mobile
     */
    public function write(BinaryStream $stream,$debug_len){
        $data  = $this->login_name.self::EOL;
        $data .= $this->login_pwd.self::EOL;
        $data .= $this->user_id.self::EOL;
        $data .= $this->company_id.self::EOL;
        $data .= $this->staff_name.self::EOL;
        $data .= $this->staff_mobile.self::EOL;
        $this->data = $data;
        parent::write($stream,$debug_len);
    }
    public function read(BinaryStream $stream,$byte_count){
        $data = $stream->readStringClean($byte_count);
        list($this->login_name, $this->login_pwd,
             $this->user_id, $this->company_id,
             $this->staffname, $this->staff_mobile) = explode(self::EOL, $data);
        $o = new \stdClass();
        $o->login_name = $this->login_name;
        $o->login_pwd = $this->login_pwd;
        $o->user_id = $this->user_id;
        $o->company_id = $this->company_id;
        $o->staff_name = $this->staff_name;
        $o->staff_mobile = $this->staff_mobile;
        $this->data = $o;
    }
}