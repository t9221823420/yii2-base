<?php

namespace yozh\base;

use Yii;
use \yii\base\Module as BaseModule;

abstract class Module extends BaseModule
{
	public function createController( $route )
	{
		
		// try to resolve ambiguity of controller and module names and return $app controller first
		if( ( $controller = \Yii::$app->createControllerByID( $this->id ) ) && $controller->createAction( $route ) ) {
			
			return [ $controller, $route ];
			
		}
		else if(
			( $controller = parent::createController( $route ) )
			|| ( $controller = parent::createController( $this->defaultRoute . '/' . trim( $route, '/' ) ) )
		) {
			
			return $controller;
			
		}
		
		return false;
		
	}
	
	public static function t( $category, $message, $params = [], $language = null )
	{
		return Yii::t( $category, $message, $params, $language );
	}
}
