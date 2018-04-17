<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ofix\PandaLog\controllers;

use ofix\PandaLog\core\PandaReader;
use yii\web\Controller;
use Yii;
use yii\web\Response;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PandaController extends Controller
{
    public $module;

    public $logFilePath; //文件保存路径
    public $pandaViewUrl; //查看日志文件路径

    public function init()
    {
        $this->layout = 'panda-layout';
        $this->pandaViewUrl = $this->module->id . '/panda/view';
    }

    public function actionIndex()
    {
        return $this->render('panda-view');
    }
    public function actionView()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $date = Yii::$app->request->post('date');
        $page_offset = Yii::$app->request->post('page_offset',0);
        $page_size = Yii::$app->request->post('page_size',100);
        $asc = Yii::$app->request->post('asc',0);
        $_asc = false;
        if($asc == 1){
            $_asc = true;
        }
        return ["error_code"=>0,"error_msg"=>'请求成功',"data"=>PandaReader::query($date,$page_offset,$page_size,$_asc)];
    }
}
