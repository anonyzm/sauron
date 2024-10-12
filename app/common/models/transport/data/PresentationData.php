<?php

namespace common\models\transport\data;

use yii\base\Model;

class PresentationData extends Model
{
    public $theme_id;

    public function rules()
    {
        return [
            [['theme_id'], 'required'],
            [['theme_id'], 'string'],
        ];
    }
}