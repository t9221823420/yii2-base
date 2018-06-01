<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 16:39
 */

namespace yozh\base\traits\actions;

use yozh\base\interfaces\ActiveRecordInterface;
use yozh\base\models\BaseModel as ActiveRecord;
use yozh\crud\interfaces\CrudInterface;

trait ActionModelGetListTrait
{
	public function actionSelectGetList()
	{
		
		if( !$this instanceof CrudInterface ) {
			throw new \yii\base\InvalidCallException( "Object must implement " . CrudInterface::class );
		}
		
		$modelClass = static::defaultModelClass();
		
		if( !( new \ReflectionClass( $modelClass ) )->implementsInterface( ActiveRecordInterface::class ) ) {
			throw new \yii\base\InvalidCallException( "Object must implement " . ActiveRecordInterface::class );
		}
		
		$output = '';
		
		if( $list = $modelClass::getList( $value ) ) {
			
			$output = '<option>' . Module::t( 'module', 'Select …' ) . '</option>';
			
			foreach( $list as $optionValue => $optionLabel ) {
				$output .= "<option value=\"$optionValue\">$optionLabel</option>'";
			}
			
		}
		
		return $output;
		
	}
}