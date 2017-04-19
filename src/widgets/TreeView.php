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

use yii;
use yii\base\Widget;
use yii\web\View;
use yongtiger\tree\TreeViewAsset;

/**
 * Class TreeView
 */
class TreeView extends Widget
{
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
        return <<<HTML
<ol class="sortable">
    <li id="list_368">
        <div>   <span class="disclose"><span></span></span>Driving Directions</div>
    </li>
    <li id="list_367">
        <div>   <span class="disclose"><span></span></span>Make a Reservation</div>
    </li>
    <li id="list_369">
        <div>   <span class="disclose"><span></span></span>Food Menu</div>
        <ol class="sortable">
            <li id="list_373">
                <div>   <span class="disclose"><span></span></span>Meals</div>
            </li>
            <li id="list_374">
                <div>   <span class="disclose"><span></span></span>Pizza & Pasta</div>
            </li>
            <li id="list_375">
                <div>   <span class="disclose"><span></span></span>Soup</div>
            </li>
        </ol>
    </li>
    <li id="list_371">
        <div>   <span class="disclose"><span></span></span>Drink Menu</div>
    </li>
    <li id="list_370">
        <div>   <span class="disclose"><span></span></span>Gift Cards</div>
    </li>
    <li id="list_372">
        <div>   <span class="disclose"><span></span></span>About Us</div>
    </li>
</ol>
HTML;
    }
}