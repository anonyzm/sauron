<?php

/* @var $this \yii\web\View */

/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

$menuItems = [
    ['label' => 'Services', 'url' => ['/service/index']],
    ['label' => 'Accounts', 'url' => ['/account/index']],
    ['label' => 'Themes', 'url' => ['/theme/index']],
    ['label' => 'Mentions', 'url' => ['/presentation-mention/index']],
    ['label' => 'Reddit Users', 'url' => ['/reddit-user/index']],
    ['label' => 'Proxy', 'url' => ['/proxy/index']],
    ['label' => 'Proxy Statistic', 'url' => ['/proxy-stat/index']],
];

if (Yii::$app->user->isGuest) {
    $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
} else {
    $logoutForm = Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar-form']);
    $logoutForm .= Html::submitButton('Logout (' . Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link']);
    $logoutForm .= Html::endForm();
    $menuItems[] = Html::tag('li', $logoutForm);
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Sauron',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);

    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= \ladno\yii2toolkit\widgets\Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<!--footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Omnipub <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer-->

<style>
    body {
        padding-top: 60px;
    }
</style>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
