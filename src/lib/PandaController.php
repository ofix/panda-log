<?php
/*
 * This file is part of panda-log.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    code lighter
 * @copyright code lighter
 * @qq        981326632
 * @wechat    981326632
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @Date: 2017/12/6
 * @Time: 0:42
 */
namespace company\controllers;
use common\controllers\BaseController;
use common\panda\PandaReader;
use common\panda\PandaWriter;
use Yii;

class PandaController extends BaseController
{
    public $enableCsrfValidation = false;
    public function actionIndex()
    {
        if (!Yii::$app->request->isPost) {
            $this->layout = false;
            return $this->render('/panda/panda-view');
        }
        $date = Yii::$app->request->post('date');
        $page_offset = Yii::$app->request->post('page_offset',0);
        $page_size = Yii::$app->request->post('page_size',10);
        $asc = Yii::$app->request->post('asc',0);
        $_asc = false;
        if($asc == 1){
            $_asc = true;
        }
        return $this->ajaxSuccess('', '', PandaReader::query($date,$page_offset,$page_size,$_asc));
    }
    public function actionTest(){
        if (!Yii::$app->request->isPost) {
            $this->layout = false;
            return $this->render('/panda/panda-test');
        }
        return $this->ajaxSuccess('', '', PandaWriter::debugTest());
    }
}