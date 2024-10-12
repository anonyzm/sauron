<?php


namespace common\models\auth;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * Class RedditUser
 * @package common\models
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $rateLimitRemaining
 * @property string $rateLimitUsed
 * @property string $rateLimitReset
 * @property int $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class RedditUser extends ActiveRecord
{
    //use MongoActiveRecordTrait;

    const STATUS_ACTIVE = 'active';
    const STATUS_BANNED = 'banned';
    const STATUS_RATE_LIMIT = 'ratelimit';
    const STATUS_DELETED = 'deleted';
    const STATUS_MAX_ERRORS = 'maxerrors';

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
            'username',
            'password',
            'status',
            'created_at',
            'updated_at',
            'rateLimitRemaining',
            'rateLimitReset',
            'rateLimitUsed',
        ];
    }

    public function setRateLimit($remaining, $used, $reset)
    {
        $this->rateLimitRemaining = $remaining;
        $this->rateLimitUsed = $used;
        $this->rateLimitReset = $reset;
        if ($remaining == 0) {
            $this->status = RedditUser::STATUS_RATE_LIMIT;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id'], 'safe'],
            [['username', 'password'], 'required'],
            ['username', 'unique'],
            [['username', 'password', 'rateLimitRemaining', 'rateLimitUsed', 'rateLimitReset'], 'string'],
            ['status', 'string'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }
}