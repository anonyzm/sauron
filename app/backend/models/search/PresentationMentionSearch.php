<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\presentation\PresentationMention;

/**
 * PresentationMentionSearch represents the model behind the search form of `common\models\presentation\PresentationMention`.
 */
class PresentationMentionSearch extends PresentationMention
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id', 'theme_id', 'external_id', 'link', 'media', 'media_type', 'user_id', 'username', 'userlogin', 'userpic', 'text', 'source', 'external_link', 'meta', 'created', 'persisted'], 'safe'],
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
        $query = PresentationMention::find();

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
            ->andFilterWhere(['like', 'theme_id', $this->theme_id])
            ->andFilterWhere(['like', 'external_id', $this->external_id])
            ->andFilterWhere(['like', 'link', $this->link])
            ->andFilterWhere(['like', 'media', $this->media])
            ->andFilterWhere(['like', 'media_type', $this->media_type])
            ->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'userlogin', $this->userlogin])
            ->andFilterWhere(['like', 'userpic', $this->userpic])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'external_link', $this->external_link])
            ->andFilterWhere(['like', 'meta', $this->meta])
            ->andFilterWhere(['like', 'created', $this->created])
            ->andFilterWhere(['like', 'persisted', $this->persisted]);

        return $dataProvider;
    }
}
