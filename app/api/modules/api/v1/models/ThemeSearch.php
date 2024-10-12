<?php

namespace api\modules\api\v1\models;

use common\models\Account;
use common\models\Theme;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class ThemeSearch extends Theme
{
    public function rules()
    {
        return [
            [['id', 'account_id'], 'eitherValidator', 'skipOnEmpty' => false],
            [['id', 'account_id'], 'accountValidator', 'skipOnEmpty' => false],
            [['id', 'account_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function eitherValidator($attribute, $params, $validator) {
        if(empty($this->id) && empty($this->account_id)) {
            $this->addError($attribute, 'Either ID or Account ID should be set');
        }
    }

    public function accountValidator($attribute, $params, $validator) {
        if(!empty($this->account_id)) {
            $accountExists = Account::find()->where(['_id' => $this->account_id, 'service_id' => Yii::$app->user->identity->id])->exists();
            if(!$accountExists) {
                $this->addError($attribute, 'Account not found');
            }
        }
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Theme::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->setAttributes($params);

        if (!$this->validate()) {
            throw new BadRequestHttpException(implode('; ', $this->firstErrors));
        }

        if(!empty($this->id)) {
            $query->andFilterWhere([
                '_id' => $this->id,
            ]);
        }
        else if(!empty($this->account_id)) {
            $query->andFilterWhere([
                'account_id' => $this->account_id,
            ]);
        }

        return $dataProvider;
    }
}
