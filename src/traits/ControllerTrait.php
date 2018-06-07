<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.06.2018
 * Time: 13:53
 */

namespace yozh\base\traits;

use Yii;
use yii\web\Response;

trait ControllerTrait
{
	public $_viewPath;
	
	/**
	 * @param $primaryKey
	 * @return null|static
	 * @throws NotFoundHttpException
	 */
	protected function _findModel( $condition, $modelClass = null, $one = true )
	{
		if( !(is_string( $modelClass ) && class_exists( $modelClass )) && !method_exists($this, 'defaultModelClass')) {
			
			throw new \yii\base\InvalidParamException( 'It\'s have to be set a defaultModelClass() or $modelClass' );
			
		}
		
		if( !$modelClass ){
			$modelClass = $this->defaultModelClass();
		}
		
		if( $one && ( $Model = $modelClass::findOne( $condition ) ) !== null ) {
			return $Model;
		}
		else{
			
			$modelCollection = $modelClass::findAll( $condition );
			
			if( count($modelCollection) ){
				return $modelCollection;
			}
			
		}
		
		throw new \yii\web\NotFoundHttpException();
		
	}
	
	protected function _findModels( $condition, $modelClass = null )
	{
		return $this->_findModel( $condition, $modelClass, false);
	}
	
	/*
	 * for backward compatibility
	 */
	protected function findModel( $id )
	{
		return $this->_findModel( $id );
	}
	
	public function getViewPath()
	{
		if ($this->_viewPath === null) {
			
			$path = parent::getViewPath();
			
			if( !is_dir( $path ) && $parentPath = $this->module->getParentPath( 'views' . DIRECTORY_SEPARATOR . $this->id )) {
				
				$path = $parentPath;
				
			}
			
			$this->_viewPath = $path;
			
		}
		
		return $this->_viewPath;
	}
	
	public function getParentViewPath()
	{
	
	}
	
	protected function _render( $view, $params = [] )
	{
		if( Yii::$app->request->isAjax ) {
			return $this->renderAjax( $view, $params );
		}
		else {
			return $this->render( $view, $params );
		}
	}
	
	
	public function responseJson( $data_type, $value = null )
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		
		if ( is_array($data_type)  ){
			return [ 'result' => $data_type ];
		}
		else{
			return [ 'result' => [ $data_type  => $value] ];
		}
		
	}
	
	
	
}