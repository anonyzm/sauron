<?php

namespace common\models;

use common\models\traits\MongoActiveRecordTrait;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\mongodb\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 *
 * @property integer $id
 * @property string $alias
 * @property string $token
 * @property integer $created_at
 * @property integer $updated_at
 */
class Service extends ActiveRecord
{
    use MongoActiveRecordTrait;

    public static function collectionName() {
        return 'service';
    }

    public function attributes()
    {
        return [
            '_id',
            'alias',
            'token',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alias'], 'required'],
            [['alias'], 'string', 'max' => 50],
            [['alias'], 'unique'],
            ['token', 'unique'],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::className(),
        ];

        $behaviors['typecast'] = [
            'class' => AttributeTypecastBehavior::class,
        ];

        return $behaviors;
    }

    public function init()
    {
        parent::init();
        $this->on(ActiveRecord::EVENT_BEFORE_INSERT, [$this, 'generateTokenIfEmpty']);
        $this->on(ActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'generateTokenIfEmpty']);
    }

    protected function generateTokenIfEmpty()
    {
        if (empty($this->token)) {
            $this->generateToken();
        }
    }

    public static function findByToken($token)
    {
        return static::find()->where(['token' => $token])->one();
    }

    public function generateToken()
    {
        $this->token = Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', 'ID'),
            'alias' => Yii::t('app', 'Alias'),
        ];
    }
}
