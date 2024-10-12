<?php

namespace api\modules\api\v1;

use api\modules\api\v1\components\ErrorHandler;
use Yii;
use yii\base\BootstrapInterface;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\UrlRule;
use yii\web\Application;

/**
 * api module definition class
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\modules\api\v1\controllers';

    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->initializeUrlRules();
        }
    }

    public function init()
    {
        parent::init();
        \Yii::configure($this, [
            'components' => [
                'errorHandler' => [
                    'class' => ErrorHandler::class,
                ]
            ],
        ]);

        /** @var ErrorHandler $handler */
        $handler = $this->get('errorHandler');
        \Yii::$app->set('errorHandler', $handler);
        $handler->register();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];
        return $behaviors;
    }

    /**
     * Это нужно для того чтобы корректно работал CORS, запрос на OPTIONS должен идти без авторизации
     * @param \yii\base\Action $action
     * @return bool|void
     */
    public function beforeAction($action)
    {
        $response = Yii::$app->response;
        $response->headers->add('Access-Control-Allow-Origin', '*');
        $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
        $response->headers->add('Access-Control-Allow-Headers', 'Content-Type, api_key, Authorization');
        if(Yii::$app->request->isOptions) {
            return;
        }

        return parent::beforeAction($action);
    }

    public function initializeUrlRules() {
        Yii::$app->urlManager->addRules([
            [
                'class' => UrlRule::class,
                'prefix' => 'api',
                'controller' => ['v1/account', 'v1/theme'],
                'tokens' => [
                    '{id}' => '<id:[a-z0-9-]*>',
                ],
            ],
            [
                'class' => UrlRule::class,
                'prefix' => 'api',
                'controller' => ['v1/mention', 'v1/mentions-day'],
            ],
        ]);
    }
}
