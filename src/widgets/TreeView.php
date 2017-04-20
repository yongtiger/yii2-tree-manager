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
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;
use yongtiger\tree\TreeViewAsset;

/**
 * Class TreeView
 *
 * Usages:
 *
 * ```php
 * echo \yongtiger\tree\widgets\TreeView::widget([
 *     'nodes' => $menuItems,
 *     'htmlOptions' => [  ///optional
 *         'tag' => 'ol',
 *         'class' => 'myclass',
 *     ],
 *     'nodeOptions' => [  ///optional
 *         'tag' => 'li',
 *         'class' => 'myclass',
 *     ],
 *     'scriptOptions' => [    ///optional
 *         'startCollapsed' => true,
 *     ],
 *     'scriptEventOptions' => [ ///optional
 *         'change' => "function(){ console.log('Relocated item'); }",
 *     ],
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

    ///[v0.0.13 (ADD# scriptOptions, scriptEventOptions)]
    /**
     * @var array the script options for `jquery.mjs.nestedSortable.js`. The following special options are recognized:
     *
     * - selector: string, the selector of the tree container, defaults to "ol.sortable".
     *
     */
    private $_defaultScriptOptions = [
        'isTree' => true,
        'startCollapsed' => false,
        'forcePlaceholderSize' => true,
        'handle' => 'div',
        'helper' => 'clone',
        'items' => 'li',
        'opacity' => .6,
        'placeholder' => 'placeholder',
        'revert' => 250,
        'tolerance' => 'pointer',
        'toleranceElement' => '> div',
    ];
    public $scriptOptions = [];

    /**
     * @var array additional script options that can be passed to the constructor of the treeview js object.
     */
    public $scriptEventOptions = [
        'update' => "function(){ console.log('Relocated item'); }",
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        ///[v0.0.13 (ADD# scriptOptions, scriptEventOptions)]
        $this->scriptOptions = array_merge($this->_defaultScriptOptions, $this->scriptOptions);
        foreach($this->scriptEventOptions as $key => $event)
        {
            $this->scriptOptions[$key] = new JsExpression($event);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerScript();
        $this->renderButtons();
        return $this->renderNodes($this->nodes);
    }

    /**
     * Renders buttons.
     */
    protected function renderButtons()
    {
        // echo Html::beginTag('div', ['class' => "{$this->id}-nestable-menu"]);
        echo Html::beginTag('div', ['class' => "nestable-menu"]);
        echo Html::beginTag('div', ['class' => 'btn-group']);
        echo Html::button('Add node', [
            'data-action' => 'action-create',
            'class' => 'btn btn-success'
        ]);
        echo Html::button('Collapse all', [
            'id' => 'collapse-all',
            'class' => 'btn btn-default',
            'style' => 'display: none',//////////???????
        ]);
        echo Html::button('Expand all', [
            'id' => 'expand-all',
            'class' => 'btn btn-default',
            // 'style' => 'display: none',//////////???????
        ]);
        echo Html::endTag('div');
        echo Html::endTag('div');
    }

    /**
     * Registers script.
     */
    protected function registerScript()
    {
        $view = $this->getView();
        TreeViewAsset::register($view);

        ///[v0.0.13 (ADD# scriptOptions, scriptEventOptions)]
        $selector = ArrayHelper::remove($this->scriptOptions, 'selector', 'ol.sortable');
        $view->registerJs("$('{$selector}').nestedSortable(" . Json::encode($this->scriptOptions) . ");");
    }

    /**
     * Renders tree nodes.
     * @param array $nodes
     * @return string the rendering result.
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
        $nodeOptions = array_merge(['id' => $node['id']], $this->nodeOptions, ArrayHelper::getValue($node, 'options', []));
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