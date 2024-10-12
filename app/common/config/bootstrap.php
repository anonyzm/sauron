<?php
Yii::setAlias('@common', dirname(__DIR__));
// задается через $application->setVendorPath исполняемом файле
//Yii::setAlias('@vendor', dirname(dirname(__DIR__)) . '/vendor');
Yii::setAlias('@operator', dirname(dirname(__DIR__)) . '/operator');
Yii::setAlias('@scheduler', dirname(dirname(__DIR__)) . '/scheduler');
Yii::setAlias('@persister', dirname(dirname(__DIR__)) . '/persister');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@yii/mongodb', dirname(dirname(__DIR__)) . '/vendor/yiisoft/yii2-mongodb/src');