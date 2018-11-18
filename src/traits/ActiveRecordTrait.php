<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 21.04.2018
 * Time: 12:32
 */

namespace yozh\base\traits;

use Yii;
use yozh\base\models\BaseActiveRecord as ActiveRecord;

trait ActiveRecordTrait
{
	use DBRecordTrait, ReadOnlyAttributesTrait;
	
	public function emptyPrimaryKey(): ActiveRecord
	{
<<<<<<< HEAD
		$this->setAttributes( array_fill_keys( $this->primaryKey(), null ), false );
=======
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
		               //->andFilterWhere( $condition ?? [] ) filter не канает,потому что нельзя задавать условия типа NOT NULL
		               ->andWhere( $condition ?? [] )
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
	
	public static function getList( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true )
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
	
	public static function getShemaReferences()
	{
		return static::getTableSchema()->foreignKeys;
	}
	
	public static function getRoute( $route = 'index' )
	{
		$namespace       = ( new\ReflectionClass( static::class ) )->getNamespaceName();
		$modelClass      = ( new\ReflectionClass( static::class ) )->getShortName();
		$moduleNamespace = preg_replace( '/\\\\models/', '', $namespace );
		
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
		
		if( $moduleId && class_exists( "$moduleNamespace\\controllers\\{$modelClass}Controller" ) ) {
			$route = mb_strtolower( $modelClass ) . '/' . $route;
		}
		else if( $moduleId && class_exists( "$moduleNamespace\\controllers\\DefaultController" ) ) {
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
	
	private static function _getList( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true )
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
>>>>>>> remotes/origin/temp
		
		return $this;
	}
	
}