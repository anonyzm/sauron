<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */

$this->title = 'Proxy';
$this->params['breadcrumbs'][] = $this->title;
$pools = \ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\ProxyPool::find()->all();
$poolList = [];
foreach ($pools as $pool) {
    $poolList[(string)$pool->_id] = $pool->name;
}
?>
<div class="theme-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'pool_id')->dropDownList($poolList) ?>

        <div class="form-group">
            <?= Html::submitButton('Update proxies', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </p>

</div>
