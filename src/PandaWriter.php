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

namespace common\panda;
use common\models\UserCompany;
use common\models\Dporder;
class PandaWriter
{
    public static function write(){
        $array = ['中国武汉', '深圳福田国际金融中心'];
        $distributor_order = new \stdClass();
        $distributor_order->name = "tom";
        $distributor_order->job = "advanced senior PHP programmer";
        $distributor_order->age = 20;
        $distributor_order->sex = "unknown things";
        $distributor_order->test= "这是一条广播消息，楼下来了快递，火速来取！";
        $distributor_order->push = "同志快醒醒，你有一个bug还没有修复";
        $distributor_order->xx_zLush = 3234324234.20;
        $this_is_a_long_str = 'This is not a good Thing';
        $online_company = UserCompany::find()->where(['user_id' => 10, 'company_id' => 1])
            ->andWhere(['something_other'=>1])
            ->andWhere(['in','query_condition',[1,100,20]])
            ->innerJoin(['user','user.id=user_company.user_id']);
        $panda = Panda::instance();
        $panda->log(  $array/*xxxx*/);
        $panda->log( /***/ $distributor_order /**/);
        $panda->log(/**/$this_is_a_long_str);
        $panda->log(/*x#*/$online_company);
        $panda->flush();
//          echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//          echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//          echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
    }
    public static function testLexer(){
//      $str1 = '/****/$class->object->things  ,/*xxx*/$user';
//      $str2 = '$long_variable_test["keys"] /*userafdf*/, tmp';
//      $str3 = '$one, $two,$thr';
//      $str4 = '12343  ,"44544",["computer_tings"=>"words"]';
        //字符串中的注释 测试不通过
        $str5 = '\'/***/this_is_string\'';
//      $str6 = 'a';
//      $str7 = 'Class::StaticMethod()';
//      $str8 = 'Class::StaticMethod($var1)';
        //方法中的逗号 测试不通过
        $str9 = 'Class::StaticMethod($var1,$var2)';
        //new 后面的空格不应该过滤
        $str10 = '(new Class())->aMethod()';
        $str11 = '$class->callMethod()';
//      print_r((new Lexer($str1))->parseArgumentList());
//      print_r((new Lexer($str2))->parseArgumentList());
//      print_r((new Lexer($str3))->parseArgumentList());
//      print_r((new Lexer($str4))->parseArgumentList());
//      print_r((new Lexer($str5))->parseArgumentList());
//      print_r((new Lexer($str6))->parseArgumentList());
//      print_r((new Lexer($str7))->parseArgumentList());
//      print_r((new Lexer($str8))->parseArgumentList());
        print_r((new Lexer($str9))->parseArgumentList());
        print_r((new Lexer($str10))->parseArgumentList());
        print_r((new Lexer($str11))->parseArgumentList());
    }
    public static function debugTest(){
        $wholesale_order = Dporder::findOne(['id'=>3240]);
        Panda::instance()->log($wholesale_order);
        Panda::instance()->flush();
        return Panda::instance()->decode(0,10);
    }
}