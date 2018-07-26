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
		
		if( $searchModelClass = $this->controller::defaultSearchModelClass() ) {
			$searchModel  = new $searchModelClass;
			$dataProvider = $searchModel->search( Yii::$app->request->queryParams );
		}
		else {
			
			$searchModel = new $defaultModelClass;
			
			$dataProvider = new ActiveDataProvider( [
				'query' => $searchModel::find(),
			] );
			
		}
		
		return [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		];
	}
	
	
	public function run()
	{
		return $this->controller->render( $this->id, $this->process() );
	}
}