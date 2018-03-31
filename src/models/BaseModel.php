<?php

namespace yozh\base\models;

use Yii;
use yozh\base\traits\Utils;

abstract class BaseModel extends \yii\db\ActiveRecord
{
	use Utils;
	
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
	
}
