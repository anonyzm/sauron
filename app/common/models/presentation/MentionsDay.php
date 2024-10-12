<?php
namespace common\models\presentation;

use common\models\traits\MongoActiveRecordTrait;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * Class MentionsDay
 * @property string $theme_id
 * @property integer $day_timestamp
 * @property integer $count
 * @property integer $source
 * @property integer $created_at
 * @property integer $updated_at
 * @property mixed $meta
 */
class MentionsDay extends ActiveRecord
{
    use MongoActiveRecordTrait;

    public static function getDb()
    {
        return Yii::$app->get('presentationMongodb');
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['typecast'] = [
            'class' => AttributeTypecastBehavior::class,
        ];

        return $behaviors;
    }

    public function attributes()
    {
        return [
            '_id',
            'theme_id',
            'day_timestamp',
            'count',
            'source',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['theme_id', 'day_timestamp', 'source', 'count'], 'required'],
            [['theme_id', 'source'], 'string'],
            [['day_timestamp', 'count', 'created_at', 'updated_at'], 'integer'],
        ];
    }
}