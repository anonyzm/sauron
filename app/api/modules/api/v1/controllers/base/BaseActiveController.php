<?php

namespace api\modules\api\v1\controllers\base;

use Yii;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class BaseActiveController
 *
 * @package api\modules\api\v1\controllers\base
 */
abstract class BaseActiveController extends ActiveController
{
    protected $searchModelClass;
    protected $validateOnly = false;

    public function init()
    {
        parent::init();
        
        $this->on(ActiveController::EVENT_BEFORE_ACTION, function () {
            $this->validateOnly = (bool)Yii::$app->request->get('validateonly', false);
        });
    }
    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        
        foreach ($actions as $id => &$action) {
            if ('options' !== $id) {
                $action['findModel'] = [$this, 'findModel'];
            }
        }
        
        return $actions;
    }
    
    /**
     * @param $id
     *
     * @return null|\yii\mongodb\ActiveRecord
     * @throws NotFoundHttpException
     * @throws \ReflectionException
     */
    public function findModel($id)
    {
        /** @var \yii\mongodb\ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        
        if (!empty($id)) {
            $model = $modelClass::findOne(['_id' => (string)$id]);
        }
        
        if (isset($model)) {
            return $model;
        } else {
            $className = (new \ReflectionClass($modelClass))->getShortName();
            throw new NotFoundHttpException("{$className} not found: $id");
        }
    }
    
    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new $this->searchModelClass;
        
        if (Yii::$app->request->isHead) {
            $params = Yii::$app->request->bodyParams;
        } else {
            $params = Yii::$app->request->queryParams;
        }
        return $searchModel->search($params);
    }
    
    /**
     * @return BaseActiveRecord
     */
    protected function createModel()
    {
        $model = new $this->modelClass([
            'scenario' => Model::SCENARIO_DEFAULT,
        ]);
        return $model;
    }
    
    public function actionCreate()
    {
        $model = $this->createModel();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        $success = $this->validateOnly ? $model->validate() : $model->save();
        
        if ($success) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        
        return $model;
    }
}
