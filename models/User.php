<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'users';
    }

    public function attributeLabels()
    {
        return [
            'id'       => 'id',
            'name'     => 'name',
            'age'      => 'age',
            'gender'   => 'gender',
            'city'     => 'city',
            'country'  => 'country',
            'email'    => 'email',
            'salt'     => 'salt',
            'password' => 'password',
            'picture'  => 'picture',
        ];
    }
    public function getId()
    {
        return $this->getPrimaryKey();
    }

}
