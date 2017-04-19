<?php ///[Yii2 tree-manager]

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

use yii\web\AssetBundle;

/**
 * Class TreeViewAsset
 */
class TreeViewAsset extends AssetBundle
{
    public $sourcePath = '@yongtiger/tree/assets';
    public $js = [
        'js/jquery.mjs.nestedSortable.js',
    ];
    public $css = [
        // 'css/tree-view-example.css',
        'css/tree-view.css',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
    ];
}