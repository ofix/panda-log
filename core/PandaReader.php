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
namespace ofix\PandaLog\core;

use ofix\PandaLog\Log;

class PandaReader
{
  public static function query($date, $page_offset, $page_size,$asc){
      $panda = Log::instance();
      $data = $panda->decode($page_offset,$page_size,$asc,$date);
      return $data;
  }
}