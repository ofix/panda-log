<?php
/*
 * binary file reader and writer.
 * User: code lighter
 * Date: 2017/11/29 0029
 * Time: 下午 17:23
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