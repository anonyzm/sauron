<?php
namespace common\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * Class Mention
 * @property string $theme_id
 * @property string $external_id
 * @property string $link
 * @property string $media
 * @property string $media_type
 * @property string $user_id
 * @property string $username
 * @property string $userlogin
 * @property string $userpic
 * @property string $title
 * @property string $text
 * @property string $external_link
 * @property string $source
 * @property integer $created
 * @property integer $updated_at
 * @property integer $gathered
 * @property mixed $meta
 */
class Mention extends ActiveRecord
{
    const SOURCE_INSTAGRAM = 'instagram';
    const SOURCE_TWITTER = 'twitter';
    const SOURCE_REDDIT = 'reddit';
    const SOURCE_YOUTUBE = 'youtube';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'gathered',
            'updatedAtAttribute' => 'updated_at',
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
            'external_id',
            'link',
            'media',
            'media_type',
            'user_id',
            'username',
            'userlogin',
            'userpic',
            'title',
            'text',
            'external_link',
            'source',
            'meta',
            'created',
            'updated_at',
            'gathered',
        ];
    }

    public function rules()
    {
        return [
            [['theme_id', 'external_id', 'source', 'created'], 'required'],
            [['theme_id', 'external_id', 'link', 'media', 'media_type', 'user_id', 'username', 'userlogin', 'userpic', 'title', 'text', 'external_link'], 'string'],
            [['created', 'gathered', 'updated_at'], 'integer'],
            [['meta'], 'safe'],
        ];
    }
}