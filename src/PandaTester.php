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
 * @Time      22:20
 *
 */
namespace common\panda;
use common\models\UserCompany;

class PandaTester
{
  public static function testBinaryReader(){
//      for($i=0; $i<100;$i++) {
//          self::testWriteBinary();
//          self::testWriteBinary2();
//          usleep(1000);
//      }
       self::testWriteBinary();
       self::testWriteBinary2();
       return self::testReadBinary();

//      print_r(Lexer::splitUtf8Str('阿士大、x\00/*asdf//夫撒地方'));
//      print_r(Lexer::splitUtf8Str(''));
//      print_r(Lexer::splitUtf8Str('‘’341324ca凸(艹皿艹 )1324'));
//      self::testLexer();
//      die();


//      $now['time'] = 1512403200;
//      $time1 =self::ti($now);
//      $time2 = self::ti($now);
//      echo "time1 = ".$time1.'time2 ='.$time2;
//      die();

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
  public static function ti(&$now){
      $time1 = date('Y-m-d',$now['time']);
      return $time1;
  }

  public static function testReadBinary(){
      $panda = Panda::instance();
      $data = $panda->decode(0,22);
      return $data;
  }

  public static function testWriteBinary2(){
      $o = '123456';
      $json = array ( 0 => array ('id' => '13', 'name' => '乒乓球'),
                      1 => array ('id' => '17', 'name' => '篮球'));
      $panda = Panda::instance();
      $panda->log($json);
      $panda->log($o);
//      $panda = Panda::instance();
//      $integer = 12343;
//      $panda->log($integer);
      $panda->flush();
//      echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//      echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//      echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
  }

  public static function testWriteBinary(){
          $array = ['天下是我的', '你是傻瓜吗？'];
          $distributor_order = new \stdClass();
      $distributor_order->name = "宋华彪";
      $distributor_order->job = "advanced PHP programmer";
      $distributor_order->age = 30;
          $this_is_a_long_str = 'This is not a good Thing';
          $get_company = UserCompany::find()->where(['user_id' => 10, 'company_id' => 1])
            ->andWhere(['somethin_other'=>1])
            ->andWhere(['in','query_condition',[1,100,20]])
            ->innerJoin(['user','user.id=user_company.user_id']);
          $panda = Panda::instance();
          $panda->log(  $array/*xxxx*/);
          $panda->log( /***/ $distributor_order /**/);
          $panda->log(/**/$this_is_a_long_str);
          $panda->log(/*x#*/$get_company);
          $panda->flush();
//          echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//          echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//          echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
  }
}