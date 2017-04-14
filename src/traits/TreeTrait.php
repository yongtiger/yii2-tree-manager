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

namespace yongtiger\tree\traits;

use Yii;

/**
 * Trait that must be used by the Tree model
 */
trait TreeTrait
{
    /**
     * @see https://github.com/voskobovich/yii2-tree-manager/blob/master/src/widgets/nestable/Nestable.php#L138
     * @param ActiveRecord|TreeInterface $node
     * @return array
     */
    public static function getMenuItems($node = null)
    {
        if (!isset($node)) {
            $rootNodes = static::find()->roots()->all();
            if (!empty($rootNodes[0])) {
                $node = $rootNodes[0];
            } else {
                return [];
            }
        }

        $items = [];
        /** @var ActiveRecord[]|TreeInterface[] $children */
        foreach ($node->children as $n => $node) {
            $items[$n]['id'] = $node->getPrimaryKey();
            $items[$n]['items'] = static::getMenuItems($node);
            $items[$n]['label'] = $node->name;
            $items[$n]['url'] = ['site/index', 'id' => $node->getPrimaryKey()];    ///Note: Cannot be `Url::to()`! Otherwise, it will not be actived. @see [[yii\widgets::isItemActive($item)]]
        }
        return $items;
    }
    /**
     * Builds a tree from list array.
     * 
     * @see \yongtiger\yii2-comment\src\widgets\Comment.php (285)
     * @param array $data `$data = tree-manager::find()->all();`
     * @return array
     */
    public static function buildTree(&$data, $rootID = 0)
    {
        $tree = [];

        foreach ($data as $id => $node) {
            if (isset($node) && $node->parent_id == $rootID) {
                $data[$id] = (unset)$data[$id];///[v0.0.5 (FIX# buildTree)]As the php documentation reads:As foreach relies on the internal array pointer in PHP 5, changing it within the loop may lead to unexpected behavior.
                $tree['children'] = self::buildTree($data, $node->id);
                $tree[] = $node;
            }
        }

        return $tree;
    }
    /**
     * Returns all of categories as tree
     * @see https://github.com/NullRefExcep/yii2-category/blob/master/src/models/Category.php#L51
     * @param array $options
     * @return mixed
     */
    public static function getTree(array $options = [])
    {
        $depth = ArrayHelper::remove($options, 'depth', -1);
        /** @var \Closure $filter */
        $filter = ArrayHelper::remove($options, 'filter', function ($item) {
            return true;
        });
        /** @var Category[] $list */
        $list = self::find()->all();
        $list = ArrayHelper::remove($options, 'list', $list);
        $getChildren = function ($id, $depth) use ($list, &$getChildren, $filter) {
            $result = [];
            foreach ($list as $i => $item) {
//                 if (isset($item) && (int)$item['parent_id'] === (int)$id) {  ///only for adjacencyList!
                if (isset($item) && (isset($item->parent) ? (int)$item->parent->id : 0) === (int)$id) {///for all! adjacency-list, nested-sets, materialized-path, nested-intervals 只要含有parent方法！
                    $list[$i] = (unset)$list[$i];///[v0.0.5 (FIX# buildTree)]As the php documentation reads:As foreach relies on the internal array pointer in PHP 5, changing it within the loop may lead to unexpected behavior.
                    $r = [
                        'label' => $item['name'],
                        // 'sort' => $item['sort'],    ///for adjacency-list
                        'sort' => $item['lft'], ///for nested-sets
                        'id' => $item['id'],
                        'url' => ['site/index', 'id' => $item['id']],    ///Note: Cannot be `Url::to()`! Otherwise, it will not be actived. @see [[yii\widgets::isItemActive($item)]]
                        'icon' => 'fa fa-cog',
                    ];
                    $c = $depth ? $getChildren($item['id'], $depth - 1) : null;
                    if (!empty($c)) {
                        $r['items'] = $c;
                    }
                    if ($filter($r)) {
                        $result[] = $r;
                    }
                }
            }
            usort($result, function ($a, $b) {
                return $a['sort'] > $b['sort'];
            });
            return $result;
        };
        return $getChildren(0, $depth);////id
    }
}