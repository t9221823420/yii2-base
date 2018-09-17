<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 08.05.2018
 * Time: 15:22
 */

namespace yozh\base\traits\patterns;

trait DecoratorTrait
{
	
	protected $_object;
	
	/* @todo - доделать когда будет необходимость
	public static function __callStatic( string $name, array $params )
	{
		return call_user_func_array( [ LxModule::class, $name ], $params );
	}
	*/
	
	public function __call( $name, $params )
	{
		return call_user_func_array( [ &$this->_object, $name ], $params );
	}
	
	public function __get( $name )
	{
		return $this->_object ? $this->_object->$name : parent::__get( $name );
	}
	
	public function __set( $name, $value )
	{
		$this->_object ? $this->_object->$name : parent::__set( $name, $value );
	}
}