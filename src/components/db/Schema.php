<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 19:38
 */

namespace yozh\base\components\db;

use yii\base\BaseObject;

class Schema extends \yii\db\mysql\Schema
{
	const TYPE_ENUM = 'enum';
	const TYPE_SET  = 'set';
	
	protected static $_instance;
	
	public function __construct( array $config = [] )
	{
		parent::__construct( $config );
		
		static::$_instance = new self();
		
		static::$_instance->typeMap[ 'enum' ] = static::TYPE_ENUM;
		static::$_instance->typeMap[ 'set' ] = static::TYPE_SET;
		
	}
	
	static public function getTypes()
	{
		return array_keys( static::$_instance->typeMap );
		
		/*
		$types = array_merge(
			( new \ReflectionClass( 'yii\db\Schema' ) )->getConstants(),
			( new \ReflectionClass( self::class ) )->getConstants()
		);
		
		$result = [];
		
		foreach( $types as $key => $type ) {
			if( strpos( $key, 'TYPE_' ) === false ) {
				unset( $types[ $key ] );
			}
		}
		
		return $types;
		*/
	}
	
	static public function getTypeMap()
	{
		return static::$_instance->typeMap;
	}
		
		protected function loadTableSchema( $name )
	{
		parent::loadTableSchema( $name );
	}
}