<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ofix\panda;

use Yii;
use yii\base\BootstrapInterface;

/**
 * This is the main module class for the panda-log module.
 *
 * To use panda-log, include it as a module in the application configuration like the following:
 *
 * ~~~
 * return [
 *     'bootstrap' => ['panda-log'],
 *     'modules' => [
 *         'panda-log' => ['class' => 'ofix\panda\Module::Class'],
 *     ],
 * ]
 * ~~~
 *
 * Because panda-log generates log files on the server, you should only use it on your own
 * development machine. To prevent other people from using this module, by default, panda-log
 * can only be accessed by localhost. You may configure its [[allowedIPs]] property if
 * you want to make it accessible on other machines.
 *
 * With the above configuration, you will be able to access GiiModule in your browser using
 * the URL `http://localhost/path/to/index.php?r=gii`
 *
 * If your application enables [[\yii\web\UrlManager::enablePrettyUrl|pretty URLs]],
 * you can then access Gii via URL: `http://localhost/path/to/index.php/gii`
 *
 * @author code lighter <981326632@qq.com>
 * @since 2.0
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'ofix\panda\controllers';
    public $log_dir = '';

    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                ['class' => 'yii\web\UrlRule', 'pattern' => $this->id, 'route' => $this->id . '/panda/index'],
                ['class' => 'yii\web\UrlRule', 'pattern' => $this->id . '/<id:\w+>', 'route' => $this->id . '/panda/view']
            ], false);
        }
    }

    public function init(){
        Panda::instance()->setDefaultSaveDir($this->log_dir);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->resetGlobalSettings();
        return true;
    }

    /**
     * Resets potentially incompatible global settings done in app config.
     */
    protected function resetGlobalSettings()
    {
        if (Yii::$app instanceof \yii\web\Application) {
            Yii::$app->assetManager->bundles = [];
        }
    }
}
