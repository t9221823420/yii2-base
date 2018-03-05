<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 05.03.2018
 * Time: 9:40
 */

namespace yozh\base\components;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
	
	const DEFAULTS_MODE_ADD    = 'mode.add';
	const DEFAULTS_MODE_FILTER = 'mode.filter';
	
	public static function defaults( $params, $defaults = [], $mode = self::DEFAULTS_MODE_ADD )
	{
		if( is_array($params) ){
			
			switch ( $mode ) {
			
			    case self::DEFAULTS_MODE_ADD :
				
				    return array_replace( $defaults, array_intersect_key( $params, $defaults ) );
			
			    case self::DEFAULTS_MODE_FILTER :
				
				    return array_intersect_key( $params, $defaults );
			
			}
			
		}
		
		return $params;
		
	}
}