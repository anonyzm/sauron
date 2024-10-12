<?php

namespace common\models\traits;

use yii\helpers\ArrayHelper;

/**
 * Class MongoIdTrait
 * @package common\models\traits
 */
trait MongoActiveRecordTrait
{
    public function fields()
    {
        $fields = $this->apiFields();
        unset($fields['_id']);

        return ArrayHelper::merge([
            'id'
        ], $fields);
    }


    public function apiFields()
    {
        return parent::fields();
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string Mongo-id
     */
    public function getId()
    {
        return (string)$this->_id;
    }

    public function validateDirty()
    {
        return $this->validate(array_keys($this->dirtyAttributes));
    }

    public function saveDirty()
    {
        if (!empty($this->getDirtyAttributes()) && $this->validateDirty()) {
            return $this->save(false);
        }

        return false;
    }
}
