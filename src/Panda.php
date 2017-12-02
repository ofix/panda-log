<?php
/*
 * Author: code lighter
 * Date: 2017/11/24
 * Note: This log class is aim to help PHP developers to find bugs easily.
 * For the version 0.11, it includes following abilities.
 * 01. format dumping SQL sentences.
 * 02. before Panda log called the first time, it will record necessary information about login user
 *     and its company,so as request parameters.
 * 03. it can also format object and string rely on front-end CodeMore plugin.
 * 04. The panda log will split every request in a block and make it user-friendly for reading.
 */

namespace common\panda;
use common\models\Company;
use common\models\User;
use common\models\UserCompany;
use Yii;
use yii\base\Model;
use yii\db\Query;

class Panda
{
    public $request_time;     // 请求开始时间
    public $response_time;    // 请求结束时间
    protected static $_instance = null;
    protected static $_data = null;
    protected static $last_flush_begin = 0; //上次写入文件的开始位置
    protected static $last_flush_end   = 0; //上次写入文件的结束位置
    protected static $last_flush_bytes = 0; //上次写入文件的字节数量
    private $log_file_prefix  = 'panda_log_'; //log日志前缀
    private $default_save_dir = __DIR__ . '/../../company/runtime/panda_log/';
    private $company_id;     // 当前登录的公司ID
    private $user_id;        // 当前登录的用户ID
    private $company_name;   // 登录的公司名称
    private $staff_name;     // 登录的员工名称
    private $staff_mobile;   // 员工手机号
    private $login_user;     // 登录用户名
    private $login_pwd;      // 登录密码
    private $flag_login_user;// 是否记录登录的用户信息
    private $is_rpc;         // 是否是RPC远程调用
    const FLAG_ARRAY  = 1;    // 格式化数组
    const FLAG_SQL    = 2;    // 格式化SQL
    const FLAG_OBJECT = 3;    // 格式化对象
    const FLAG_STRING = 4;    // 格式化字符串
    const META_BYTES_ITEM = 5; //每项数据占用的字节数

    private function __construct()
    {
        $this->flag_login_user = true;
        $this->is_rpc = false;
    }
    public static function instance()
    {
        if (is_null(self::$_instance) || !isset(self::$_instance)) {
            self::$_instance = new self();
            self::$_data = [];
        }
        return self::$_instance;
    }
    public function getFlushBytes(){
        return self::$last_flush_bytes;
    }
    public function getFlushBegin(){
        return self::$last_flush_begin;
    }
    public function getFlushEnd(){
        return self::$last_flush_end;
    }
    /*
     * 请求之前记录必要的用户信息
     */
    public function beforeRequest(){
        if($this->flag_login_user && (!$this->is_rpc)){
            $this->user_id = Yii::$app->user->getId();
            $this->company_id = Yii::$app->user->getCompanyId();
            $company = Company::findOne(['id'=>$this->company_id]);
            if($company){
                $this->company_name = $company->name;
            }
            $staff = UserCompany::findOne(['company_id'=>$this->company_id,'user_id'=>$this->user_id]);
            if($staff){
                $this->staff_name   = $staff->staff_name;
                $this->staff_mobile = $staff->staff_mobile;
            }
            $user = User::findOne(['id'=>$this->user_id]);
            if($user){
                $this->login_user = $user->mobile;
                $this->login_pwd  = $user->password_hash;
            }
        }
        $this->request_time = date('Y-m-d H:i:s',time());
    }

    public function log($content = '')
    {
        if(is_array($content)){
            self::logArray($content);
        }else if(($content instanceof Query) || ($content instanceof Model)){
            self::logSql($content);
        }else if(is_object($content)){
            self::logObject($content);
        }else if(is_string($content)){
            self::logStr($content);
        }
    }

    /*
     * @func 打印数组
     */
    protected static function logArray($content)
    {
        $data = json_encode($content);
        $len = strlen($data)+self::META_BYTES_ITEM;
        self::$_data[] = ['len'=>$len,'data' => json_encode($content), 'type' => self::FLAG_ARRAY];
    }
    protected static function logSql(&$content)
    {
        $data = '';
        if($content instanceof Model){
            $data = Yii::$app->db->createCommand($content)->getRawSql();
        }else if($content instanceof Query){
            $data = $content->createCommand()->getRawSql();
        }
        $len = strlen($data)+ self::META_BYTES_ITEM;
        self::$_data[] = ['len'=>$len,'data' => $data, 'type' => self::FLAG_SQL];
    }
    protected static function logStr($content){
        $len = strlen($content)+self::META_BYTES_ITEM;
        self::$_data[] = ['len'=>$len, 'data' =>$content,'type'=>self::FLAG_STRING];
    }
    protected static function logObject($content)
    {
        $o = json_encode($content);
        $len = strlen($o)+self::META_BYTES_ITEM;
        self::$_data[] = ['len'=>$len, 'data' => json_encode($content), 'type' => self::FLAG_OBJECT];
    }

    public static function ensureDir($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return TRUE;
        }
        if (!self::ensureDir(dirname($dir), $mode)) {
            return FALSE;
        }
        return @mkdir($dir, $mode);
    }

    /*
     * 保存文件日志
     */
    public function flush()
    {
        $this->saveMetaFile(); //保存元数据
        $logFile = $this->getLogFile();
        if (count(self::$_data)) {
            //写入二进制流
            $hFile = BinaryWriter::open($logFile);
            $o = new BinaryStream();
            $o->setEndian(Endian::BIG_ENDIAN);
            foreach (self::$_data as $v) {
                $o->writeUInt32($v['len']); // 4个字节长度
                $o->writeUByte($v['type']); // 1个字节类型
                $o->writeStringClean($v['data']); //剩下的都是数据字节
            }
            $bytes = $o->toBytes();
            BinaryWriter::append($hFile,$bytes);
            BinaryWriter::close($hFile);
        }
    }

    /*
     * 保存元数据
     * 魔幻数|主版本号|小版本号|总请求数|请求1,请求2,请求3,....
     */
    protected function saveMetaFile()
    {
        self::ensureDir($this->default_save_dir);
        $metaFile = self::getMetaFile();
        $lastItemCount = 0;
        if (file_exists($metaFile)) {
            $hFile = BinaryWriter::open($metaFile);
            if ($hFile) {
                $lastItemCount = $this->getMetaItemCount($hFile);
            }
            $szItem = self::getItemLength();
            $posOffset = self::getLastLogFilePos($hFile);
            if ($szItem) {
                $this->saveMetaItemCount($hFile,1+$lastItemCount);
                $this->saveMetaOneItem($hFile,$posOffset+1,$szItem+$posOffset);
            }
            BinaryWriter::close($hFile);
            self::$last_flush_begin = $posOffset+1;
            self::$last_flush_end = $szItem+$posOffset;
            self::$last_flush_bytes = $szItem;
        }else{
            $hFile = BinaryWriter::open($metaFile);
            $szItem = self::getItemLength()-1;
            $this->saveHeader($hFile);
            $this->saveMetaItemCount($hFile,1);
            $this->saveMetaOneItem($hFile,0,$szItem);
            BinaryWriter::close($hFile);
            self::$last_flush_begin = 0;
            self::$last_flush_end = $szItem;
            self::$last_flush_bytes = $szItem+1;
        }
    }

    protected function getLastLogFilePos($hFile){
        $bytes = BinaryReader::getRawBytesFromFile($hFile,-4,4);
        $o = new BinaryStream($bytes);
        $o->setEndian(Endian::BIG_ENDIAN);
        return $o->readUint32();
    }
    /*
     * version|row_length|row_type|row_data
     *    1字节|2字节|1字节|2^16字节以内
     */
    protected function getItemLength(){
        $bytes = 0;
        if (count(self::$_data)) {
            foreach (self::$_data as $v) {
                $bytes += $v['len'];
            }
            return $bytes;
        }
        return 0;
    }
    protected function getMetaFile()
    {
        $now = Date('Y_m_d', time());
        return realpath($this->default_save_dir) . DIRECTORY_SEPARATOR . $this->log_file_prefix . 'meta_' . $now . '.idx';
    }

    protected function getLogFile(){
        $now = Date('Y_m_d', time());
        return realpath($this->default_save_dir) . DIRECTORY_SEPARATOR . $this->log_file_prefix. $now . '.pda';
    }

    protected function saveHeader($hFile){
        BinaryWriter::flush($hFile,new PandaHeader(),0);
    }
    public function getHeader($hFile){
        return BinaryReader::getPacketFromFile($hFile,0,new PandaHeader());
    }

    public function getMetaItemCount($hFile){
        $header = $this->getHeader($hFile);
        return $header->item_count;
    }
    /*
     * @func 保存日志总数,4个字节
     */
    protected function saveMetaItemCount($hFile,$count){
        $o = new BinaryStream();
        $o->setEndian(Endian::BIG_ENDIAN);
        $o->writeUInt32($count);
        $bytes = $o->toBytes();
        BinaryWriter::flushRawBytes($hFile,$bytes,6);
    }
    /*
     *@func 保存请求的meta数据
     */
    protected function saveMetaOneItem($hFile,$offset_start,$offset_end){
        $o = new BinaryStream();
        $o->setEndian(Endian::BIG_ENDIAN);
        $o->writeUInt32($offset_start);
        $o->writeUInt32($offset_end);
        $bytes = $o->toBytes();
        BinaryWriter::append($hFile,$bytes);
    }
    public function decode($page_offset,$page_size=20,$asc=true){
        // 解析meta二进制文件
        $metaFile = $this->getMetaFile();
        $hFile = BinaryReader::open($metaFile);
        //获取总数目
        $totalItems = $this->getMetaItemCount($hFile);
        //获取真实数据所在偏移meta信息
        $pos = self::calcItemPos($totalItems,$page_offset,$page_size,$asc);
        $rawMeta = BinaryReader::getRawBytesFromFile($hFile,$pos->offset,$pos->size);
        $arrMetaItem = self::decodeMetaItem($rawMeta,$pos->size/8);
        BinaryReader::close($hFile);
        // 解析data二进制文件
        $logFile = $this->getLogFile();
        $hFile = BinaryReader::open($logFile);
        $items = [];
        foreach($arrMetaItem as $k=>$v){
            $items[] = self::decodeLogData($hFile,$v['start'],$v['end']);
        }
        BinaryReader::close($hFile);
        return $items;
    }

    public static function decodeLogData($hFile,$start,$end){
        $items = [];
        $offset = $start;
        $byteCount = $end-$start+1;
        $rawData = BinaryReader::getRawBytesFromFile($hFile,$offset,$byteCount);
        $o = new BinaryStream($rawData);
        $o->setEndian(Endian::BIG_ENDIAN);
        while($byteCount){
            $len = $o->readUint32();
            $type = $o->readUByte();
            $data = $o->readStringClean($len-self::META_BYTES_ITEM);
            $items[] =['type'=>$type,'data'=>self::parseData($type,$data)];
            $byteCount -= $len;
        }
        return $items;
    }

    public static function parseData($type,$data){
        if($type == self::FLAG_ARRAY || $type == self::FLAG_OBJECT) {
            return json_decode($data);
        }
        return $data;
    }

    public static function decodeMetaItem($rawBytes,$count){
        $meta= [];
        $o = new BinaryStream($rawBytes);
        $o->setEndian(Endian::BIG_ENDIAN);
        for($i=0;$i<$count;$i++){
            $start = $o->readUInt32();
            $end = $o->readUInt32();
            $meta[] = ['start'=>$start,'end'=>$end];
        }
        return $meta;
    }

    /*
     * @para $total 所有总数据数目
     * @para $page_offset 当前偏移
     * @para $page_size 每页显示的大小
     * @para $asc true:按写入时间顺序读取,false:按写入时间倒序读取
     */
    public function calcItemPos($total,$page_offset,$page_size,$asc=true){
        if($page_offset>$total){
            return -1;
        }
        if(($page_offset+$page_size)>$total){
            $page_size = $total-$page_offset;
        }
        $o= new \stdClass();
        if($asc) {
            $o->offset = 10+$page_offset*8;
            $o->size = $page_size*8;
        }else{
            $o->offset =(10+8*$total)-($page_offset+$page_size)*8;
            $o->size = $page_size*8;
        }
        return $o;
    }
}