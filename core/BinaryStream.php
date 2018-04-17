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
 * @Date      2017/11/30
 * @Time      9:28
 *
 * @desc binary stream is a wrapper for PHP pack/unpack functions
 */
namespace ofix\panda\core;
class BinaryStream
{
    private $data = '';
    private $position = 0;
    private $endian;
    private $isLittleEndian;
    public $needConvertEndian;

    private static $systemEndian = null;

    public static function systemEndian()
    {
        if(self::$systemEndian === null){
            self::$systemEndian = pack('v', 1) == pack('s', 1) ?
                Endian::LITTLE_ENDIAN : Endian::BIG_ENDIAN;
        }
        return self::$systemEndian;
    }

    public function __construct($data = null)
    {
        $this->setEndian(self::systemEndian());
        $this->data = is_null($data) ? $this->data : $data;
    }

    /*
     * Endian::LITTLE_ENDIAN or Endian::BIG_ENDIAN
     */
    public function getEndian()
    {
        return $this->endian;
    }

    public function setEndian($value)
    {
        $this->endian = $value == Endian::BIG_ENDIAN ? Endian::BIG_ENDIAN : Endian::LITTLE_ENDIAN;
        $this->isLittleEndian = $this->endian == Endian::LITTLE_ENDIAN;
        $this->needConvertEndian = $this->endian != self::systemEndian();
    }

    public function getLength()
    {
        return strlen($this->data);
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($value)
    {
        $this->position = $value;
    }

    public function getBytesAvailable()
    {
        return strlen($this->data) - $this->position;
    }

    public function clear()
    {
        $this->data = '';
        $this->position = 0;
    }

    public function readBoolean()
    {
        if($this->getBytesAvailable() < 1){
            return null;
        }

        $arr = unpack('@' . $this->position . '/ck', $this->data);
        $this->position++;
        return boolval($arr['k']);
    }

    public function readByte()
    {
        if($this->getBytesAvailable() < 1){
            return false;
        }

        $arr = unpack('@' . $this->position . '/ck', $this->data);
        $this->position++;
        return $arr['k'];
    }

    public function readUByte()
    {
        if($this->getBytesAvailable() < 1){
            return false;
        }

        $arr = unpack('@' . $this->position . '/Ck', $this->data);
        $this->position++;
        return $arr['k'];
    }

    public function readInt16()
    {
        //php缺少有符号型整数的大小端读取，参见补码相关知识
        if(($i = $this->readUInt16()) !== false && $i > 0x7fff){
            $i = -(~($i - 1) & 0xffff);
        }

        return $i;
    }

    public function readUInt16()
    {
        if($this->getBytesAvailable() < 2){
            return false;
        }

        $key = $this->needConvertEndian ? ($this->isLittleEndian ? '/vk' : '/nk') : '/Sk';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += 2;
        return $arr['k'];
    }

    public function readInt32()
    {
        if(($i = $this->readUInt32()) !== false && $i > 0x7fffffff){
            $i = -(~($i - 1) & 0xffffffff);
        }

        return $i;
    }

    public function readUInt32()
    {
        if($this->getBytesAvailable() < 4){
            return false;
        }

        $key = $this->needConvertEndian ? ($this->isLittleEndian ? '/Vk' : '/Nk') : '/Lk';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += 4;
        return $arr['k'];
    }

    public function readInt64()
    {
        if(($i = $this->readUInt64()) !== false && $i > 0x7fffffffffffffff){
            $i = -(~($i - 1));
        }

        return $i;
    }

    /**
     * php has't uint64，so be sure the number is in int64.min ~ int64.max
     * @return [type] [description]
     */
    public function readUInt64()
    {
        if($this->getBytesAvailable() < 8){
            return false;
        }

        $key = $this->needConvertEndian ? ($this->isLittleEndian ? '/Pk' : '/Jk') : '/Qk';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += 8;
        return $arr['k'];
    }

    public function readFloat()
    {
        if($this->getBytesAvailable() < 4){
            return false;
        }

        if($this->needConvertEndian){
            $data = $this->readBytes(4);
            $arr = unpack('fk', strrev($data));
        } else{
            $arr = unpack('@' . $this->position . '/fk', $this->data);
            $this->position += 4;
        }

        return $arr['k'];
    }

    public function readDouble()
    {
        if($this->getBytesAvailable() < 8){
            return false;
        }

        if($this->needConvertEndian){
            $data = $this->readBytes(8);
            $arr = unpack('dk', strrev($data));
        } else{
            $arr = unpack('@' . $this->position . '/dk', $this->data);
            $this->position += 8;
        }

        return $arr['k'];
    }

    public function readBytes($count)
    {
        if($this->getBytesAvailable() < $count){
            return false;
        }

        $key = '/a'. $count . 'k';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += $count;
        return $arr['k'];
    }

    /**
     * first read strlen(2byte), then read str
     */
    public function readString()
    {
        $len = $this->readUInt16();

        if($len <=0 || $this->getBytesAvailable() < $len){
            return false;
        }

        $key = '/a'. $len . 'k';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += $len;
        return $arr['k'];
    }

    /*
     * @func 读取指定字节字符串
     * @author code lighter
     */
    public function readStringClean($len){
        if($len <=0){
            return false;
        }
        $key = '/a'. $len . 'k';
        $arr = unpack('@' . $this->position . $key, $this->data);
        $this->position += $len;
        return $arr['k'];
    }

    public function writeBoolean($value)
    {
        $this->data .= pack('c', $value ? 1 : 0);
        $this->position++;
    }

    public function writeByte($value)
    {
        $this->data .= pack('c', $value);
        $this->position++;
    }

    public function writeUByte($value)
    {
        $this->data .= pack('C', $value);
        $this->position++;
    }

    public function writeInt16($value)
    {
        //php缺少有符号型整数的大小端写入，参见补码相关知识
        if($value < 0){
            $value = -(~($value & 0xffff) + 1);
        }

        $this->writeUInt16($value);
    }

    public function writeUInt16($value)
    {
        $key = $this->needConvertEndian ? ($this->isLittleEndian ? 'v' : 'n') : 'S';
        $this->data .= pack($key, $value);
        $this->position += 2;
    }

    public function writeInt32($value)
    {
        if($value < 0){
            $value = -(~($value & 0xffffffff) + 1);
        }

        $this->writeUInt32($value);
    }

    public function writeUInt32($value)
    {
        $key = $this->needConvertEndian ? ($this->isLittleEndian ? 'V' : 'N') : 'L';
        $this->data .= pack($key, $value);
        $this->position += 4;
    }

    public function writeInt64($value)
    {
        if ($value < 0) {
            $value = -(~$value + 1);
        }

        $this->writeUInt64($value);
    }

    /**
     * php has't uint64，so be sure the number is in int64.min ~ int64.max
     * @return [type] [description]
     */
    public function writeUInt64($value)
    {
        $key = $this->needConvertEndian ? ($this->isLittleEndian ? 'P' : 'J') : 'Q';
        $this->data .= pack($key, $value);
        $this->position += 8;
    }

    public function writeFloat($value)
    {
        $this->data .= $this->needConvertEndian ? strrev(pack('f', $value)) : pack('f', $value);
        $this->position += 4;
    }

    public function writeDouble($value)
    {
        $this->data .= $this->needConvertEndian ? strrev(pack('d', $value)) : pack('d', $value);
        $this->position += 8;
    }

    public function writeBytes($value)
    {
        $len = strlen($value);
        $this->data .= pack('a' . $len, $value);
        $this->position += $len;
    }

    /*
     * first read strlen(2byte), then read str
     */
    public function writeString($value)
    {
        $len = strlen($value);
        $this->writeUInt16($len);
        $this->data .= pack('a' . $len, $value);
        $this->position += $len;
    }
    /*
     * @func 写入二进制流补充版
     * @author code lighter
     */
    public function writeStringClean($data,$byte_count)
    {
        $this->data .=pack('a'.$byte_count,$data);
        $this->position += $byte_count;
    }

    public function toBytes()
    {
        return $this->data;
    }
}