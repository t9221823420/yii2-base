<?php

namespace yozh\base;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\base\ViewContextInterface;
use yozh\base\traits\ObjectTrait;
use yozh\base\traits\ViewContextTrait;

abstract class Module extends BaseModule implements BootstrapInterface, ViewContextInterface
{
	use ObjectTrait, ViewContextTrait;
	
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
		
		/**
		 * /module/controller/action (standart)
		 */
		else if( $controller = parent::createController( $route ) ) {
			return $controller;
		}
		
		/**
		 * /module[/action] -> /module/controllers/DefaultController[/action]
		 *
		 */
		else if( $controller = parent::createController( $this->defaultRoute . '/' . trim( $route, '/' ) ) ) {
			return $controller;
		}
		
		/**
		 * /module/subfolder -> /module/controllers/subfolder/DefaultController/DefaultAction [index]
		 * /ysell/product -> ysell/product/default/index
		 * module has subfolders with default controllers
		 */
		else if( $controller = parent::createController( trim( $route, '/' ) . '/' . $this->defaultRoute ) ) {
			return $controller;
		}
		
		/**
		 * /module/subfolder/action -> /module/controllers/subfolder/DefaultController/action
		 * /ysell/product/search -> ysell/product/default/search
		 * insert defaultRoute instead of last '/'
		 */
		else if( ( $testRoute = preg_replace( '/\/(?=[a-zA-Z]+[\w-]+(?:\?|$))/', "/{$this->defaultRoute}/", $route ) )
			&& $controller = parent::createController( $testRoute )
		) {
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