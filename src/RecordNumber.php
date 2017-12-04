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
 * @Date      2017/12/4
 * @Time      13:44
 */

namespace common\panda;

class RecordNumber extends Record
{
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_NUMBER;
    }
    public function log($string){
        $this->data = $string;
    }
    public function getLength(){
        return 4+self::META_DATA_BYTES;
    }
    public function write(BinaryStream $stream,$debug_len){
        $len =4; // UInt 32位
        $total_bytes = $debug_len+$len+self::META_DATA_BYTES+self::META_ITEM_BYTES;
        $stream->writeUInt32($total_bytes); // 4个字节长度
        $stream->writeUByte($this->type);
        $stream->writeUInt16($len+self::META_DATA_BYTES);
        $stream->writeUInt32($this->data); //剩下的都是数据字节
    }
    public function read(BinaryStream $stream,$byte_count){
        $this->data = $stream->readInt32();
    }
}