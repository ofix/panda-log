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

use ofix\PandaLog\core\BinaryReader;
use ofix\PandaLog\core\BinaryStream;
use ofix\PandaLog\core\BinaryWriter;
use ofix\PandaLog\core\Endian;
use ofix\PandaLog\core\PandaHeader;
use ofix\PandaLog\core\Record;
use ofix\PandaLog\core\RecordArray;
use ofix\PandaLog\core\RecordBool;
use ofix\PandaLog\core\RecordLogin;
use ofix\PandaLog\core\RecordNull;
use ofix\PandaLog\core\RecordNumber;
use ofix\PandaLog\core\RecordObject;
use ofix\PandaLog\core\RecordRequest;
use ofix\PandaLog\core\RecordSql;
use ofix\PandaLog\core\RecordString;
use yii\base\Model;
use yii\db\Query;
date_default_timezone_set('Asia/Shanghai');
class Panda
{
    protected static $instance = null;
    protected static $data = null;
    protected static $debug_trace = null;
    protected static $last_flush_begin = 0; //上次写入文件的开始位置
    protected static $last_flush_end   = 0; //上次写入文件的结束位置
    protected static $last_flush_bytes = 0; //上次写入文件的字节数量
    protected static $default_save_dir = '';

    private function __construct()
    {
        self::$data = [];
        self::$debug_trace = [];
    }
    public static function instance()
    {
        if (is_null(self::$instance) || !isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
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
     * @func 调用信息记录
     */
    public function trace($trace1,$trace2,$para){
        $o = new \stdClass();
        $o->cls  = $trace2['class'];
        $o->func = $trace2['function'];
        $o->type = $trace2['type'];
        $o->line = $trace1['line'];
        $o->args = $para;
        $o->time = date('Y-m-d H:i:s',time());
        return $o;
    }

    /*
     * @func 提取传入Panda::instance()->log()的参数名称
     * @para $class 调用Panda::instance()->log()函数所在类
     * @para $line 调用Panda::instance()->log()所在代码行号
     */
    private function reflectFunctionParameter($class,$line_no){
        if ($class) {
            $path = realpath(dirname(dirname(__DIR__))).'\\'.$class.'.php';
            $file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            if (file_exists($file)) {
                $fp = fopen($file, 'rb');
                if (!$fp)
                    return '$file_dummy';
                for ($i=1; $i<$line_no; ++$i) {
                    fgets($fp);
                }
                $function = fgets($fp);
                preg_match('/\(\s*(\/\*.*\*\/)*\s*(\$\w+)+\s*(\/\*.*\*\/)*\)/',$function,$matches);
                fclose($fp);
                if(isset($matches[2])) {
                    return $matches[2];
                }else{
                    return '$matches';
                }
            }
            return '$dummy';
        }
        return '$file_non_exist';
    }

    public function getDebugInfo(){
        return self::$debug_trace;
    }

    public function exception($name,$e)
    {
        if ($e instanceof \Exception) {
            $debug = debug_backtrace();
            self::$debug_trace[] = $this->trace($debug[0], $debug[1], $name);
            $o = new \stdClass();
            $o->message = $e->getMessage();
            $o->file = $e->getFile();
            $o->code = $e->getCode();
            $o->line = $e->getLine();
            $this->logData($o);
            $this->flush();
            return $this;
        }
        return $this;
    }


    public function log2($name,$content){
        $debug = debug_backtrace();
        self::$debug_trace[] = $this->trace($debug[0],$debug[1],$name);
        $this->logData($content);
        return $this;
    }
    public function log($content = '')
    {
        $debug = debug_backtrace();
        $para_name=$this->reflectFunctionParameter($debug[1]['class'],$debug[0]['line']);
        self::$debug_trace[] = $this->trace($debug[0],$debug[1],$para_name);
        $this->logData($content);
        return $this;
    }
    protected function logData($data){
        $o = null;
        if(is_array($data)){
            $o = new RecordArray();
        }else if(($data instanceof Query) || ($data instanceof Model)){
            $o = new RecordSql();
        }else if(is_object($data)){
            $o = new RecordObject();
        }else if(is_string($data)){
            $o = new RecordString();
        }else if(is_numeric($data)){
            $o = new RecordNumber();
        }else if(is_bool($data)){
            $o = new RecordBool();
        }else if(is_null($data)){
            $o = new RecordNull();
        }
        if(!is_null($o)){
            $o->log($data);
            self::$data[] = $o;
        }
    }

    /*
     * 保存文件日志
     */
    public function flush()
    {
        try {
            $this->saveMeta(); //保存元数据
            $logFile = $this->getLogFile();
            if (count(self::$data)) {
                //写入二进制流
                $hFile = BinaryWriter::open($logFile);
                $stream = new BinaryStream();
                $stream->setEndian(Endian::BIG_ENDIAN);
                foreach (self::$data as $k => $v) {
                    $debug_len = self::getDebugItemLength($k);
                    $v->write($stream, $debug_len);
                    $this->writeDebugInfo($stream, $k, $debug_len);
                }
                $bytes = $stream->toBytes();
                BinaryWriter::append($hFile, $bytes);
                fflush($hFile); //立即刷新到磁盘文件
                BinaryWriter::close($hFile);
            }
            self::$data = []; //存盘的时候，必须释放掉变量内存,因为这个是单例
            //否则循环测试写入的时候，会出问题。
            self::$debug_trace = [];
        }catch(\Exception $e){
            return false;
        }
    }

    /*
     * @func 写入Debug信息
     */
    private function writeDebugInfo(BinaryStream $stream,$k,$debug_total_bytes){
        $stream->writeUInt16($debug_total_bytes);
        $stream->writeStringClean(json_encode(self::$debug_trace[$k]),$debug_total_bytes-Record::META_DEBUG_BYTES);
    }

    /*
     * 保存元数据
     * 魔幻数|主版本号|小版本号|总请求数|请求1,请求2,请求3,....
     */
    protected function saveMeta()
    {
        self::ensureDir($this->getDefaultSaveDir());
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
    /*
     * @func 获取保存的日志文件大小
     */
    protected function getLastLogFilePos($hFile){
        $bytes = BinaryReader::getRawBytesFromFile($hFile,-4,4);
        $o = new BinaryStream($bytes);
        $o->setEndian(Endian::BIG_ENDIAN);
        return $o->readUint32();
    }
    /*
     * row_length|row_type|data_length|data|debug_length|debug
     *    4字节|1字节|2字节|数据数据|2字节|调试数据
     */
    protected function getItemLength(){
        $bytes = 0;
        if (count(self::$data)) {
            foreach (self::$data as $k=>$v) {
                $length = $v->getLength()+self::getDebugItemLength($k);
                $bytes += $length;
            }
            return $bytes;
        }
        return 0;
    }

    protected function getDebugItemLength($i){
        $data = json_encode(self::$debug_trace[$i]);
        return strlen($data)+Record::META_DEBUG_BYTES;
    }

    /*
     * @func 保存panda log 头部meta信息
     */
    protected function saveHeader($hFile){
        BinaryWriter::flush($hFile,new PandaHeader(),0);
    }
    public function getHeader($hFile){
        return BinaryReader::getPacketFromFile($hFile,0,new PandaHeader());
    }
    /*
     * @func 获取所有记录的请求总数
     */
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
     * @func 保存请求的meta数据
     */
    protected function saveMetaOneItem($hFile,$offset_start,$offset_end){
        $o = new BinaryStream();
        $o->setEndian(Endian::BIG_ENDIAN);
        $o->writeUInt32($offset_start);
        $o->writeUInt32($offset_end);
        $bytes = $o->toBytes();
        BinaryWriter::append($hFile,$bytes);
    }
    /*
     * @func 读取保存的日志信息
     */
    public function decode($page_offset,$page_size=20,$asc=false,$date = ''){
        if($page_size <1){
            return [];
        }
        // 解析meta二进制文件
        $metaFile = $this->getMetaFile($date);
        if(!file_exists($metaFile)){
            return [];
        }
        $hFile = BinaryReader::open($metaFile);
        //获取总数目
        $totalItems = $this->getMetaItemCount($hFile);
        //获取真实数据所在偏移meta信息
        $pos = self::calcItemPos($totalItems,$page_offset,$page_size,$asc);
        if(is_null($pos)){
            return ['total'=>$totalItems,'records'=>[]];
        }
        $rawMeta = BinaryReader::getRawBytesFromFile($hFile,$pos->offset,$pos->size);
        $arrMetaItem = self::decodeMetaItem($rawMeta,$pos->size/8);
        BinaryReader::close($hFile);
        // 解析data二进制文件
        $logFile = $this->getLogFile($date);
        $hFile = BinaryReader::open($logFile);
        $items = [];
        foreach($arrMetaItem as $k=>$v){
            if($v['start']<=0 || $v['end']<=0){
                continue;
            }
            $items[] = $this->decodeLogData($hFile,$v['start'],$v['end']);
        }
        BinaryReader::close($hFile);
        return ['total'=>$totalItems,'records'=>$items];
    }
    /*
     * @func 解析多个请求日志数据
     */
    public function decodeLogData($hFile,$start,$end){
        $items = [];
        $byteCount = $end-$start+1;
        $rawData = BinaryReader::getRawBytesFromFile($hFile,$start,$byteCount);
        $stream= new BinaryStream($rawData);
        $stream->setEndian(Endian::BIG_ENDIAN);
        while($byteCount){
            $len = $stream->readUint32();
            $type = $stream->readUByte();
            $data_len = $stream->readUInt16();
            $data = $this->decodeRecord($type,$data_len-Record::META_DATA_BYTES,$stream);
            $debug_len = $stream->readUInt16();
            $debug = $this->decodeDebug($debug_len-Record::META_DEBUG_BYTES,$stream);
            $items[] = ['log'=>$data,'type'=>$type,'debug'=>$debug];
            $byteCount -= $len;
        }
        return $items;
    }

    public function decodeDebug($byte_count,BinaryStream $stream){
        return json_decode($stream->readStringClean($byte_count));
    }
    /*
     * @func 解析一个请求日志数据
     */
    public function decodeRecord($type,$byte_count,$stream){
        $data = null;
        $o = null;
        switch($type){
            case Record::RECORD_TYPE_STRING:{
                $o = new RecordString();
                break;
            }
            case Record::RECORD_TYPE_OBJECT:{
                $o = new RecordObject();
                break;
            }
            case Record::RECORD_TYPE_ARRAY:{
                $o = new RecordArray();
                break;
            }
            case Record::RECORD_TYPE_SQL:{
                $o = new RecordSql();
                break;
            }
            case Record::RECORD_TYPE_REQUEST:{
                $o = new RecordRequest();
                break;
            }
            case Record::RECORD_TYPE_LOGIN:{
                $o = new RecordLogin();
                break;
            }
            case Record::RECORD_TYPE_NUMBER:{
                $o = new RecordNumber();
                break;
            }
            case Record::RECORD_TYPE_BOOL:{
                $o = new RecordBool();
                break;
            }
            case Record::RECORD_TYPE_NULL:{
                $o = new RecordNull();
                break;
            }
            default:
                break;
        }
        if($o) {
            $o->read($stream,$byte_count);
            return $o->getData();
        }
        return null;
    }
    /*
     * @func 解析meta数据
     */
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
    public function calcItemPos($total,$page_offset,$page_size,$asc=false){
        if($page_offset>=$total){
            return null;
        }
        if(($page_offset+$page_size)>=$total){
            $page_size = $total-$page_offset;
        }
        if(!$asc){ // 倒序查询
            $page_offset = ($total - $page_size);
        }
        $o= new \stdClass();
        $o->offset =10+$page_offset*8;
        $o->size = $page_size*8;
        return $o;
    }
    /*
     * @func 递归创建目录，如果目标目录不存在的话
     */
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
    protected function getMetaFile($date='')
    {
        if($date == '') {
            $now = Date('Ymd', time());
        }else{
            $now = $date;
        }
        return realpath($this->getDefaultSaveDir()) . DIRECTORY_SEPARATOR .'panda_meta_' . $now . '.idx';
    }

    public function getLogFile($date=''){
        if($date == '') {
            $now = Date('Ymd', time());
        }else{
            $now = $date;
        }
        return realpath($this->getDefaultSaveDir()) . DIRECTORY_SEPARATOR .'panda_data_'. $now . '.pda';
    }

    public function getDefaultSaveDir(){
        $default_url = \Yii::getAlias(self::$default_save_dir);
        return $default_url;
    }

    public function setDefaultSaveDir($log_dir){
        self::$default_save_dir = $log_dir;
    }
}
