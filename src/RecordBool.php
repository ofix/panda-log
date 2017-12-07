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
 * @Date      2017/12/4
 * @Time      13:44
 */

namespace common\panda;


class RecordBool extends Record
{
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_BOOL;
    }
    public function log($bool){
        $this->data = json_encode(['b'=>$bool?1:0]);
    }
    public function getData(){
        return ($this->data)->b;
    }
    public function read(BinaryStream $stream,$byte_count){
        $data = $stream->readStringClean($byte_count);
        $this->data = json_decode($data);
    }
}