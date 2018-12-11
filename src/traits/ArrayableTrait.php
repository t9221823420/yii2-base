<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.12.2018
 * Time: 18:34
 */

namespace yozh\base\traits;

trait ArrayableTrait
{
	use ObjectTrait;
	
	/**
	 * you need to define $_traversable
	 */
	//protected $_traversable;
	
	public function __get( $name )
	{
		if( in_array( $name, $this->_traversable ) ) {
			return $this->{"_$name"};
		}
		else {
			return parent::__get( $name );
		}
		
	}
	
	public function __set( $name, $value )
	{
		switch( $name ) {
			
			case '_':
				
				break;
			
			default:
				
				return parent::__set( $name, $value );
		}
		
	}
	
	public function offsetSet( $offset, $value )
	{
		$this->__set( $offset, $value );
	}
	
	public function offsetExists( $offset )
	{
		return in_array( $offset, $this->_traversable );
	}
	
	public function offsetUnset( $offset )
	{
		$this->__set( $offset, null );
	}
	
	public function offsetGet( $offset )
	{
		return $this->__get( $offset );
	}
	
	public function jsonSerialize( ?array $only = [], ?array $except = [] )
	{
		$names = $this->_traversable ?? [];
		
		if( $only ) {
			$names = array_intersect( $names, array_unique( $only ) );
		}
		
		if( $except ) {
			$names = array_diff( $names, array_unique( $except ) );
		}
		
		$result = [];
		
		foreach( $names as $name ) {
			$result[ $name ] = $this->{"_$name"};
		}
		
		return $result;
	}
}