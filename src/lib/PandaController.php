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
use common\panda\PandaTester;
use common\service\Sendsms;
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
        return $this->ajaxSuccess('', '', PandaTester::testBinaryReader($date));
    }
    public function actionTest(){
        if (!Yii::$app->request->isPost) {
            $this->layout = false;
            return $this->render('/panda/panda-test');
        }
        return $this->ajaxSuccess('', '', PandaTester::debugTest());
    }
}