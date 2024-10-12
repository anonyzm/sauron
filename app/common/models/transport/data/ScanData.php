<?php

namespace common\models\transport\data;

use yii\base\Model;

class ScanData extends Model
{
    public $theme_id;
    public $words = [];
    public $min_time;
    public $minus_words;
    public $limit;
    public $auth_data;

    public function rules()
    {
        return [
            [['theme_id', 'words'], 'required'],
            [['min_time', 'limit'], 'integer'],
            [['theme_id'], 'string'],
            [['words', 'minus_words', 'auth_data'], 'safe'],
        ];
    }
}