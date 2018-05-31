<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:57
 */

namespace yozh\base\controllers;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;

class DefaultController extends Controller
{
	public $_viewPath;
	
	/*
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => [ '@' ],
					],
				],
			],
		];
	}
	*/
	
	/**
	 * @param $primaryKey
	 * @return null|static
	 * @throws NotFoundHttpException
	 */
	protected function _findModel( $condition, $modelClass = null, $one = true )
	{
		if( !(is_string( $modelClass ) && class_exists( $modelClass )) ) {
			
			throw new \yii\base\InvalidParamException( 'It\'s have to be set a defaultModelClass() or $modelClass' );
			
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
	
	protected function _responseJson( $data_type, $value = null )
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		
		if ( is_array($data_type)  ){ //
			return [ 'result' => $data_type ];
		}
		else{ //
			return [ 'result' => [ $data_type  => $value] ];
		}
		
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
	
}
