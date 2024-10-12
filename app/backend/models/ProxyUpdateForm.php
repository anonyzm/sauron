<?php

namespace backend\models;

use yii\base\Model;

/**
 * ProxyUpdateForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class ProxyUpdateForm extends Model
{
    public $pool_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['pool_id'], 'required'],
            [['pool_id'], 'string'],
        ];
    }
}
