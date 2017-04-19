# yii2-tree-manager v0.0.13 (ADD# scriptOptions, scriptEventOptions)

[![Latest Stable Version](https://poser.pugx.org/yongtiger/yii2-tree-manager/v/stable)](https://packagist.org/packages/yongtiger/yii2-tree-manager)
[![Total Downloads](https://poser.pugx.org/yongtiger/yii2-tree-manager/downloads)](https://packagist.org/packages/yongtiger/yii2-tree-manager) 
[![Latest Unstable Version](https://poser.pugx.org/yongtiger/yii2-tree-manager/v/unstable)](https://packagist.org/packages/yongtiger/yii2-tree-manager)
[![License](https://poser.pugx.org/yongtiger/yii2-tree-manager/license)](https://packagist.org/packages/yongtiger/yii2-tree-manager)

## FEATURES

* Sample of extensions directory structure. `src`, `docs`, etc.
* `README.md`
* `composer.json`
* `development-roadmap.md`


## DEPENDENCES


## INSTALLATION   

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yongtiger/yii2-tree-manager "*"
```

or add

```json
"yongtiger/yii2-tree-manager": "*"
```

to the require section of your composer.json.


## CONFIGURATION


## USAGES

```php
echo \yongtiger\tree\widgets\TreeView::widget([
    'nodes' => $menuItems,
    'htmlOptions' => [  ///optional
        'tag' => 'ol',
        'class' => 'myclass',
    ],
    'nodeOptions' => [  ///optional
        'tag' => 'li',
        'class' => 'myclass',
    ],
    'scriptOptions' => [    ///optional
        'startCollapsed' => true,
    ],
    'scriptEventOptions' => [ ///optional
        'change' => "function(){ console.log('Relocated item'); }",
    ],
]);
```


## NOTES


## DOCUMENTS


## REFERENCES

### Example of `nestedSortable` html code:

```
<ol class="sortable">
    <li id="list_368">
        <div>   <span class="disclose"><span></span></span>Driving Directions</div>
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
        </ol>
    </li>
</ol>
```


### Config of `jquery.mjs.nestedSortable.js`:

```
///jquery-ui version 1.11.4 options
// appendTo: "parent",
// axis: false,
// connectWith: false,
// containment: false,
// cursor: "auto",
// cursorAt: false,
// dropOnEmpty: true,
forcePlaceholderSize: true,                 ///defaults to false 
// forceHelperSize: false,
// grid: false,
handle: 'div',                              ///defaults to false
helper: 'clone',                            ///defaults to "original"
items: 'li',                                ///defaults to "> *"
opacity: .6,                                ///defaults to false 
placeholder: 'placeholder',                 ///defaults to false 
revert: 250,                                ///defaults to false 
// scroll: true,
// scrollSensitivity: 20,
// scrollSpeed: 20,
// scope: "default",
tolerance: 'pointer',                       ///defaults to "intersect" 
toleranceElement: '> div',                  ///defaults to null 
// zIndex: 1000,

///jquery.mjs.nestedSortable.js v 2.0b1 options
// disableParentChange: false,
// doNotClear: false,
// expandOnHover: 700,
// isAllowed: function() { return true; },
isTree: true,                               ///defaults to false
// listType: "ol",
// maxLevels: 0,
// protectRoot: false,
// rootID: null,
// rtl: false,
// startCollapsed: false,
// tabSize: 20,
// branchClass: "mjs-nestedSortable-branch",
// collapsedClass: "mjs-nestedSortable-collapsed",
// disableNestingClass: "mjs-nestedSortable-no-nesting",
// errorClass: "mjs-nestedSortable-error",
// expandedClass: "mjs-nestedSortable-expanded",
// hoveringClass: "mjs-nestedSortable-hovering",
// leafClass: "mjs-nestedSortable-leaf",
// disabledClass: "mjs-nestedSortable-disabled",
```


## SEE ALSO

- https://github.com/ilikenwf/nestedSortable
- http://jsfiddle.net/vq9dD/2/


## TBD


## [Development roadmap](docs/development-roadmap.md)


## LICENSE 
**yii2-tree-manager** is released under the MIT license, see [LICENSE](https://opensource.org/licenses/MIT) file for details.
