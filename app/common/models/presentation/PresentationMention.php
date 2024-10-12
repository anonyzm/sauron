<?php
namespace common\models\presentation;

use common\models\Mention;
use common\models\traits\MongoActiveRecordTrait;
use Yii;
use yii\base\ErrorException;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * Class PresentationMention
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
 * @property integer $persisted
 * @property-read string $codeFromLink
 * @property mixed $meta
 */
class PresentationMention extends ActiveRecord
{
    use MongoActiveRecordTrait;

    const SOURCE_TWITTER = 'twitter';
    const SOURCE_INSTAGRAM = 'instagram';
    const SOURCE_REDDIT = 'reddit';
    const SOURCE_YOUTUBE = 'youtube';

    public static function getDb()
    {
        return Yii::$app->get('presentationMongodb');
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'persisted',
            'updatedAtAttribute' => null,
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
            'persisted',
        ];
    }

    public function rules()
    {
        return [
            [['theme_id', 'external_id', 'source', 'created'], 'required'],
            [['theme_id', 'external_id', 'link', 'media', 'media_type', 'user_id', 'username', 'userlogin', 'userpic', 'title', 'text', 'external_link', 'source'], 'string'],
            [['created', 'persisted'], 'integer'],
            [['meta'], 'safe'],
        ];
    }

    public function isUpdateRequired() {
        if($this->isInstagram()) {
            if(!$this->hasLink()) {
                return false;
            }
            $userInfoMissing = empty($this->username) || empty($this->userpic) || empty($this->userlogin);
            $pictureMissing = empty($this->media);
            return $userInfoMissing || $pictureMissing;
        }
        return false;
    }

    public function hasLink() {
        return !empty($this->link);
    }

    public function isInstagram() {
        return $this->source === self::SOURCE_INSTAGRAM;
    }

    public function isTwitter() {
        return $this->source === self::SOURCE_TWITTER;
    }

    public function isReddit() {
        return $this->source === self::SOURCE_REDDIT;
    }

    public function isYoutube() {
        return $this->source === self::SOURCE_YOUTUBE;
    }

    public function getCodeFromLink() {
        if(empty($this->link)) {
            throw new ErrorException('Link is empty');
        }

        $link = explode('/', $this->link);
        return $link[count($link) - 2];
    }

    public static function fromMention(Mention $mention) {
        $model = new self();
        $model->theme_id = $mention->theme_id;
        $model->external_id = $mention->external_id;
        $model->link = $mention->link;
        $model->media = $mention->media;
        $model->media_type = $mention->media_type;
        $model->user_id = $mention->user_id;
        $model->username = $mention->username;
        $model->userlogin = $mention->userlogin;
        $model->userpic = $mention->userpic;
        $model->title = $mention->title;
        $model->text = $mention->text;
        $model->external_link = $mention->external_link;
        $model->source = $mention->source;
        $model->meta = $mention->meta;
        $model->created = $mention->created;
        $model->persisted = time();

        if(!$model->validate()) {
            \Yii::error(['msg' => 'Error validating PresentationMention', 'errors' => json_encode($model->errors)]);
            return false;
        }

        return $model;
    }
}