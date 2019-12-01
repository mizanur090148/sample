<?php
/**
 * Created by PhpStorm.
 * User: hizbul
 * Date: 2/5/17
 * Time: 5:49 PM
 */

namespace App\Repository;


class AttributeManager
{
    /**
     * @param $model
     * @param Related model name $relatedModel
     * @param  $items
     * @return mixed
     */
    public function getModel($model, $items)
    {
        $data = [];
        foreach ($items as $relatedModel => $item) {
            foreach ($model->$relatedModel as $obj) {
                $data[$item][] = $obj->id;
            }
            if (count($data) > 0)
                $itemId = $item . '_id';
            $model->$itemId = implode('|', $data[$item]);
        }

        return $model;
    }
}