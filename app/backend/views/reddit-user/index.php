<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\RedditUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reddit Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reddit-user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Reddit User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            '_id',
            'username',
            //'password',
            'status',
            'created_at:datetime',
            'updated_at:datetime',
            'rateLimitRemaining',
            'rateLimitReset',
            //'rateLimitUsed',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
