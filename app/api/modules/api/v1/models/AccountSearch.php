<?php

namespace api\modules\api\v1\models;

use api\models\ServiceIdentity;
use common\models\Account;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

/**
 * AccountSearch represents the model behind the search form of `app\models\data\Account`.
 */
class AccountSearch extends Account
{

    public function rules()
    {
        return [
            [['id', 'external_id'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Account::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->setAttributes($params);

        if (!$this->validate()) {
            throw new BadRequestHttpException(implode('; ', $this->firstErrors));
        }

        $query->andFilterWhere([
            'service_id' => \Yii::$app->user->identity->id,
        ]);

        if(!empty($this->id)) {
            $query->andFilterWhere([
                '_id' => $this->id,
            ]);
        }
        else if(!empty($this->external_id)) {
            $query->andFilterWhere([
                'external_id' => $this->external_id,
            ]);
        }

        return $dataProvider;
    }
}
