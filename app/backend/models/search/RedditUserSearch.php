<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\auth\RedditUser;

/**
 * RedditUserSearch represents the model behind the search form of `common\models\auth\RedditUser`.
 */
class RedditUserSearch extends RedditUser
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id', 'username', 'password', 'status', 'created_at', 'updated_at', 'rateLimitRemaining', 'rateLimitReset', 'rateLimitUsed'], 'safe'],
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
        $query = RedditUser::find();

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
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at])
            ->andFilterWhere(['like', 'rateLimitRemaining', $this->rateLimitRemaining])
            ->andFilterWhere(['like', 'rateLimitReset', $this->rateLimitReset])
            ->andFilterWhere(['like', 'rateLimitUsed', $this->rateLimitUsed]);

        return $dataProvider;
    }
}
