<?php

namespace common\models\transport;

use yii\base\Model;

/**
 * Class Entry
 * @package common\models\transport
 */
class Entry extends Model
{
    const MEDIA_TYPE_PHOTO = 'photo';
    const MEDIA_TYPE_VIDEO = 'video';

    public $external_id;
    public $link;
    public $media;
    public $media_type;
    public $user_id;
    public $username;
    public $userlogin;
    public $userpic;
    public $title;
    public $text;
    public $external_link;
    public $source;
    public $created;
    public $meta;

    public function rules()
    {
        return [
            [['external_id', 'source', 'created'], 'required'],
            [['external_id', 'link', 'media', 'media_type', 'user_id', 'username', 'userlogin', 'userpic', 'title', 'text', 'source', 'external_link'], 'string'],
            [['created'], 'integer'],
            [['meta'], 'safe'],
        ];
    }
}