<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 16:39
 */

namespace yozh\base\traits\actions;

use yozh\base\models\BaseModel as ActiveRecord;

trait ActionUpdateAttributeTrait
{
	public function actionUpdateAttribute( $id, $attribute, $value )
	{
		/**
		 * @var ActiveRecord $model
		 */
		$model = $this->findModel( (int)$id );
		
		if( $model->hasAttribute( $attribute ) ) {
			
			$model->setAttribute( $attribute, (int)$value );
			
			if( $model->validate( $attribute ) ){
				$model->updateAttributes( [ $attribute ] );
				return $value;
			}
			
		}
		
		throw new \yii\web\NotFoundHttpException();
		
	}
}