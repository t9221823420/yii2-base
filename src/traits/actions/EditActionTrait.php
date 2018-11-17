<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.06.2018
 * Time: 19:29
 */

namespace yozh\base\traits\actions;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yozh\base\models\BaseActiveRecord as ActiveRecord;

trait EditActionTrait
{
	
	public function process( ActiveRecord $Model = null, bool $clone = false )
	{
		/**
		 * @var ActiveRecord $defaultModelClass
		 * @var array $primaryKey
		 */
		$defaultModelClass = $this->controller::defaultModelClass(); // like app\models\Model
		$primaryKey        = array_intersect_key( $_GET, array_flip( $defaultModelClass::primaryKey() ) ); // because of composite key
		
		// Event AFTER_LOAD_PRIMARY_KEY
		
		if( !$Model ) {
			
			if( empty( $primaryKey ) ) { // create
				$Model = new $defaultModelClass();
				// Event AFTER_NEW_MODEL
			}
			else if( ( $Model = $defaultModelClass::findOne( $primaryKey ) ) !== null ) { // update
				// Event AFTER_FIND_MODEL
			}
			else {
				throw new NotFoundHttpException( Yii::t( 'app', 'The requested page does not exist.' ) );
			}
			
		}
		
		// Event BEFORE_PRELOAD_MODEL
		
		/**
		 * Leave it here because we may have to load some initial data to new model (for example parent_id)
		 * and if there is not POST data the model would not be saved
		 * This load only safe attributes. PrimaryKey is usualy unsafe and for Clone (for example) wouldn't be overrided
		 */
		$Model->load( Yii::$app->request->get(), '' );
		
		// Event AFTER_PRELOAD_MODEL
		
		if( $clone ){
			
			$Model->emptyPrimaryKey();
			$Model->setOldAttributes( null );
			
			// Event CLONE_RESET_INITIAL_MODEL
			
		}
		
		// Event BEFORE_LOAD_MODEL
		
		/**
		 * for Load only POST data
		 */
		if( $Model->load( Yii::$app->request->post() ) ) {
			
			// Event AFTER_LOAD_MODEL
			
			// @todo к load прикрутить конвертер типов на основании rules
			
			if( $Model->validate() ) {
				
				// Event AFTER_VALIDATE_MODEL
				
				if( $Model->save( false ) ) {
					
					Yii::$app->session->setFlash( 'kv-detail-success', Yii::t( 'app', 'Record saved successfully' ) );
					
					// Event AFTER_SAVE_RECORD
					
					return true;
					
				}
				
			}
		}
		
		// Event BEFORE_RENDER
		
		return [
			'Model' => $Model,
		];
		
	}
	
	public function run( ActiveRecord $Model = null, bool $clone = false )
	{
		
		$result = $this->process( $Model, $clone );
		
		if( $result === true ) {
			
			if( Yii::$app->request->isAjax ) {
				
				$Response = Yii::$app->getResponse();
				
				$Response->format = Response::FORMAT_JSON;
				$Response->data   = true;
				
				return $Response;
			}
			else {
				return $this->controller->redirect( [ 'index' ] );
			}
			
		}
		elseif( $result instanceof Response ){
			
			return $result;
			
		}
		else {
			
			return $this->controller->render( $this->id, $result );
			
		}
		
		/*
		if( $result instanceof Response ) { //
			return $result;
		}
		else if( $result ) { //
			
			if( $result instanceof \yii\db\ActiveRecord ) {
				
				return $this->render( 'create', [
					'model' => $result,
				] );
				
			}
			
			throw new \yii\base\ErrorException( get_class( $result ) . " have to be extends \yii\db\ActiveRecord" );
			
		}
		else {
			throw new \yii\web\NotFoundHttpException();
		}
		*/
		
	}
}