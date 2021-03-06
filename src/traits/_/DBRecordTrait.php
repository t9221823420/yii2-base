<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 21.04.2018
 * Time: 12:32
 */

namespace yozh\base\traits;

use Yii;
use yii\base\Module;
use yozh\base\models\BaseActiveRecord as ActiveRecord;
use yozh\base\models\BaseActiveQuery as ActiveQuery;

trait DBRecordTrait
{
	
	public static function getRawTableName( $alias = null )
	{
		return $alias ?? Yii::$app->db->schema->getRawTableName( static::tableName() );
	}
	
	/**
	 * Return records as Array id => column for dropdowns
	 *
	 * @param $key
	 * @param $value
	 * @param bool $orderBy
	 * @return array
	 */
	public static function getListQuery( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true, $alias = null )
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
		               ->andFilterWhere( $condition ?? [] )
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
	
	public static function getList( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true ): array
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
	
	public static function shemaReferences()
	{
		return static::getTableSchema()->foreignKeys;
	}
	
	public static function getRoute( $route = 'index' )
	{
		$namespace  = ( new\ReflectionClass( static::class ) )->getNamespaceName();
		$modelClass = ( new\ReflectionClass( static::class ) )->getShortName();
		
		preg_match( '/(?<moduleNamespace>.*)(?:\\\\models)(?<subpath>\\\\.*)?/', $namespace, $matches );
		$moduleNamespace = $matches['moduleNamespace'] ?? '';
		$subpath         = $matches['subpath'] ?? '';
		
		$moduleId = null;
		
		foreach( Yii::$app->getModules() as $key => $module ) {
			if( $module instanceof Module && ( new\ReflectionObject( $module ) )->getNamespaceName() == $moduleNamespace ) {
				
				$moduleId = $key;
				
				//$defaultRoute = $module->defaultRoute;
				
				break;
			}
			else if( ( $module['class'] ?? false ) && strpos( $module['class'], $moduleNamespace . '\\' ) === 0 ) {
				
				$moduleId = $key;
				
				//$defaultRoute = $module['defaultRoute'] ?? 'default';
				
				break;
			}
		}
		
		if( $moduleId && class_exists( "$moduleNamespace\\controllers$subpath\\{$modelClass}Controller" ) ) {
			$route = mb_strtolower( $modelClass ) . '/' . $route;
		}
		else if( $moduleId && class_exists( "$moduleNamespace\\controllers$subpath\\DefaultController" ) ) {
			$route = 'default/' . $route;
		}
		else {
			$moduleId = null;
		}
		
		if( $moduleId ) {
			
			return $moduleId . '/' . $route;
		}
		else {
			return $route;
		}
		
	}
	
	private static function _getList( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true ): array
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
	 * Returns the list of all attribute names of the model.
	 * The default implementation will return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributes( ?array $only = null, ?array $except = null, ?bool $schemaOnly = false )
	{
		$names = array_keys( static::getTableSchema()->columns );
		
		if( !$schemaOnly ) {
			
			$class = new \ReflectionClass( $this );
			
			foreach( $class->getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
				if( !$property->isStatic() ) {
					$names[] = $property->getName();
				}
			}
			
		}
		
		$names = array_unique( $names );
		
		if( !is_null( $only ) ) {
			$names = array_intersect( $only, $names );
		}
		
		if( !is_null( $except ) ) {
			$names = array_diff( $names, $except );
		}
		
		return $names;
	}
	
	/**
	 * Returns attribute values.
	 * @param array $only list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @param array $except list of attributes whose value should NOT be returned.
	 * @return array attribute values (name => value).
	 */
	public function getRawAttributes( ?array $only = null, ?array $except = [], ?bool $schemaOnly = false )
	{
		$values = [];
		
		if( $only === null ) {
			$only = $this->attributes( $only, $except, $schemaOnly );
		}
		
		foreach( $only as $name ) {
			$values[ $name ] = $this->getAttribute( $name );
		}
		
		if( $except ) {
			$values = array_diff_key( $values, array_flip( $except ) );
		}
		
		/*
		foreach( $except as $name ) {
			unset( $values[ $name ] );
		}
		*/
		
		return $values;
	}
	
	// add return $this for chain;
	public function setAttributes( $values, $safeOnly = true )
	{
		parent::setAttributes( $values, $safeOnly ); // TODO: Change the autogenerated stub
		
		return $this;
	}
	
	
}