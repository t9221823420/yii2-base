<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 28.03.2018
 * Time: 0:25
 */

namespace yozh\base\components;

use Yii;

class UrlManager extends \yii\web\UrlManager
{
	public function createUrl( $params )
	{
		return parent::createUrl( $params );
	}
	
}