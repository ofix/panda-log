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
//          usleep(100000);
//      }
       self::testReadBinary();
        die();
  }

  public static function testReadBinary(){
      $panda = Panda::instance();
      $page_offset = rand(0,1000);
      $page_size = rand(2,50);
      $data = $panda->decode($page_offset,$page_size,true);
      echo "page_offset = ".$page_offset.PHP_EOL;
      echo "page_size = ".$page_size.PHP_EOL;
      echo json_encode($data,JSON_UNESCAPED_UNICODE);
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
      for($i=0; $i<1; $i++) {
          $array = ['天下是我的', '你是傻瓜吗？'];
          $o = new \stdClass();
          $o->name = "宋华彪";
          $o->job = "advanced PHP programmer";
          $o->age = 30;
          $str = 'This is not a good Thing';
          $sql = UserCompany::find()->where(['user_id' => 10, 'company_id' => 1]);
          $panda = Panda::instance();
          $panda->log($array);
          $panda->log($o);
          $panda->log($str);
          $panda->log($sql);;
          $panda->flush();
//          echo '写入文件起始地址:0x'.dechex($panda->getFlushBegin()).'H'.PHP_EOL;
//          echo '写入文件结束地址:0x'.dechex($panda->getFlushEnd()).'H'.PHP_EOL;
//          echo '写入文件字节数量:'.dechex($panda->getFlushBytes()).PHP_EOL;
      }
  }
}