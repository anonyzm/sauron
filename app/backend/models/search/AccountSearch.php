<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Account;

/**
 * AccountSearch represents the model behind the search form of `common\models\Account`.
 */
class AccountSearch extends Account
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id', 'service_id', 'external_id', 'alias', 'limit', 'maxLimit', 'collected', 'timezone', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', '_id', $this->_id])
            ->andFilterWhere(['like', 'service_id', $this->service_id])
            ->andFilterWhere(['like', 'external_id', $this->external_id])
            ->andFilterWhere(['like', 'alias', $this->alias])
            ->andFilterWhere(['like', 'limit', $this->limit])
            ->andFilterWhere(['like', 'maxLimit', $this->maxLimit])
            ->andFilterWhere(['like', 'collected', $this->collected])
            ->andFilterWhere(['like', 'timezone', $this->timezone])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);

        return $dataProvider;
    }
}
