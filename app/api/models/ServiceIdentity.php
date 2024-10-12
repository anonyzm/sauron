<?php

namespace api\models;

use common\models\Service;
use Yii;

class ServiceIdentity extends Service implements \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne(['_id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->token === $authKey;
    }
}
