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

namespace yongtiger\tree\actions;

use Yii;
use yii\web\Response;

/**
 * Class MoveAction
 *
 * @package yongtiger\tree\actions
 */
class MoveAction extends BaseAction
{
    /**
     * Moves a node by given nodes with `parent_id`, `prev_id` and `next_id`.
     *
     * @param integer $id the primary key of the moved node
     * @return array
     */
    public function run($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = $this->findModel($id);

            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                $parentId = isset($post['parent_id']) ? $post['parent_id'] : 0;
                $prevId = isset($post['prev_id']) ? $post['prev_id'] : 0;
                $nextId = isset($post['next_id']) ? $post['next_id'] : 0;

                try {
                    if (empty($parentId)) {
                        if ($this->isMultipleTree) {    ///[isMultipleTree]
                            if (!$model->isRoot()) {
                                $result = $model->makeRoot();
                            }

                            ///?????root sorting logic

                        } else {
                            return [
                                'status' => 'error',
                                'error' => 'Tree root operations are protected when "isMultipleTree" is false!',
                            ];
                        }
                    } elseif (!empty($prevId)) {
                        $prevModel = $this->findModel($prevId);
                        $result = $model->insertAfter($prevModel);
                    } elseif (!empty($nextId)) {
                        $nextModel = $this->findModel($nextId);
                        $result = $model->insertBefore($nextModel);
                    } else {
                        $parentModel = $this->findModel($parentId);
                        $result = $model->appendTo($parentModel);
                    }
                } catch (\Exception $e) {
                    return [
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ];
                }

                ///Note: remove `save()` when using `creocoder/yii2-nested-sets`!
                return is_bool($result) ? $result : $result->save();
            }
        }

        ///Redirect to this controller's default action
        return $this->controller->redirect([$this->controller->defaultAction]);
    }
}