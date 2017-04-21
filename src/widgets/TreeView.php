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

/**
 * Class TreeView
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
     * - tag: string, defaults to "div", the tag name of the node container tags. 
     *   Set to false to disable container tag.
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
     * @var array additional client event options that can be passed to the constructor of the treeview js object.
     */
    public $clientEventOptions = [
        'update' => "function(){ console.log('Relocated item'); }",
    ];

    /**
     * @var array list of nodes in the [TreeView] widget. Each array element represents a single
     * tree node which can be either a string or an array with the following structure:
     *
     * - name: string, required, the node name.
     * - visible: boolean, optional, whether this node is visible. Defaults to true.
     * - nodes: array|string, optional, the nodes array, or a string representing the node name.
     * - encode: boolean, optional, whether the node name will be HTML-encoded. If set, supersedes the $encodeNodeNames option for only this node.
     */
    public $nodes = [];

    /**
     * @var array the HTML attributes for the tree's [nodes] tag. The following special options are recognized:
     *
     * - tag: string, defaults to "ol", the tag name of the [nodes] tags. 
     *   Set to false to disable [nodes] tag.
     *   See also [[\yii\helpers\Html::tag()]].
     * - class: string, defaults to "sortable", the class name of the [nodes] tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $nodesOptions = ['tag' => 'ol', 'class' => 'sortable'];

    /**
     * @var array list of HTML attributes shared by all tree nodes. If any individual node
     * specifies its `options`, it will be merged with this property before being used to generate the HTML
     * attributes for the [node] tag. The following special options are recognized:
     *
     * - tag: string, defaults to "li", the tag name of the [node] tags.
     *   Set to false to disable [node] tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $nodeOptions = [];

    /**
     * @var array the HTML attributes for the tree's node [actions] tag. The following special options are recognized:
     *
     * - tag: string, defaults to "span", the tag name of the node [actions] tags. 
     *   Set to false to disable [actions] tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $nodeActionsOptions = ['class' => 'pull-right'];

    /**
     * @var array the HTML attributes for the tree's node [actions] tag. The following special options are recognized:
     *
     * - tag: string, defaults to "span", the tag name of the node [actions] tags. 
     *   Set to false to disable [actions] tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     *
     * Note: `nodeActionOptions` will be orverriden by the individual `actionOptions` of `nodeActions`.
     */
    public $nodeActionOptions = [
        'tag' => 'a',
        'href' => '{action-url}',   ///it will be replaced with the URL created using [[createUrl()]]
    ];

    /**
     * @var array the node actions.
     */
    public $nodeActions = [
        'view' => [
            'actionText' => '<span class="glyphicon glyphicon-eye-open"></span>',
            'actionOptions' => [
                'class' => 'btn btn-xs btn-default',
            ],
        ],
        'update' => [
            'actionText' => '<span class="glyphicon glyphicon-pencil"></span>',
            'actionOptions' => [
                'class' => 'btn btn-xs btn-primary',
            ],
        ],
        'create' => [
            'actionText' => '<span class="glyphicon glyphicon-plus"></span>',
            'actionOptions' => [
                'class' => 'btn btn-xs btn-success',
            ],
        ],
        'delete' => [
            'actionText' => '<span class="glyphicon glyphicon-trash"></span>',
            'actionOptions' => [
                'class' => 'btn btn-xs btn-primary',
                'data-confirm' => 'Are you sure you want to delete this item?', ///???i18n
                'data-method' => 'post',
                ///for ajax
                'href' => 'javascript:void(0)', ///Note: It will override the 'href' of `nodeActionOptions`
                'data-action-url' => '{action-url}',   ///it will be replaced with the URL created using [[createUrl()]]
            ],
        ],
    ];

    /**
     * @var string the ID of the controller that should handle the actions specified here.
     * If not set, it will use the currently active controller. This property is mainly used by
     * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
     * to each action name to form the route of the action.
     */
    public $controller;

    /**
     * @var callable a callback that creates a button URL.
     * The signature of the callback should be the same as that of [[createUrl()]].
     *
     * ```php
     * function (string $action, integer $id, TreeView $this) {
     *     //return string;
     * }
     * ```
     *
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $urlCreator;

    /**
     * @var string the template used to render a node name.
     * In this template, the token `{name}` will be replaced with the name of the node.
     * This property will be overridden by the `template` option set in individual nodess via [[nodes]].
     */
    public $nodeNameTemplate = '<span class="disclose"><span></span></span>{name}';

    /**
     * @var boolean whether the node names should be HTML-encoded.
     */
    public $encodeNodeNames = true;

    /**
     * @var array the default client script options for `jquery.mjs.nestedSortable.js`.
     * @see https://github.com/ilikenwf/nestedSortable/blob/master/jquery.mjs.nestedSortable.js#L35
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
     * @return string the rendering result.
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
     * @return string the rendering result.
     */
    protected function renderToolbar()
    {
        $lines = [];

        $lines[] = Html::beginTag('div', ['class' => 'btn-group']);

        $lines[] = Html::button('Create node', [
            'data-action-name' => 'create',
            'class' => 'btn btn-success',
            'onclick' => '{location.href="' . $this->createUrl('create') . '"}',
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

            $nodesOptions = $this->nodesOptions;
            $tag = ArrayHelper::remove($nodesOptions, 'tag', 'ol');
            $lines[] =  Html::beginTag($tag, $nodesOptions);

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
            'data-node-id' => $node['id'],
            'data-node-name' => Html::encode($node['name']),
            // more node data ...
        ], $this->nodeOptions, ArrayHelper::getValue($node, 'options', []));
        $tag = ArrayHelper::remove($nodeOptions, 'tag', 'li');

        $lines[] = Html::beginTag($tag, $nodeOptions);

        $lines[] = Html::beginTag('div');

        $lines[] = $this->renderNodeName($node);
        $lines[] = $this->renderNodeActions($node);

        $lines[] = Html::endTag('div');

        if (!empty($node['nodes'])) {
            $lines[] =  $this->renderNodes($node['nodes']);
        }

        $lines[] =  Html::endTag($tag);

        return implode("\n", $lines);
    }

    /**
     * Renders a node name.
     * @param array $node
     * @return string the rendering result.
     */
    protected function renderNodeName($node)
    {
        $encodeName = isset($node['encode']) ? $node['encode'] : $this->encodeNodeNames;
        $name = $encodeName ? Html::encode($node['name']) : $node['name'];

        $template = ArrayHelper::getValue($node, 'template', $this->nodeNameTemplate);
        return strtr($template, [
            '{name}' => $name,
        ]);
    }

    /**
     * Renders node actions.
     * @param array $node
     * @return string the rendering result.
     */
    protected function renderNodeActions($node)
    {
        $lines = [];

        $nodeActionsOptions = $this->nodeActionsOptions;
        $tag = ArrayHelper::remove($nodeActionsOptions, 'tag', 'span');
        $lines[] = Html::beginTag($tag, $nodeActionsOptions);

        foreach ($this->nodeActions as $actionName => $actionValue) {
            $lines[] = $this->renderNodeAction($node, $actionName, $actionValue);
        }

        $lines[] =  Html::endTag($tag);

        return implode("\n", $lines);
    }

    /**
     * Renders a node action.
     * @param array $node
     * @param string $actionName
     * @param array $actionValue Array of a node action.
     * @return string the rendering result.
     */
    protected function renderNodeAction($node, $actionName, $actionValue)
    {
        $lines = [];

        $nodeActionOptions = $this->nodeActionOptions;
        $tag = ArrayHelper::remove($nodeActionOptions, 'tag', 'a');

        $title = Yii::t('yii', ucfirst($actionName));
        $options = array_merge([
            'title' => $title,
            'aria-label' => $title,
            'data-node-id' => $node['id'],
            'data-action-name' => "{$actionName}",
        ], $nodeActionOptions, $actionValue['actionOptions']);

        foreach ($options as $key => $option) {
            $options[$key] = strtr($option, [
                '{action-url}' => Url::to(ArrayHelper::getValue($node, $actionName . '-url', $this->createUrl($actionName, $node))),
            ]);
        }

        $lines[] = Html::tag($tag, $actionValue['actionText'], $options);

        return implode("\n", $lines);
    }

    /**
     * Creates a URL for the given action and node id.
     * @see http://www.yiiframework.com/doc-2.0/yii-grid-actioncolumn.html
     * @param string $actionName the action name (or action ID).
     * @param array $node will be null while creating node (root node) from toolbar.
     * @return string the created URL.
     */
    protected function createUrl($actionName, $node = null)
    {
        if (isset($node) && is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $node, $actionName, $this);
        } else {
            $params = isset($node) ? ['id' => (string) $node['id']] : [];
            $params[0] = $this->controller ? $this->controller . '/' . $actionName : $actionName;

            return Url::toRoute($params);
        }
    }
}