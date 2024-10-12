<?php

namespace common\models\transport\responses;

use common\models\transport\Entry;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class ScanResponse extends Model
{
    public $theme_id;
    /**
     * @var Entry[]
     */
    public $entries = [];

    public function rules()
    {
        return [
            [['theme_id'], 'required'],
            [['theme_id'], 'string'],
            [['entries'], 'safe'],
        ];
    }

    public function hasEntries($word = null) {
        $entries = $this->entries;
        if($word) {
            $entries = ArrayHelper::getValue($this->entries, $word, []);
        }
        return count($entries) > 0;
    }
}
