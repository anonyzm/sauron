<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\auth\RedditUser */

$this->title = 'Create Reddit User';
$this->params['breadcrumbs'][] = ['label' => 'Reddit Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reddit-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
