<?php

namespace yozh\base\models;

use Yii;
use yii\base\DynamicModel;
use yozh\base\traits\ObjectTrait;
use yozh\base\traits\ViewModelTrait;

class BaseViewModel extends DynamicModel
{
	use ObjectTrait, ViewModelTrait;
	
}