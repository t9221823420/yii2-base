<?php

namespace yozh\base;

use Yii;
use yii\base\BootstrapInterface;
use yozh\base\controllers\MigrateController;
use yozh\base\components\UrlRule;

abstract class Bootstrap implements BootstrapInterface
{
	
	public function bootstrap( $app )
	{
		
		$moduleId = ( ( new \ReflectionObject( $this ) )->getNamespaceName() . '\Module' )::MODULE_ID;
		
		$app->getUrlManager()->addRules( [
			
			// remove module/default/action
			[
				'class'   => UrlRule::classname(),
				'pattern' => $moduleId . '/<action:[\w\-]+>',
				'route'   => $moduleId . '/<action>',
			],
			[
				'class'   => UrlRule::classname(),
				'pattern' => $moduleId . '/<action:[\w\-]+>',
				'route'   => $moduleId . '/default/<action>',
			],
			
			$moduleId => $moduleId . '/default/index',
		
		], false )
		;
		
		/*
		$app->setModule( $moduleId, 'yozh\\' . $moduleId . '\Module' );
		
		if( ( new \ReflectionObject( $app ) )->getNamespaceName() == 'yii\console' ) {
			
			if( !isset( $app->controllerMap['migrate']['migrationPath'] ) ) {
				$app->controllerMap['migrate'] = [
					'class'         => MigrateController::class,
					'migrationPath' => [],
				];
			}
			
			foreach( $app->extensions as $name => $extension ) {
				
				$alias = key( $extension['alias'] );
				$path  = reset( $extension['alias'] ) . '/migrations';
				
				if( strpos( $alias, '@yozh' ) === 0 && is_dir( $path ) ) {
					$app->controllerMap['migrate']['migrationPath'][ $name ] = $path;
				}
			}
		}
		*/
	}
	
}