<?php

namespace common\models\transport\data;

use yii\base\Model;

class TimezoneData extends Model
{
    public $theme_id;
    public $timezone;

    public function rules()
    {
        return [
            [['theme_id', 'timezone'], 'required'],
            [['theme_id', 'timezone'], 'string'],
        ];
    }
}