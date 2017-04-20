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
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;
use yongtiger\tree\TreeViewAsset;
use rmrevin\yii\fontawesome\FA;

/**
 * Class TreeView
 *
 * Usages:
 *
 * ```php
 * echo \yongtiger\tree\widgets\TreeView::widget([
 *     'nodes' => $menuItems,
 *     'options' => [  ///optional
 *         'tag' => 'div',
 *         'class' => 'myclass',
 *     ],
 *     'nodesOptions' => [  ///optional
 *         'tag' => 'ol',
 *         'class' => 'myclass',
 *     ],
 *     'nodeOptions' => [  ///optional
 *         'tag' => 'li',
 *         'class' => 'myclass',
 *     ],
 *     'clientOptions' => [    ///optional
 *         'startCollapsed' => true,
 *     ],
 *     'clientEventOptions' => [ ///optional
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
     * - tag: string, defaults to "div", the tag name of the node container tags. Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @var array the client script options for `jquery.mjs.nestedSortable.js`. The following special options are recognized:
     *
     * - selector: string, the selector of the tree container, defaults to "ol.sortable".
     *
     */
    public $clientOptions = [];

    /**
     * @var array additional client options that can be passed to the constructor of the treeview js object.
     */
    public $clientEventOptions = [
        'update' => "function(){ console.log('Relocated item'); }",
    ];

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
     * @var array the HTML attributes for the tree's nodes tag. The following special options are recognized:
     *
     * - tag: string, defaults to "ol", the tag name of the node container tags. Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     * - class: string, defaults to "sortable", the class name of the nodes tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $nodesOptions = ['tag' => 'ol', 'class' => 'sortable'];

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
    public $nodeOptions = [];

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
     * @var array the default client script options for `jquery.mjs.nestedSortable.js`.
     */
    private $_defaultClientOptions = [
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->clientOptions = array_merge($this->_defaultClientOptions, $this->clientOptions);
        foreach($this->clientEventOptions as $key => $event)
        {
            $this->clientOptions[$key] = new JsExpression($event);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClient();
        return $this->renderTree();
    }

    /**
     * Registers client script.
     */
    protected function registerClient()
    {
        $view = $this->getView();
        TreeViewAsset::register($view);

        $selector = ArrayHelper::remove($this->clientOptions, 'selector', 'ol.sortable');
        $view->registerJs("$('{$selector}').nestedSortable(" . Json::htmlEncode($this->clientOptions) . ");");
    }

    /**
     * Renders the tree.
     */
    protected function renderTree()
    {
        $lines = [];

        $tag = ArrayHelper::remove($this->options, 'tag', 'div');
        $lines[] = Html::beginTag($tag, $this->options);

        $lines[] = $this->renderToolbar();
        $lines[] = $this->renderNodes($this->nodes);

        $lines[] = Html::endTag($tag);

        return implode("\n", $lines);
    }

    /**
     * Renders the toolbar.
     */
    protected function renderToolbar()
    {
        $lines = [];

        $lines[] = Html::beginTag('div', ['class' => 'btn-group']);

        $lines[] = Html::button('Add node', [
            'data-action' => 'action-add-node',
            'class' => 'btn btn-success'
        ]);
        $lines[] = Html::button('Collapse all', [
            'id' => 'collapse-all',
            'class' => 'btn btn-default',
            'style' => $this->clientOptions['startCollapsed'] ? 'display: none' : 'display: block',
        ]);
        $lines[] = Html::button('Expand all', [
            'id' => 'expand-all',
            'class' => 'btn btn-default',
            'style' => $this->clientOptions['startCollapsed'] ? 'display: block' : 'display: none',
        ]);

        $lines[] = Html::endTag('div');

        return implode("\n", $lines);
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

            $tag = ArrayHelper::remove($this->nodesOptions, 'tag', 'ol');
            $lines[] =  Html::beginTag($tag, $this->nodesOptions);

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

        $nodeOptions = array_merge([
            'data-id' => $node['id'],
            'data-update-url' => Url::to(['update', 'id' => $node['id']]),
        ], $this->nodeOptions, ArrayHelper::getValue($node, 'options', []));
        $tag = ArrayHelper::remove($nodeOptions, 'tag', 'li');
        $lines[] = Html::beginTag($tag, $nodeOptions);

        $lines[] = Html::beginTag('div');

        $lines[] = $this->renderNodeName($node);

        $lines[] = Html::beginTag('span', ['class' => 'pull-right']);
        $lines[] = Html::a(FA::i(FA::_PENCIL), ['update', 'id' => $node['id']], ['class' => 'btn btn-xs btn-primary']);
        $lines[] = Html::a(FA::i(FA::_PLUS), ['create', 'parent_id' => $node['id']], ['class' => 'btn btn-xs btn-success']);
        $lines[] = Html::a(FA::i(FA::_TRASH), ['delete', 'id' => $node['id']], [
            'data' => ['confirm' => Yii::t('app', 'Are you sure you want to delete this item?'), 'method' => 'post',],
            'class' => 'btn btn-xs btn-danger'
        ]);
        $lines[] =  Html::endTag('span');

        $lines[] =  Html::endTag('div');

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