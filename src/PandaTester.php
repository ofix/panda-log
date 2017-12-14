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
use common\models\Dporder;
use common\models\UserCompany;

class PandaTester
{
    public static function debugTest(){
//        $student = new \stdClass();
//        $student->name = 'tom song';
//        $student->family = ['father'=>'me',
//            'mother'=>1];
//        $student->water = ['things'=>['user_name','user_type'],
//            'other'=>['sb','打几下']];
//        Panda::instance()->log2('$things',$student->water['things'])
//            ->log2('$water',$student);
        $wholesale_order = Dporder::findOne(['id'=>3240]);
       // $wholesale_order = Dporder::find()->where(['id'=>3240]);
        Panda::instance()->log($wholesale_order);
        Panda::instance()->flush();
        return Panda::instance()->decode(0,10);
    }
  public static function testBinaryReader(){
//      for($i=0; $i<100;$i++) {
//          self::testWriteBinary();
//          self::testWriteBinary2();
//          usleep(1000);
//      }
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
      $data = $panda->decode(0,40);
      return $data;
  }

  public static function what_can_i_do(){
      $o = new \stdClass();
      $o->is_array = ['a'=>'things','b'=>'no-problem'];
      Panda::instance()->log2('$null',null);
      Panda::instance()->log2('$true',true);
      Panda::instance()->log2('$false',false);
      Panda::instance()->flush();
  }

  public static function testWriteBinary2(){
      $integer_val = 123456;
      $double_value = 823234234.4343;
      $favorite_things = array ( 0 => array ('id' => '13', 'name' => '乒乓球'),
                      1 => array ('id' => '17', 'name' => '篮球'),
          2 => array ('id' => '17', 'name' => '篮球'),
          3 => array ('id' => '17', 'name' => '篮球'),
          4 => array ('id' => '17', 'name' => '篮球'),
          5 => array ('id' => '17', 'name' => '篮球'),
          6 => array ('id' => '17', 'name' => '篮球'),
          7 => array ('id' => '17', 'name' => '篮球'));
      $panda = Panda::instance();
      $panda->log($integer_val);
      $panda->log($favorite_things);
      $panda->log($double_value);
      $panda->flush();
//      $panda = Panda::instance();


//      echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//      echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//      echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
  }

  public static function testWriteBinary(){
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
}