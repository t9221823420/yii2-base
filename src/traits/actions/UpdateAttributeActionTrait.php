<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 16:39
 */

namespace yozh\base\traits\actions;

use yii\db\ActiveRecordInterface;
use yozh\base\interfaces\controllers\ControllerInterface;
use yozh\base\models\BaseModel as ActiveRecord;

trait UpdateAttributeActionTrait
{
	public function run( $id, $attribute, $value, $modelClass = null )
	{
		
		/**
		 * @var ActiveRecord $Model
		 */
		if( $modelClass && class_exists( $modelClass ) && ( new \ReflectionClass( $modelClass ) )->implementsInterface( ActiveRecordInterface::class ) ) {
			
			if( !$Model = $modelClass::findOne( $id )) {
				throw new \yii\web\NotFoundHttpException();
			}
			
		}
		elseif( $this instanceof ControllerInterface ){
			$Model = $this->findModel( $id );
		}
		
		if( $Model->hasAttribute( $attribute ) ) {
			
			$Model->setAttribute( $attribute, $value );
			
			if( $Model->validate( $attribute ) ){
				$Model->updateAttributes( [ $attribute ] );
				return $value;
			}
			
		}
		
		throw new \yii\web\NotFoundHttpException();
		
	}
}