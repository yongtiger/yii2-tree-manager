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
use yii\helpers\ArrayHelper;
use yongtiger\tree\models\TreeQuery;
use yongtiger\tree\Module;

/**
 * Trait that must be used by the [[Tree]] model
 */
trait TreeTrait
{
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge([
            'tree' => [
                'class' => \creocoder\nestedsets\NestedSetsBehavior::className(),
            ],
        ], Module::instance()->behaviors);
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return Module::instance()->isOpAllTransactions ? [self::SCENARIO_DEFAULT => self::OP_ALL] : [];
    }

    /**
     * @inheritdoc
     * @return TreeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return Yii::createObject(Module::instance()->queryClass ? : TreeQuery::className(), [get_called_class()]);
    }

    /**
     * Returns all of tree nodes as array.
     *
     * @see https://github.com/NullRefExcep/yii2-category/blob/master/src/models/Category.php#L51
     * @param array $options
     * @return array
     */
    public static function getTree($options = [])
    {
        $rootId = ArrayHelper::remove($options, 'rootId', 0);
        $depth = ArrayHelper::remove($options, 'depth', -1);
        $itemsName = ArrayHelper::remove($options, 'itemsName', 'items');
        $sortOrder = ArrayHelper::remove($options, 'sortOrder', SORT_ASC);
        $sortName = ArrayHelper::remove($options, 'sortName', 'sort');
        /** @var \Closure $map */
        $map = ArrayHelper::remove($options, 'map', function ($item) {return true;});
        /** @var \Closure $filter */
        $filter = ArrayHelper::remove($options, 'filter', function ($item) {return true;});
        
        /** @var Tree[] $list */
        $list = ArrayHelper::remove($options, 'list', self::find()->all());

        $getChildren = function ($id, $depth) use (&$list, &$parentsMethod, &$sortProperty, &$getChildren, $map, $itemsName, $filter, $sortOrder, $sortName) {
            $result = [];
            foreach ($list as $i => $item) {
                if (isset($item)) {

                    if ($parentsMethod === null) {
                        if ($item->hasMethod('parents', true)) {
                            $parentsMethod = 'parents';     ///for creocoder/yii2-nested-sets
                        } elseif ($item->hasMethod('getParents', true)) {
                            $parentsMethod = 'getParents';    ///for paulzi/yii2-nested-sets
                        } else {
                            throw new InvalidConfigException('The "model" method `parents` or `getParents` must be set.');
                        }
                        if ($item->hasProperty('sortAttribute')) {  ///for yii2-adjacency-list
                            $sortProperty = 'sortAttribute';
                        } elseif ($item->hasProperty('leftAttribute')) {    ///for yii2-nested-sets
                            $sortProperty = 'leftAttribute';
                        }
                    }
                    $parent = $item->{$parentsMethod}(1)->one();

                    if ((!empty($parent) ? (int)$parent->id : 0) === (int)$id) {
                        $list[$i] = (unset)$list[$i];   ///[v0.0.5 (FIX# buildTree)]As the php documentation reads:As foreach relies on the internal array pointer in PHP 5, changing it within the loop may lead to unexpected behavior.

                        $r = array_merge([
                            'id' => $item['id'],
                            $sortName => $sortProperty === 'sortAttribute' ? $item[$item->sortAttribute] : ($sortProperty === 'leftAttribute' ? $item[$item->leftAttribute] : null),
                        ], $map($item));

                        $c = $depth ? $getChildren($item['id'], $depth - 1) : null;
                        if (!empty($c)) {
                            $r[$itemsName] = $c;
                        }

                        if ($filter($r)) {
                            $result[] = $r;
                        }
                    }
                }
            }
            usort($result, function ($a, $b) use ($sortOrder, $sortName) {
                return $sortOrder === SORT_ASC ? $a[$sortName] >= $b[$sortName] : $a[$sortName] < $b[$sortName];
            });
            return $result;
        };

        return $getChildren($rootId, $depth);
    }
}