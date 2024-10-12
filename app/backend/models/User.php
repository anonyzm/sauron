<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class User
 * @package backend\models
 */
class User extends Model implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $accessToken;
    public $auth_key;

    public function init()
    {
        parent::init();

        $this->id = 1;
        $this->username = ArrayHelper::getValue(Yii::$app->params, 'backendUser');
        $this->password = ArrayHelper::getValue(Yii::$app->params, 'backendPassword');
        $this->accessToken = md5(md5($this->username).md5($this->password).'-token');
        $this->auth_key = md5(md5($this->username).md5($this->password).'-key');
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        if($id !== 1) {
            return false;
        }
        return new User();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = new User();
        if($token !== $user->accessToken) {
            return null;
        }

        return $user;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = new User();
        if($username !== $user->username) {
            return null;
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {

    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $password === $this->password;
    }
}
