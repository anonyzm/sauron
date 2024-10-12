<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Theme;

/**
 * ThemeSearch represents the model behind the search form of `common\models\Theme`.
 */
class ThemeSearch extends Theme
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id', 'account_id', 'status', 'name', 'words', 'limit', 'maxLimit', 'collected', 'minusWords', 'sources', 'persisted', 'scanned_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function init()
    {
        //parent::init();
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
        $query = Theme::find();

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
            ->andFilterWhere(['like', 'account_id', $this->account_id])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'words', $this->words])
            ->andFilterWhere(['like', 'limit', $this->limit])
            ->andFilterWhere(['like', 'maxLimit', $this->maxLimit])
            ->andFilterWhere(['like', 'collected', $this->collected])
            ->andFilterWhere(['like', 'minusWords', $this->minusWords])
            ->andFilterWhere(['like', 'sources', $this->sources])
            ->andFilterWhere(['like', 'persisted', $this->persisted])
            ->andFilterWhere(['like', 'scanned_at', $this->scanned_at])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);

        return $dataProvider;
    }
}
