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

namespace yongtiger\tree\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yongtiger\tree\TreeViewAsset;

/**
 * Class TreeView
 *
 * Example html code:
 *
 * ```
 * <ol class="sortable">
 *     <li id="list_368">
 *         <div>   <span class="disclose"><span></span></span>Driving Directions</div>
 *     </li>
 *     <li id="list_369">
 *         <div>   <span class="disclose"><span></span></span>Food Menu</div>
 *         <ol class="sortable">
 *             <li id="list_373">
 *                 <div>   <span class="disclose"><span></span></span>Meals</div>
 *             </li>
 *             <li id="list_374">
 *                 <div>   <span class="disclose"><span></span></span>Pizza & Pasta</div>
 *             </li>
 *         </ol>
 *     </li>
 * </ol>
 * ```
 *
 * The following example shows how to use TreeView:
 *
 * ```php
 * echo TreeView::widget([
 *   'nodes' => $menuItems,
 * ]);
 * ```
 *
 * @see https://github.com/ilikenwf/nestedSortable
 * @see http://jsfiddle.net/vq9dD/2/
 * @package yongtiger\tree\widgets
 */
class TreeView extends Widget
{
    /**
     * @var array the HTML attributes for the tree's container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "ol", the tag name of the node container tags. Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $htmlOptions = [];   ///[v0.0.8 (container tag and options)]

    /**
     * @var array list of nodes in the TreeView widget. Each array element represents a single
     * tree node which can be either a string or an array with the following structure:
     *
     * - name: string, required, the node name.
     * - visible: boolean, optional, whether this node is visible. Defaults to true.
     * - nodes: array|string, optional, the nodes array, or a string representing the node name.
     * - encode: boolean, optional, whether the node name will be HTML-encoded. If set, supersedes the $encodeNames option for only this node.
     */
    public $nodes = [];

    /**
     * @var array list of HTML attributes shared by all tree [[nodes]]. If any individual node
     * specifies its `options`, it will be merged with this property before being used to generate the HTML
     * attributes for the node tag. The following special options are recognized:
     *
     * - tag: string, defaults to "li", the tag name of the node container tags.
     *   Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $nodeOptions = [];   ///[v0.0.7 (node tag and options)]

    /**
     * @var string the template used to render a node name.
     * In this template, the token `{name}` will be replaced with the name of the node.
     * This property will be overridden by the `template` option set in individual nodess via [[nodes]].
     */
    public $nodeNameTemplate = '<span class="disclose"><span></span></span>{name}';

    /**
     * @var boolean whether the node names should be HTML-encoded.
     */
    public $encodeNames = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $view = $this->getView();
        TreeViewAsset::register($view);

        $view->registerJs(<<<JS
$('.sortable').nestedSortable({
    forcePlaceholderSize: true,
    handle: 'div',
    helper: 'clone',
    items: 'li',
    opacity: .6,
    placeholder: 'placeholder',
    revert: 250,
    tabSize: 25,
    tolerance: 'pointer',
    toleranceElement: '> div',
    maxLevels: 4,
    isTree: true,
    expandOnHover: 700,
    startCollapsed: false,
    change: function(){
        console.log('Relocated item');
    }
});

$('.expandEditor').attr('title','Click to show/hide item editor');
$('.disclose').attr('title','Click to show/hide children');
$('.deleteMenu').attr('title', 'Click to delete item.');

$('.disclose').on('click', function() {
    $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
    $(this).toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
});

$('.expandEditor, .itemTitle').click(function(){
    var id = $(this).attr('data-id');
    $('#menuEdit'+id).toggle();
    $(this).toggleClass('ui-icon-triangle-1-n').toggleClass('ui-icon-triangle-1-s');
});

$('.deleteMenu').click(function(){
    var id = $(this).attr('data-id');
    $('#menuItem_'+id).remove();
});

$(".collapseAll").on('click', function() {
    $('.mjs-nestedSortable-branch').addClass('mjs-nestedSortable-collapsed').removeClass('mjs-nestedSortable-expanded');
    $('.disclose-children').children().removeClass('fa-minus-circle').addClass('fa-plus-circle');
});
$(".expandAll").on('click', function() {
    $('.mjs-nestedSortable-branch').addClass('mjs-nestedSortable-expanded').removeClass('mjs-nestedSortable-collapsed');
    $('.disclose-children').children().removeClass('fa-plus-circle').addClass('fa-minus-circle');
});

JS
);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->renderNodes($this->nodes);
    }

    /**
     * Renders tree nodes.
     */
    protected function renderNodes($nodes)
    {
        $lines = [];
        if (!empty($nodes)) {

            ///[v0.0.8 (container tag and options)]
            $htmlOptions = $this->htmlOptions;
            $tag = ArrayHelper::remove($htmlOptions, 'tag', 'ol');
            if (empty($htmlOptions['class'])) {
                $htmlOptions['class'] = 'sortable';
            } else {
                $htmlOptions['class'] .= ' sortable';
            }
            $lines[] =  Html::beginTag($tag, $htmlOptions);

            foreach ($nodes as $node) {
                if (isset($node['visible']) && !$node['visible']) {
                    continue;
                }
                $lines[] = $this->renderNode($node);
            }
            $lines[] =  Html::endTag($tag); 
        }

        return implode("\n", $lines);
    }

    /**
     * Renders a tree node.
     * @param string|array $node the node to render.
     * @return string the rendering result.
     * @throws InvalidConfigException
     */
    protected function renderNode($node)
    {
        if (is_string($node)) {
            $node = ['name' => $node];
        }
        if (!isset($node['name'])) {
            throw new InvalidConfigException("The 'name' option is required.");
        }

        $lines = [];

        ///[v0.0.7 (node tag and options)]
        $nodeOptions = array_merge($this->nodeOptions, ArrayHelper::getValue($node, 'options', []));
        $tag = ArrayHelper::remove($nodeOptions, 'tag', 'li');
        $lines[] =  Html::beginTag($tag, $nodeOptions);

        $lines[] =  Html::tag('div', $this->renderNodeName($node));
        if (!empty($node['nodes'])) {
            $lines[] =  $this->renderNodes($node['nodes']);
        }

        $lines[] =  Html::endTag($tag);

        return implode("\n", $lines);
    }

    /**
     * Renders a node name.
     * @param array $node
     * @return string
     */
    protected function renderNodeName($node)
    {
        $encodeName = isset($node['encode']) ? $node['encode'] : $this->encodeNames;
        $name = $encodeName ? Html::encode($node['name']) : $node['name'];

        $template = ArrayHelper::getValue($node, 'template', $this->nodeNameTemplate);
        return strtr($template, [
            '{name}' => $name,
        ]);
    }
}