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
    const RECORD_TYPE_EMPTY  = 1;
    const RECORD_TYPE_STRING = 2;
    const RECORD_TYPE_SQL    = 3;
    const RECORD_TYPE_ARRAY  = 4;
    const RECORD_TYPE_OBJECT = 5;
    const RECORD_TYPE_REQUEST= 6;
    const RECORD_TYPE_LOGIN  = 7;
    const RECORD_TYPE_NUMBER = 8;
    const RECORD_TYPE_BOOL   = 9;
    const EMPTY_PLACE_HOLDER = 'nul'; // 空字符串占位符
    const EOL = '\n';                 // 分割符
    const META_DATA_BYTES  = 2;       // 每项数据占用的字节数
    const META_DEBUG_BYTES = 2;
    const META_ITEM_BYTES  = 5;       // 所有字节
    public $type;
    public $data;
    public function __construct()
    {
        $this->type = self::RECORD_TYPE_EMPTY;
        $this->data = null;
    }
    /*
     * @para stream BinaryStream object
     * @para $byte_count 字节数
     */
    public function read(BinaryStream $stream,$byte_count){

    }
    public function getData(){
        return $this->data;
    }
    public function getLength(){
        return strlen($this->data)+self::META_DATA_BYTES+self::META_ITEM_BYTES;
    }
    public function write(BinaryStream $stream,$debug_len){
        $len = strlen($this->data);
        $total_bytes = $debug_len+$len+self::META_DATA_BYTES+self::META_ITEM_BYTES;
        $stream->writeUInt32($total_bytes); // 4个字节长度
        $stream->writeUByte($this->type);
        $stream->writeUInt16($len+self::META_DATA_BYTES);
        $stream->writeStringClean($this->data,$len); //剩下的都是数据字节
    }
}