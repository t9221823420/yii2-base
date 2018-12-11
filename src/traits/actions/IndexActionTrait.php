<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.06.2018
 * Time: 20:33
 */

namespace yozh\base\traits\actions;

use Yii;
use yii\data\ActiveDataProvider;

trait IndexActionTrait
{
	
	public function process()
	{
		$defaultModelClass = $this->controller::defaultModelClass();
		
		if( $ModelSearchClass = $this->controller::defaultSearchModelClass() ) {
			$ModelSearch  = new $ModelSearchClass;
			$dataProvider = $ModelSearch->search( Yii::$app->request->queryParams );
		}
		else {
			
			$ModelSearch = new $defaultModelClass;
			
			$dataProvider = new ActiveDataProvider( [
				'query' => $ModelSearch::find(),
			] );
			
		}
		
		return [
			'ModelSearch'  => $ModelSearch,
			'dataProvider' => $dataProvider,
		];
	}
	
	
	public function run()
	{
		return $this->controller->render( $this->id, $this->process() );
	}
}