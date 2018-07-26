<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 26.03.2018
 * Time: 23:40
 */

namespace yozh\base\components\utils;

class Config
{
	public static function setWithClosure( $variable, $value = null, $params = [] )
	{
		/*
		$params = func_get_args();
		$value = array_shift($params);
		*/
		
		if ( $variable instanceof \Closure ) {
			return call_user_func_array( $variable, $params );
		} 
		elseif( $value ) {
			return $value;
		}
		else{
			return $variable;
		}
		
	}
}