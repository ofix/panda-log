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
 * @Date      2017/12/25
 * @Time      17:10
 */

namespace ofix\panda\core;

class PandaWriter
{
    public static function write(){
//        echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//        echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//        echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
    }
    public static function testLexer(){
        $str1 = '/****/$class->object->things  ,/*xxx*/$user';
        $str2 = '$long_variable_test["keys"] /*userafdf*/, tmp';
        $str3 = '$one, $two,$thr';
        $str4 = '12343  ,"44544",["computer_tings"=>"words"]';
        //字符串中的注释 测试不通过
        $str5 = '\'/***/this_is_string\'';
        $str6 = 'a';
        $str7 = 'Class::StaticMethod()';
        $str8 = 'Class::StaticMethod($var1)';
        //方法中的逗号 测试不通过
        $str9 = 'Class::StaticMethod($var1,$var2)';
        //new 后面的空格不应该过滤
        $str10 = '(new Class())->aMethod()';
        $str11 = '$class->callMethod()';
        print_r((new Lexer($str1))->parseArgumentList());
        print_r((new Lexer($str2))->parseArgumentList());
        print_r((new Lexer($str3))->parseArgumentList());
        print_r((new Lexer($str4))->parseArgumentList());
        print_r((new Lexer($str5))->parseArgumentList());
        print_r((new Lexer($str6))->parseArgumentList());
        print_r((new Lexer($str7))->parseArgumentList());
        print_r((new Lexer($str8))->parseArgumentList());
        print_r((new Lexer($str9))->parseArgumentList());
        print_r((new Lexer($str10))->parseArgumentList());
        print_r((new Lexer($str11))->parseArgumentList());
    }
}
