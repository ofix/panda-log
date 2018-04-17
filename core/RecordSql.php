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
 * @Date      2017/12/2
 * @Time      14:23
 */
namespace ofix\panda\core;

use yii\db\ActiveRecord;
use yii\db\Query;
use Yii;

class RecordSql extends Record
{
    public function __construct()
    {
        parent::__construct();
        $this->type = self::RECORD_TYPE_SQL;
    }
    public function log($sql){
        if($sql instanceof ActiveRecord){
            $this->type = self::RECORD_TYPE_OBJECT;
            $this->data = json_encode($sql->attributes);
        }else if($sql instanceof Query){
            $this->data = $this->removeLineFeed($sql->createCommand()->getRawSql());
        }
    }
    public function read(BinaryStream $stream,$byte_count){
        $this->data = $stream->readStringClean($byte_count);
    }
}