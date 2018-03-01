<?php

namespace yozh\base;

use Yii;
use yii\base\BootstrapInterface;
use yozh\base\components\UrlRule;

class Bootstrap implements BootstrapInterface
{
	
	public function bootstrap( $app )
	{
		
		$moduleId = ( ( new \ReflectionObject( $this ) )->getNamespaceName() . '\Module' )::MODULE_ID;
		
		
		$foo = $app->getUrlManager();
		
		$app->getUrlManager()->addRules( [
			
			// remove module/default/action
			[
				'class' => UrlRule::classname(),
				'pattern' => $moduleId . '/<action:[\w\-]+>',
				'route' => $moduleId . '/<action>'
			],
			[
				'class' => UrlRule::classname(),
				'pattern' => $moduleId . '/<action:[\w\-]+>',
				'route' => $moduleId . '/default/<action>'
			],
			
			$moduleId => $moduleId . '/default/index',
		
		], false );
		
		$app->setModule( $moduleId, 'yozh\\' . $moduleId . '\Module' );
		
	}
	
}