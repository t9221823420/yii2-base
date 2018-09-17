<?php

namespace yozh\base;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\base\ViewContextInterface;
use yozh\base\traits\ViewContextTrait;

abstract class Module extends BaseModule implements BootstrapInterface, ViewContextInterface
{
	use ViewContextTrait;
	
	public $_viewPath;
	
	public static function t( $category, $message, $params = [], $language = null )
	{
		return Yii::t( $category, $message, $params, $language );
	}
	
	public function createController( $route )
	{
		
		// try to resolve ambiguity of controller and module names and return $app controller first
		if( ( $controller = \Yii::$app->createControllerByID( $this->id ) ) && $controller->createAction( $route ) ) {
			return [ $controller, $route ];
		}

		elseif( $controller = parent::createController( $route ) ){
			return $controller;
		}
		
		// for cases such as /module[/action] -> /module/controllers/DefaultController[/action]
		else if( $controller = parent::createController( $this->defaultRoute . '/' . trim( $route, '/' ) ) ) {
			return $controller;
		}
		
		// for cases such  as /module/subfolder -> /module/controllers/subfolder/DefaultController/DefaultAction [index]
		else if( $controller = parent::createController( trim( $route, '/' ) . '/' . $this->defaultRoute ) ) {
			return $controller;
		}
		
		return false;
		
	}
	
	public function bootstrap( $app )
	{
		if( $app instanceof \yii\console\Application ) {
			$this->controllerNamespace = ( new \ReflectionObject( $this ) )->getNamespaceName() . '\commands';
		}
	}
	
	
}