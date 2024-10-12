<?php

namespace common\models\transport\data;

use yii\base\Model;

class UpdateData extends Model
{
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
        ];
    }
}