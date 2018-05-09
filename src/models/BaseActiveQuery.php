<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 7:14
 */

namespace yozh\base\models;

use yozh\base\traits\ActiveQueryTrait;

class BaseActiveQuery extends \yii\db\ActiveQuery
{
	use ActiveQueryTrait;
}