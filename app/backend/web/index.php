<?php
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../common/config/dotenv.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$appName = getenv('APPLICATION');
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../' . $appName . '/config/main.php')
);

$application = new yii\web\Application($config);
$application->setVendorPath('/app/vendor');

/** bowerfix */
Yii::setAlias('@bower', '/app/vendor/bower-asset');
Yii::setAlias('@yii/gii', '/app/vendor/yiisoft/yii2-gii/src');
Yii::setAlias('@yii/debug', '/app/vendor/yiisoft/yii2-debug/src');

$application->run();
