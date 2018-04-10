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
	public static function getConstants( $prefix = '' )
	{
		$list = ( new \ReflectionClass( static::class ) )->getConstants();
		
		foreach( $list as $key => $const ) {
			if( strpos( $key, $prefix ) === false ) {
				unset( $list[ $key ] );
			}
		}
		
		return $list;
	}
}