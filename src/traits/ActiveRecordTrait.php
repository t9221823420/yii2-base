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
use yii\db\ActiveRecord;
use yii\db\Query;
use yozh\base\components\db\Reference;
use yozh\base\models\BaseActiveQuery as ActiveQuery;

trait ActiveRecordTrait
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
	
	public static function references( $asArray = false, bool $refresh = false )
	{
		static $references;
		
		if( is_null( $references ) || $refresh ) {
			
			foreach( static::shemaReferences() as $fkName => $reference ) {
				
				$refTable = array_shift( $reference );
				
				$params = [
					'name'     => $fkName,
					'table'    => static::getRawTableName(),
					'refTable' => $refTable,
					'link'     => $reference,
				];
				
				if( $asArray ) {
					$references[ $fkName ] = $params;
				}
				else {
					$references[ $fkName ] = new Reference( $params );
				}
				
			}
		}
		
		return $references;
	}
	
	public static function shemaReferences( bool $refresh = false )
	{
		static $shemaReferences;
		
		if( is_null( $shemaReferences ) || $refresh ) {
			$shemaReferences = static::getTableSchema()->foreignKeys;
		}
		
		return $shemaReferences;
	}
	
	public static function shemaColumns()
	{
		$columns = static::getTableSchema()->columns;
		
		foreach( $columns as $name => $config ) {
			if( preg_match( '/(?<type>[a-z]+)[\(]{0,}(?<size>\d*)/', $config->dbType, $matches ) ) {
				
				if( $matches['type'] == 'tinyint' && $matches['size'] == 1 ) { //boolean
					$config->type    = 'integer';
					$config->phpType = 'boolean';
				}
				
				else if( $matches['type'] == 'enum' ) {
					$config->phpType = 'array';
					$config->dbType  = 'enum';
				}
				
				else if( $matches['type'] == 'set' ) {
					
					/**
					 * @todo temporary hack
					 */
					if( preg_match_all( "/'[^']*'/", $config->dbType, $values ) ) {
						foreach( $values[0] as $i => $value ) {
							$values[ $i ] = trim( $value, "'" );
						}
						$config->enumValues = $values;
					}
					
					$config->phpType = 'array';
					$config->dbType  = 'set';
				}
				
			}
		}
		
		return $columns;
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
	
	public function getAttributeReferences( string $attribute, bool $refresh = false )
	{
		static $referencesByAttributes = [];
		
		if( !array_key_exists( $attribute, $referencesByAttributes ) || $refresh ) {
			
			foreach( $this->references() as $fkName => $Reference ) {
				
				if( array_key_exists( $attribute, $Reference->link ) ) {
					$referencesByAttributes[ $attribute ][ $fkName ] = $Reference;
				}
				
			}
			
		}
		
		return $referencesByAttributes[ $attribute ] ?? false;
	}
	
	public function getAttributeReferenceItems( $attributeName, $mixed, $refCondition = [] )
	{
		// supposed to be FK name
		if( is_string( $mixed ) ) {
			
			if( !( $Reference = $this->references()[ $mixed ] ?? false ) ) {
				return false;
			}
			
		}
		else if( $mixed instanceof Reference ) {
			$Reference = $mixed;
		}
		else {
			throw new \yii\base\InvalidParamException( "\$mixed have to be an instance of " . Reference::class );
		}
		
		$refModelClass = $Reference->refModelClass;
		
		$refAttributes = Yii::$app->db->getSchema()->getTableSchema( $Reference->refTable )->columns;
		
		if( isset( $refAttributes['name'] ) ) {
			$refLabel = 'name';
		}
		else if( isset( $refAttributes['title'] ) ) {
			$refLabel = 'title';
		}
		else {
			$refLabel = $Reference->link[ $attributeName ];
		}
		
		if( $refModelClass && ( new \ReflectionClass( $refModelClass ) )->implementsInterface( ActiveRecordInterface::class ) ) {
			
			//$condition, $key, $value, $indexBy, $orderBy
			$refItems = $refModelClass::getList( $refCondition, $reference[ $attributeName ], $refLabel, $reference[ $attributeName ] );
			
		}
		
		else {
			
			if( $refGetter = $Reference->getter
				&& $activeQuery = $Model->$relationGetter()
			) {
				
				$refQuery = clone $activeQuery;
				
				// reset ActiveQuery to simple Query
				$refQuery->primaryModel = null;
			}
			else {
				$refQuery = ( new Query() );
			}
			
			$link = $Reference->link;
			
			$refQuery->select( [ $refLabel ] + array_values( $link ) )
			         ->from( $Reference->refTable )
			         ->andWhere( $refCondition )
			;
			
			$refItems = $refQuery->indexBy( reset( $link ) )->column();
			
		}
		
		return $refItems ?? [];
	}
	
	public function rules( $rules = [], $update = false )
	{
		return $rules;
	}
	
	public function emptyPrimaryKey(): ActiveRecord
	{
		$this->setAttributes( array_fill_keys( $this->primaryKey(), null ), false );
		
		return $this;
	}
	
	public function setIsNewRecord( $setAsNewRecord = true )
	{
		$this->emptyPrimaryKey();
		parent::setIsNewRecord( $setAsNewRecord );
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
		
		if( $only ) {
			$names = array_intersect( $only, $names );
		}
		
		if( $except ) {
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
	
	public function readOnlyAttributes( ?array $attributes = [] ): array
	{
		return array_unique( array_merge( $attributes, [
			'id',
		] ) );
		
	}
	
	public function isReadOnlyAttribute( string $name ): bool
	{
		return in_array( $name, $this->readOnlyAttributes() );
	}
	
	public function resetAttribute( $name )
	{
		if( $this->getOldAttribute( $name ) != $this->getAttribute( $name ) ) {
			$this->setAttribute( $name, $this->getOldAttribute( $name ) );
		}
	}
	
	
}