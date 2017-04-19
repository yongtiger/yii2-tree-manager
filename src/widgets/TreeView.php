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
$('#tree-{$this->getId()}').nestedSortable({
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
<ol id="tree-{$this->getId()}" class="sortable  ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded">
   <li style="display: list-item;" class="mjs-nestedSortable-branch mjs-nestedSortable-expanded" id="menuItem_2" data-foo="bar">
   <div class="menuDiv">
       <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
       <span></span>
       </span>
       <span title="Click to show/hide item editor" data-id="2" class="expandEditor ui-icon ui-icon-triangle-1-n">
       <span></span>
       </span>
       <span>
       <span data-id="2" class="itemTitle">a</span>
       <span title="Click to delete item." data-id="2" class="deleteMenu ui-icon ui-icon-closethick">
       <span></span>
       </span>
       </span>
       <div id="menuEdit2" class="menuEdit">
           <p>
               Content or form, or nothing here. Whatever you want.
           </p>
       </div>
   </div>
   <ol>
       <li style="display: list-item;" class="mjs-nestedSortable-branch mjs-nestedSortable-expanded" id="menuItem_4" data-foo="baz">
       <div class="menuDiv">
           <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
           <span></span>
           </span>
           <span title="Click to show/hide item editor" data-id="4" class="expandEditor ui-icon ui-icon-triangle-1-n">
           <span></span>
           </span>
           <span>
           <span data-id="4" class="itemTitle">c</span>
           <span title="Click to delete item." data-id="4" class="deleteMenu ui-icon ui-icon-closethick">
           <span></span>
           </span>
           </span>
           <div id="menuEdit4" class="menuEdit">
               <p>
                   Content or form, or nothing here. Whatever you want.
               </p>
           </div>
       </div>
       <ol>
           <li class="mjs-nestedSortable-leaf" id="menuItem_6">
           <div class="menuDiv">
               <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
               <span></span>
               </span>
               <span title="Click to show/hide item editor" data-id="6" class="expandEditor ui-icon ui-icon-triangle-1-n">
               <span></span>
               </span>
               <span>
               <span data-id="6" class="itemTitle">e</span>
               <span title="Click to delete item." data-id="6" class="deleteMenu ui-icon ui-icon-closethick">
               <span></span>
               </span>
               </span>
               <div id="menuEdit6" class="menuEdit">
                   <p>
                       Content or form, or nothing here. Whatever you want.
                   </p>
               </div>
           </div>
           </li>
       </ol>
       </li>
       <li style="display: list-item;" class="mjs-nestedSortable-leaf" id="menuItem_5">
       <div class="menuDiv">
           <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
           <span></span>
           </span>
           <span title="Click to show/hide item editor" data-id="5" class="expandEditor ui-icon ui-icon-triangle-1-n">
           <span></span>
           </span>
           <span>
           <span data-id="5" class="itemTitle">d</span>
           <span title="Click to delete item." data-id="5" class="deleteMenu ui-icon ui-icon-closethick">
           <span></span>
           </span>
           </span>
           <div id="menuEdit5" class="menuEdit">
               <p>
                   Content or form, or nothing here. Whatever you want.
               </p>
           </div>
       </div>
       </li>
   </ol>
   </li>
   <ol>
   </ol>
   <li style="display: list-item;" class="mjs-nestedSortable-leaf" id="menuItem_7">
   <div class="menuDiv">
       <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
       <span></span>
       </span>
       <span title="Click to show/hide item editor" data-id="7" class="expandEditor ui-icon ui-icon-triangle-1-n">
       <span></span>
       </span>
       <span>
       <span data-id="7" class="itemTitle">f</span>
       <span title="Click to delete item." data-id="7" class="deleteMenu ui-icon ui-icon-closethick">
       <span></span>
       </span>
       </span>
       <div id="menuEdit7" class="menuEdit">
           <p>
              Content or form, or nothing here. Whatever you want.
           </p>
       </div>
   </div>
   </li>
   <li class="mjs-nestedSortable-leaf" id="menuItem_3">
   <div class="menuDiv">
       <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
       <span></span>
       </span>
       <span title="Click to show/hide item editor" data-id="3" class="expandEditor ui-icon ui-icon-triangle-1-n">
       <span></span>
       </span>
       <span>
       <span data-id="3" class="itemTitle">b</span>
       <span title="Click to delete item." data-id="3" class="deleteMenu ui-icon ui-icon-closethick">
       <span></span>
       </span>
       </span>
       <div id="menuEdit3" class="menuEdit">
           <p>
               Content or form, or nothing here. Whatever you want.
           </p>
       </div>
   </div>
   </li>
</ol>
HTML;
    }
}