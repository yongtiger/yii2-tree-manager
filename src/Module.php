<?php ///[yii2-tree-manager]

/**
 * Yii2 tree manager
 *
 * @link        http://www.brainbook.cc
 * @see         https://github.com/yongtiger/yii2-tree-manager
 * @author      Tiger Yong <tigeryang.brainbook@outlook.com>
 * @copyright   Copyright (c) 2017 BrainBook.CC
 * @license     http://opensource.org/licenses/MIT
 */

namespace yongtiger\tree;

use Yii;

/**
 * The tree management module for Yii Framework 2.0.
 *
 * @package yongtiger\tree
 */
class Module extends \yii\base\Module
{
    /**
     * @var string module name
     */
    public static $moduleName = 'treemanager';

    /**
     * @return static
     */
    public static function instance()
    {
        return Yii::$app->getModule(static::$moduleName);
    }

    /**
     * Registers the translation files.
     */
    public static function registerTranslations()
    {
        ///[i18n]
        ///if no setup the component i18n, use setup in this module.
        if (!isset(Yii::$app->i18n->translations['extensions/yongtiger/yii2-tree-manager/*']) && !isset(Yii::$app->i18n->translations['extensions/yongtiger/yii2-tree-manager'])) {
            Yii::$app->i18n->translations['extensions/yongtiger/yii2-tree-manager/*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@vendor/yongtiger/yii2-tree-manager/src/messages',
                'fileMap' => [
                    'extensions/yongtiger/yii2-tree-manager/message' => 'message.php',  ///category in Module::t() is message
                ],
            ];
        }
    }

    /**
     * Translates a message. This is just a wrapper of Yii::t().
     *
     * @see http://www.yiiframework.com/doc-2.0/yii-baseyii.html#t()-detail
     *
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        static::registerTranslations();
        return Yii::t('extensions/yongtiger/yii2-tree-manager/' . $category, $message, $params, $language);
    }
}
