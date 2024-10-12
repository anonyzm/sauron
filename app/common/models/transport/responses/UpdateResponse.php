<?php

namespace common\models\transport\responses;

use yii\base\Model;

class UpdateResponse extends Model
{
    public $entries = [];

    public function rules()
    {
        return [
            [['entries'], 'safe'],
        ];
    }
}
