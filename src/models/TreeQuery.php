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

namespace yongtiger\tree\models;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for both adjacency list and nested sets [[Tree]] models.
 */
class TreeQuery extends ActiveQuery
{
    /**
     * Gets the root nodes.
     * @return \yii\db\ActiveQuery
     */
    public function roots()
    {
        /** @var \yii\db\ActiveQuery $this */
        $class = $this->modelClass;
        $model = new $class;
        if ($model->hasProperty('leftAttribute')) {	///for nested sets
        	return $this->andWhere([$model->leftAttribute => 1]);
        } elseif ($model->hasProperty('parentAttribute')) {	///for adjacency list
        	return $this->andWhere([$model->parentAttribute => null]);
        } else {
        	throw new InvalidConfigException('The "model" property `leftAttribute` or `parentAttribute` must be set.');
        }
    }

    /**
     * Gets the leaf nodes.
     * @return \yii\db\ActiveQuery
     */
    public function leaves()
    {
        /** @var \yii\db\ActiveQuery $this */
        $class = $this->modelClass;
        $model = new $class;
        if ($model->hasProperty('leftAttribute')) {	///for nested sets
	        $db = $model->getDb();
	        return $this->andWhere([$model->rightAttribute => new Expression($db->quoteColumnName($model->leftAttribute) . '+ 1')]);
        } elseif ($model->hasProperty('parentAttribute')) {	///for adjacency list
        	///@see https://github.com/paulzi/yii2-adjacency-list/blob/master/AdjacencyListBehavior.php#L240
        	return $this->joinWith(['children' => function ($query) {
                /** @var \yii\db\ActiveQuery $query */
                $modelClass = $query->modelClass;
                $query->from($modelClass::tableName() . ' children');
            }])->andWhere(["children.[[{$model->parentAttribute}]]" => null]);
        } else {
        	throw new InvalidConfigException('The "model" property `leftAttribute` or `parentAttribute` must be set.');
        }
	}
}
