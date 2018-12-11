<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 31.03.2018
 * Time: 1:52
 */

namespace yozh\base\traits;

trait ObjectTrait
{
	/**
	 * Get list of prefixed class constants OR all if $prefix not set
	 *
	 * @param string $prefix
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getConstants( $prefix = '', $combineValues = false )
	{
		$list = ( new \ReflectionClass( static::class ) )->getConstants();
		
		foreach( $list as $key => $const ) {
			if( strpos( $key, $prefix ) !== 0 ) {
				unset( $list[ $key ] );
			}
		}
		
		return $combineValues
			? array_combine( $list, $list )
			: $list;
	}
	
	public static function className( $short = false )
	{
		$class = get_called_class();
		
		return $short ? ( new\ReflectionClass( $class ) )->getShortName() : $class;
	}
	
	public static function namespace( )
	{
		$class = get_called_class();
		
		return ( new\ReflectionClass( $class ) )->getNamespaceName();
	}
}