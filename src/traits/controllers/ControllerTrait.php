<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.06.2018
 * Time: 13:53
 */

namespace yozh\base\traits\controllers;

use Yii;
use yii\web\Response;

trait ControllerTrait
{
	public $viewPath;
	
	public $jsId;
	
	public function beforeAction( $action )
	{
		$this->jsId = '_' . uniqid();
		
		return parent::beforeAction( $action ); // TODO: Change the autogenerated stub
	}
	
	
	public function findModel( $id ): ?ActiveRecord
	{
		return $this->_findModel( $id );
	}
	
	public function getViewPath()
	{
		if( $this->viewPath === null ) {
			
			$path = parent::getViewPath();
			
			if( !is_dir( $path ) && $parentPath = $this->module->getParentPath( 'views' . DIRECTORY_SEPARATOR . $this->id ) ) {
				
				$path = $parentPath;
				
			}
			
			$this->viewPath = $path;
			
		}
		
		return $this->viewPath;
	}
	
	/*
	 * for backward compatibility
	 */
	
	public function getParentViewPath()
	{
	
	}
	
	public function render( $view, $params = [] )
	{
		if( Yii::$app->request->isAjax ) {
			return $this->renderAjax( $view, $params );
		}
		else {
			return parent::render( $view, $params );
		}
	}
	
	public function responseJson( $data_type, $value = null )
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		
		if( is_array( $data_type ) ) {
			return [ 'result' => $data_type ];
		}
		else {
			return [ 'result' => [ $data_type => $value ] ];
		}
		
	}
	
	/**
	 * @param $primaryKey
	 * @return null|static
	 * @throws NotFoundHttpException
	 */
	protected function _findModel( $condition, $modelClass = null, $one = true ): ?ActiveRecord
	{
		if( !$modelClass ) {
			
			if( method_exists( $this, 'defaultModelClass' ) ) {
				$modelClass = $this::defaultModelClass();
			}
			else {
				throw new \yii\base\InvalidParamException( '$modelClass not set' );
			}
			
		}
		
		if( $one && ( $Model = $modelClass::findOne( $condition ) ) !== null ) {
			return $Model;
		}
		else {
			
			$modelCollection = $modelClass::findAll( $condition );
			
			if( count( $modelCollection ) ) {
				return $modelCollection;
			}
			
		}
		
		throw new \yii\web\NotFoundHttpException();
		
	}
	
	protected function _findModels( $condition, $modelClass = null ): ?array
	{
		return $this->_findModel( $condition, $modelClass, false );
	}
	
}