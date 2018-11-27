<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 27.11.2018
 * Time: 22:00
 */

namespace yozh\base\traits\controllers;

use yii\helpers\Console;

trait ConsoleControllerTrait
{
	public function confirm( $message, $default = true )
	{
		if( $this->interactive ) {
			return Console::confirm( $message, $default );
		}
		
		return $default;
	}
}