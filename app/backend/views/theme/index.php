<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel api\models\search\ThemeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Themes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="theme-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!--p>
        <?= Html::a('Create Theme', ['create'], ['class' => 'btn btn-success']) ?>
    </p-->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            '_id',
            'account_id',
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
            'persisted',
            'scanned_at',
            'created_at',
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
