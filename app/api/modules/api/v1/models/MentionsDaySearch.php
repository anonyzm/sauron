<?php

namespace api\modules\api\v1\models;

use common\models\presentation\MentionsDay;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class MentionsDaySearch extends MentionsDay
{
    public $from;
    public $to;
    public $sources;

    public function rules()
    {
        return [
            [['id', 'theme_id', 'from', 'to', 'sources'], 'safe'],
            [['from', 'to'], 'integer'],

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
        $query = MentionsDay::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['day_timestamp' => SORT_ASC]],
        ]);

        $this->setAttributes($params);

        if (!$this->validate()) {
            throw new BadRequestHttpException(implode('; ', $this->firstErrors));
        }

        if (!empty($this->id)) {
            $query->andFilterWhere([
                '_id' => $this->id,
            ]);
        }

        if (!empty($this->theme_id)) {
            $query->andFilterWhere([
                'theme_id' => $this->theme_id,
            ]);
        }

        if (!empty($this->sources)) {
            $query->andFilterWhere([
                'source' => $this->sources,
            ]);
        }

        if (!empty($this->from)) {
            $query->andFilterWhere(['>=', 'day_timestamp', $this->from]);
        }

        if (!empty($this->to)) {
            $query->andFilterWhere(['<=', 'day_timestamp', $this->to]);
        }

        return $dataProvider;
    }
}
