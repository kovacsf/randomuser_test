<?php

/* @var $this yii\web\View */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title                   = 'Display users';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?=Html::encode($this->title)?></h1>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        [
            'label' => 'gender',
            'value' => 'gender',
        ],
        [
            'label' => 'name',
            'value' => 'name',
        ],
    ],
]);
?>
