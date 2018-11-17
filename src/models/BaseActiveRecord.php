<?php

namespace yozh\base\models;

use Yii;
use yozh\base\interfaces\models\ActiveRecordInterface;
use yozh\base\models\BaseActiveQuery as ActiveQuery;
use yozh\base\traits\ActiveRecordTrait;
use yozh\base\traits\ObjectTrait;

class BaseActiveRecord extends \yii\db\ActiveRecord implements ActiveRecordInterface
{
	use ObjectTrait, ActiveRecordTrait;
	
}