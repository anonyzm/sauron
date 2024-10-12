<?php

namespace api\modules\api\v1\controllers;

use api\modules\api\v1\controllers\base\BaseActiveController;
use api\modules\api\v1\models\PresentationMentionSearch;
use common\components\instagram\PresentationMentionConsister;
use common\models\presentation\PresentationMention;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class MentionController extends BaseActiveController
{
    public $modelClass = PresentationMention::class;
    public $searchModelClass = PresentationMentionSearch::class;

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        /** @var PresentationMentionSearch $searchModel */
        $searchModel = new $this->searchModelClass;
        if (Yii::$app->request->isHead) {
            $params = Yii::$app->request->bodyParams;
        } else {
            $params = Yii::$app->request->queryParams;
        }
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = $searchModel->search($params);

        // Если включено в настройках
        if(ArrayHelper::getValue(Yii::$app->params, 'settings.updateInstaOnDemand', false)) {
            // Подготовка данных для инстаграма, догружаем данные при обращении
            if (empty($searchModel->sources) || (is_array($searchModel->sources) && in_array('instagram', $searchModel->sources))) {
                $keyedModels = [];
                $models = $dataProvider->getModels();
                $consister = new PresentationMentionConsister();
                foreach ($models as $key => $model) {
                    /** @var PresentationMention $model */
                    $keyedModels[$model->external_id] = $model;
                    if ($model->isInstagram()) {
                        if ($model->isUpdateRequired()) {
                            $consister->addModel($model);
                        }
                    }
                }

                $updatedModels = $consister->batchUpdate();
                if($updatedModels) {
                    foreach ($updatedModels as $external_id => $model) {
                        $keyedModels[$external_id] = $model;
                    }
                }

                usort($keyedModels, function ($a, $b) {
                    if ($a->created === $b->created) {
                        return 0;
                    }
                    return ($a->created > $b->created) ? -1 : 1;
                });

                $dataProvider->setModels($keyedModels);
            }
        }

        return $dataProvider;
    }
}
