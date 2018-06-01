<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 21.04.2018
 * Time: 12:32
 */

namespace yozh\base\traits;

use Yii;
use yii\db\ActiveRecord;
use yozh\base\models\BaseActiveQuery as ActiveQuery;

trait ActiveRecordTrait
{
	
	public static function getRawTableName()
	{
		return Yii::$app->db->schema->getRawTableName( static::tableName() );
	}
	
	/**
	 * Return records as Array id => column for dropdowns
	 *
	 * @param $key
	 * @param $value
	 * @param bool $orderBy
	 * @return array
	 */
	public static function getListQuery( $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true, $alias = null )
	{
		
		if( !$alias && is_subclass_of( get_called_class(), ActiveRecord::class ) ) {
			$table = Yii::$app->db->schema->getRawTableName( ( get_called_class() )::tableName() );
		}
		else if( $alias ) {
			$table = $alias;
		}
		else {
			$table = '';
		}
		
		!$table ?: $table .= '.';
		
		$key   = $key ?? static::primaryKey()[0];
		$value = $value ?? $key;
		
		$query = static::find()
		               ->select( [
			               $table . $value,
			               $table . $key,
		               ] )
		               ->andFilterWhere( $condition )
		;
		
		if( $orderBy === true ) { //
			$query->orderBy( $table . $value );
		}
		else if( $orderBy !== false ) { //
			$query->orderBy( $table . $orderBy );
		}
		
		if( $indexBy === true ) { //
			$query->indexBy( $key );
		}
		else if( $indexBy !== false ) { //
			$query->indexBy( $indexBy );
		}
		
		return $query;
	}
	
	public static function getList( $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true )
	{
		return static::_getList( $condition, $key, $value, $indexBy, $orderBy );
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
	
	protected static function _getList( $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true )
	{
		
		$attributes = static::getTableSchema()->columns;
		
		if( !$value && isset( $attributes['name'] ) ) {
			$value = 'name';
		}
		if( !$value && isset( $attributes['title'] ) ) {
			$value = 'title';
		}
		
		return static::getListQuery( $condition, $key, $value, $indexBy, $orderBy )->column();
	}
	
	/**
	 * Returns attribute values.
	 * @param array $names list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @param array $except list of attributes whose value should NOT be returned.
	 * @return array attribute values (name => value).
	 */
	public function getRawAttributes( $names = null, $except = [] )
	{
		$values = [];
		
		if( $names === null ) {
			$names = $this->attributes();
		}
		
		foreach( $names as $name ) {
			$values[ $name ] = $this->getAttribute( $name );
		}
		
		foreach( $except as $name ) {
			unset( $values[ $name ] );
		}
		
		return $values;
	}
	
	public static function getShemaReferences()
	{
		return static::getTableSchema()->foreignKeys;
	}
}