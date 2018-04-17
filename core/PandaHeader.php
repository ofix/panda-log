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
 * @Time      21:57
 *
 */
namespace ofix\PandaLog\core;
class PandaHeader extends BinaryPacket
{
    public static $magic_number  = [BinaryType::UNSIGNED_LONG,19880919];
    public static $main_version  = [BinaryType::UNSIGNED_CHAR,1];
    public static $minor_version = [BinaryType::UNSIGNED_CHAR,1];
    public static $item_count = [BinaryType::UNSIGNED_LONG,0];
}