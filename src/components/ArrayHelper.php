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
	
	const DEFAULTS_MODE_ADD    = 'defaults_mode_add';
	const DEFAULTS_MODE_FILTER = 'defaults_mode_filter';
	
	const BY_PATH_TYPE_GET   = 'by_path_type_get';
	const BY_PATH_TYPE_SET   = 'by_path_type_set';
	const BY_PATH_TYPE_UNSET = 'by_path_type_unset';
	const BY_PATH_TYPE_ADD   = 'by_path_type_add';
	
	
	public static function setDefaults( $params, $defaults = [], $mode = self::DEFAULTS_MODE_ADD )
	{
		if( is_array( $params ) ) {
			
			switch( $mode ) {
				
				case self::DEFAULTS_MODE_ADD :
					
					return array_replace( $defaults, array_intersect_key( $params, $defaults ) );
				
				case self::DEFAULTS_MODE_FILTER :
					
					return array_intersect_key( $params, $defaults );
				
			}
			
		}
		
		return $params;
		
	}
	
	public static function setByPath( $path, &$array = [], $value )
	{
		return static::_byPath( $path, $array, self::BY_PATH_TYPE_SET, $value );
	}
	
	public static function addByPath( $path, &$array = [], $value )
	{
		return static::_byPath( $path, $array, self::BY_PATH_TYPE_ADD, $value );
	}
	
	protected static function &_byPath( $path, &$array = [], $opsType = self::BY_PATH_TYPE_GET, $value = null )
	{
		$path_ = explode( '.', $path ); //if needed
		$temp  = &$array;
		
		foreach( $path_ as $key ) {
			if( $opsType == self::BY_PATH_TYPE_UNSET && !is_array( $temp[ $key ] ) ) {
				unset( $temp[ $key ] );
				break;
			}
			else {
				$temp =& $temp[ $key ];
			}
		}
		
		switch ( $opsType ) {
		
		    case self::BY_PATH_TYPE_SET:
			    $temp = $value;
		        break;
		
		    case self::BY_PATH_TYPE_ADD:
			    $temp[] = $value;
		        break;
		
		}
		
		return $temp;
	}
	
	public static function &getByPath( $path, &$array = [] )
	{
		return static::_byPath( $path, $array );
	}
	
	public static function unsetter( $path, &$array = [] )
	{
		return static::_byPath( $path, $array, self::BY_PATH_TYPE_UNSET );
	}
	
}