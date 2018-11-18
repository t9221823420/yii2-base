<?php

namespace yozh\base\models;

use Yii;
use yii\base\DynamicModel;
use yozh\base\interfaces\models\ActiveRecordInterface;
use yozh\base\models\BaseActiveQuery as ActiveQuery;
use yozh\base\traits\ActiveRecordTrait;
use yozh\base\traits\ObjectTrait;
use yozh\base\traits\ViewModelTrait;

class BaseViewModel extends DynamicModel
{
	use ObjectTrait, ViewModelTrait;
	
}