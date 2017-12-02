<?php
/*
 * Author: code lighter
 * Date: 2017/11/29 0029
 * Time: 下午 18:35
 */
namespace common\panda;
class BinaryWriter{
    public static function flush($hFile,BinaryPacket $packet,$offset){
        $_data = '';
        $byte_count = 0;
        $o = new \ReflectionObject($packet);
        $static = $o->getStaticProperties();
        foreach($static as $k=>$v){
            $_data.= pack($v[0],$v[1]);
            $byte_count += BinaryType::getTypeByteCount($v[0]);
        }
        $ret = fseek($hFile,$offset);
        if($ret != -1){
            $flushed = fwrite($hFile,$_data,$byte_count);
            if($flushed != $byte_count){
                return false;
            }
            return true;
        }
        return false;
    }
    public static function flushRawBytes($hFile,$binary_data,$offset){
        $byte_count = strlen($binary_data);
        $ret = fseek($hFile,$offset);
        $cur_pos = ftell($hFile);
        if($ret != -1){
            $flushed = fwrite($hFile,$binary_data,$byte_count);
            if($flushed != $byte_count){
                return false;
            }
            return true;
        }
        return false;
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

    public static function append($hFile,$binary_data){
        $byte_count = strlen($binary_data);
        $ret = fseek($hFile,0,SEEK_END);
        if($ret != -1){
            $flushed = fwrite($hFile,$binary_data,$byte_count);
            if($flushed != $byte_count){
                return false;
            }
            return true;
        }
        return false;
    }
}