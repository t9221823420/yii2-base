<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 23.03.2018
 * Time: 13:06
 */

namespace yozh\base\components;

class ErrorHandler extends \yii\web\ErrorHandler
{
	public $callStackItemView = '@yozh/base/views/errorHandler/callStackItem.php';
}