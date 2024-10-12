<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Account */
/* @var $themesProvider \yii\data\ActiveDataProvider */

$this->title = $model->_id;
$this->params['breadcrumbs'][] = ['label' => 'Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="account-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => (string)$model->_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => (string)$model->_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            '_id',
            'service_id',
            'external_id',
            'alias',
            'limit',
            'maxLimit',
            'collected',
            'timezone',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $themesProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            '_id',
            //'account_id',
            'status',
            'name',
            [
                'attribute' => 'words',
                'value' => function ($model) {
                    return implode(', ', $model->words);
                },
            ],
            [
                'attribute' => 'minusWords',
                'value' => function ($model) {
                    return implode(', ', $model->minusWords);
                },
            ],
            //'words',
            //'minusWords',
            //'limit',
            'maxLimit',
            'collected',
            //'sources',
            [
                'attribute' => 'sources',
                'value' => function ($model) {
                    return implode(', ', $model->sources);
                },
            ],
            //'persisted',
            'scanned_at:datetime',
            'created_at:datetime',
            //'updated_at',
            [
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/theme/view', 'id' => $model->id] , ['title' => 'View', 'data-pjax' => 0]);
                }
            ],

            //['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>

</div>
