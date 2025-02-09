<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\auth\RedditUser */

$this->title = 'Update Reddit User: ' . $model->_id;
$this->params['breadcrumbs'][] = ['label' => 'Reddit Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->_id, 'url' => ['view', 'id' => (string)$model->_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="reddit-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
