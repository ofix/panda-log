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
      return self::testReadBinary();
  }

  public static function testReadBinary(){
      $panda = Panda::instance();
      $data = $panda->decode(0,14,true);
      return $data;
  }

  public static function testWriteBinary2(){
      $o = '123456';
      $json = array ( 0 => array ('id' => '13', 'name' => '乒乓球'),
                      1 => array ('id' => '17', 'name' => '篮球'));
      $panda = Panda::instance();
      $panda->log($json);
      $panda->log($o);
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