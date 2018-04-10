<?php

namespace yozh\base\models;

use Yii;
use yozh\base\traits\ObjectTrait;

abstract class BaseModel extends \yii\db\ActiveRecord
{
	use ObjectTrait;
	
	/**
	 * Return records as Array id => column for dropdowns
	 *
	 * @param $key
	 * @param $value
	 * @param bool $orderBy
	 * @return array
	 */
	public static function getList( $key, $value, $orderBy = true )
	{
		$key = $key ?? static::primaryKey()[0];
		
		$query = static::find()->select( [ $key, $value ] )->indexBy( $key );
		
		if( $orderBy === true ) { //
			$query->orderBy( $value );
		}
		else if( $orderBy !== false ) { //
			$query->orderBy( $orderBy );
		}
		
		return $query->column();
		
	}
	
	public static function find()
	{
		return new BaseActiveQuery( get_called_class() );
	}
	
	
}
