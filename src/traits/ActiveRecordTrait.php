<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 21.04.2018
 * Time: 12:32
 */

namespace yozh\base\traits;

use yozh\base\models\BaseActiveQuery as ActiveQuery;

trait ActiveRecordTrait
{
	/**
	 * Return records as Array id => column for dropdowns
	 *
	 * @param $key
	 * @param $value
	 * @param bool $orderBy
	 * @return array
	 */
	public static function getListQuery( $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true )
	{
		$key = $key ?? static::primaryKey()[0];
		$value = $value ?? $key;
		
		$query = static::find()
		               ->select( [ $key, $value ] )
		               ->andFilterWhere( $condition )
		;
		
		if( $orderBy === true ) { //
			$query->orderBy( $value );
		}
		else if( $orderBy !== false ) { //
			$query->orderBy( $orderBy );
		}
		
		if( $indexBy === true ) { //
			$query->indexBy( $key );
		}
		else if( $indexBy !== false ) { //
			$query->indexBy( $indexBy );
		}
		
		return $query;
	}
	
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