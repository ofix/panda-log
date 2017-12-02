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
 * @Time      17:23
 *
 * @desc binaryItem is the minimum data for packet.
 */
namespace common\panda;
class BinaryItem{
    public $data;
    public $type;
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }
}