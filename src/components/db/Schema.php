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
		
		static::$_instance->typeMap['enum'] = static::TYPE_ENUM;
		static::$_instance->typeMap['set']  = static::TYPE_SET;
		
	}
	
	static public function initSi()
	{
	
	}
	
	static public function getTypesList()
	{
		
		return static::getTypeMap( true );
		
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
	

	
	static public function getTypeMap( $keys = false )
	{
		if( null === self::$_instance ) {
			self::$_instance = new self();
		}
		
		$typeMap = static::$_instance->typeMap;
		
		return $keys ? array_keys( $typeMap ) : $typeMap;
	}
	
	protected function loadTableSchema( $name )
	{
		parent::loadTableSchema( $name );
	}
}