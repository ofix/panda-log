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
 * @Date      2017/11/29
 * @Time      18:35
 */
namespace ofix\panda\core;
class BinaryReader{
    public static function getRawBytesFromFile($hFile,$offset,$byte_count){
        if($offset <0){
            $ret = fseek($hFile,$offset,SEEK_END);
        }else {
            $ret = fseek($hFile, $offset);
        }
        if($ret != -1){
            return fread($hFile,$byte_count);
        }
        return false;
    }
    public static function getPacketFromFile($hFile,$offset,$packet){
        $byte_count = 0;
        $format = '';
        if(!$packet instanceof BinaryPacket){
            throw new \Exception(__METHOD__.' $packet parameter is invalid.');
        }
        $o = new \ReflectionObject($packet);
        $static = $o->getStaticProperties();
        foreach($static as $k=>$v){
            $byte_count += BinaryType::getTypeByteCount($v[0]);
            $format .= '/'.$v[0].$k;
        }
        $format = substr($format,1);
        $binary_data =  self::getRawBytesFromFile($hFile,$offset,$byte_count);
        $data = unpack($format,$binary_data);
        return (object)($data);
    }

    public static function open($filePath){
        if(file_exists($filePath)) {
            return fopen($filePath, "rb+"); // a+ 读写方式打开，会忽略fseek,所以必须rb+方式打开
        }else{
            $hFile = fopen($filePath,"wb+");
            fclose($hFile);
            return fopen($filePath,"rb+");
        }
    }
    public static function close($hFile){
        fclose($hFile);
    }
}