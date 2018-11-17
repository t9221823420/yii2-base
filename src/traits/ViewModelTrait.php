<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 31.03.2018
 * Time: 1:52
 */

namespace yozh\base\traits;

use yozh\base\models\BaseActiveQuery as ActiveQuery;

trait ViewModelTrait
{
	public static function find()
	{
		$class = static::class . 'Query';
		
		if( class_exists( $class ) ) {
			return new $class( static::class );
		}
		else {
			return new ActiveQuery( static::class );
		}
		
	}
	
}