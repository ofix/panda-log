<?php
/*
 * Author: code lighter
 * Date: 2017/11/29
 * Time: 21:57
 */

namespace common\panda;
class PandaHeader extends BinaryPacket
{
    public static $magic_number  = [BinaryType::UNSIGNED_LONG,19880919];
    public static $main_version  = [BinaryType::UNSIGNED_CHAR,1];
    public static $minor_version = [BinaryType::UNSIGNED_CHAR,1];
    public static $item_count = [BinaryType::UNSIGNED_LONG,0];
}