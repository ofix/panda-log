<?php
namespace ofix\PandaLog;

use yii\web\AssetBundle;

/**
 * This declares the asset files required by Panda-log.
 *
 * @author ofix/code lighter <shb8845369@gmail.com>
 * @since 1.0
 */
class PandaAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ofix/panda-log/assets';
    public $css = [
        'date.css',
        'date-font.css',
        'panda-log.css',
        'highlight/styles/monokai.css',
    ];
    public $js = [
        'date.js',
        'clipboard.min.js',
        'jquery.class.js',
        'highlight/highlight.pack.js',
        'panda-log.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
