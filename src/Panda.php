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
namespace common\panda;

use yii\base\Model;
use yii\db\Query;

class Panda
{
    protected static $_instance = null;
    protected static $data = null;
    protected static $debug_trace = null;
    protected static $last_flush_begin = 0; //上次写入文件的开始位置
    protected static $last_flush_end   = 0; //上次写入文件的结束位置
    protected static $last_flush_bytes = 0; //上次写入文件的字节数量
    private $prefix  = 'panda_log_'; //log日志前缀
    private $default_save_dir = __DIR__ . '/../../company/runtime/panda_log/';
    private $is_rpc;         // 是否是RPC远程调用
    const FLAG_HTTP_REQUEST = 5; // 格式化每次请求的数据

    private function __construct()
    {
        self::$data = [];
        self::$debug_trace = [];
    }
    public static function instance()
    {
        if (is_null(self::$_instance) || !isset(self::$_instance)) {
            self::$_instance = new self();
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

    public function trace($trace1,$trace2,$para){
        $o = new \stdClass();
        $o->class = $trace2['class'];
        $o->function = $trace2['function'];
        $o->line_no = $trace1['line'];
        $o->args = $para;
        return $o;
    }

    public function reflectFunctionParameter($class,$line){
        if ($class) {
            $file = realpath(dirname(dirname(__DIR__))).'\\'.$class.'.php';
            if (file_exists($file)) {
                $fp = fopen($file, 'rb');
                if (!$fp)
                    return '$file_dummy';
                for ($i=1; $i<$line; ++$i) {
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

    public function log($content = '')
    {
        $debug = debug_backtrace();
        $para_name=self::reflectFunctionParameter($debug[1]['class'],$debug[0]['line']);
        self::$debug_trace[] = $this->trace($debug[0],$debug[1],$para_name);
        if(is_array($content)){
            $o = new RecordArray();
            $o->log($content);
            self::$data[] = $o;
        }else if(($content instanceof Query) || ($content instanceof Model)){
            $o = new RecordSql();
            $o->log($content);
            self::$data[] = $o;
        }else if(is_object($content)){
            $o = new RecordObject();
            $o->log($content);
            self::$data[] = $o;
        }else if(is_string($content)){
            $o = new RecordString();
            $o->log($content);
            self::$data[] = $o;
        }
    }

    /*
     * 保存文件日志
     */
    public function flush()
    {
        $this->saveMetaFile(); //保存元数据
        $logFile = $this->getLogFile();
        if (count(self::$data)) {
            //写入二进制流
            $hFile = BinaryWriter::open($logFile);
            $o = new BinaryStream();
            $o->setEndian(Endian::BIG_ENDIAN);
            foreach (self::$data as $v) {
                $v->write($o);
            }
            $bytes = $o->toBytes();
            BinaryWriter::append($hFile,$bytes);
            BinaryWriter::close($hFile);
        }
        self::$data = []; //存盘的时候，必须释放掉变量内存,因为这个是单例
        //否则循环测试写入的时候，会出问题。
        self::$debug_trace = [];
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
     * row_length|row_type|row_data
     *    4字节|1字节|2^16字节以内
     */
    protected function getItemLength(){
        $bytes = 0;
        if (count(self::$data)) {
            foreach (self::$data as $v) {
                $bytes += $v->getLength();
            }
            return $bytes;
        }
        return 0;
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
        if($page_size <1){
            return [];
        }
        // 解析meta二进制文件
        $metaFile = $this->getMetaFile();
        if(!file_exists($metaFile)){
            return [];
        }
        $hFile = BinaryReader::open($metaFile);
        //获取总数目
        $totalItems = $this->getMetaItemCount($hFile);
        //获取真实数据所在偏移meta信息
        $pos = self::calcItemPos($totalItems,$page_offset,$page_size,$asc);
        if(is_null($pos)){
            return ['total'=>$totalItems,'data'=>[]];
        }
        $rawMeta = BinaryReader::getRawBytesFromFile($hFile,$pos->offset,$pos->size);
        $arrMetaItem = self::decodeMetaItem($rawMeta,$pos->size/8);
        BinaryReader::close($hFile);
        // 解析data二进制文件
        $logFile = $this->getLogFile();
        $hFile = BinaryReader::open($logFile);
        $items = [];
        foreach($arrMetaItem as $k=>$v){
            $items[] = $this->decodeLogData($hFile,$v['start'],$v['end']);
        }
        BinaryReader::close($hFile);
        return ['total'=>$totalItems,'data'=>$items];
    }

    public function decodeLogData($hFile,$start,$end){
        $items = [];
        $offset = $start;
        $byteCount = $end-$start+1;
        $rawData = BinaryReader::getRawBytesFromFile($hFile,$offset,$byteCount);
        $stream= new BinaryStream($rawData);
        $stream->setEndian(Endian::BIG_ENDIAN);
        while($byteCount){
            $len = $stream->readUint32();
            $type = $stream->readUByte();
            $items[] =['type'=>$type,'record'=>$this->decodeRecord($type,$len-Record::META_BYTES,$stream)];
            $byteCount -= $len;
        }
        return $items;
    }

    public function decodeRecord($type,$byte_count,$stream){
        $data = null;
        $o = null;
        switch($type){
            case Record::RECORD_TYPE_STRING:{
                $o = new RecordString();
                $o->read($stream,$byte_count);
                break;
            }
            case Record::RECORD_TYPE_OBJECT:{
                $o = new RecordObject();
                $o->read($stream,$byte_count);
                break;
            }
            case Record::RECORD_TYPE_ARRAY:{
                $o = new RecordArray();
                $o->read($stream,$byte_count);
                break;
            }
            case Record::RECORD_TYPE_SQL:{
                $o = new RecordSql();
                $o->read($stream,$byte_count);
                break;
            }
            case Record::RECORD_TYPE_REQUEST:{
                $o = new RecordRequest();
                $o->read($stream,$byte_count);
                break;
            }
            case Record::RECORD_TYPE_LOGIN:{
                $o = new RecordLogin();
                $o->read($stream,$byte_count);
                break;
            }
            default:
                break;
        }
        if($o) {
            return $o->getData();
        }
        return null;
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
        if($page_offset>=$total){
            return null;
        }
        if(($page_offset+$page_size)>=$total){
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
    protected function getMetaFile()
    {
        $now = Date('Y_m_d', time());
        return realpath($this->default_save_dir) . DIRECTORY_SEPARATOR . $this->prefix . 'meta_' . $now . '.idx';
    }

    protected function getLogFile(){
        $now = Date('Y_m_d', time());
        return realpath($this->default_save_dir) . DIRECTORY_SEPARATOR . $this->prefix. $now . '.pda';
    }
}