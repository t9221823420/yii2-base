<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 09.04.2018
 * Time: 9:43
 */

namespace yozh\base\components;

class Model extends \yii\base\Model
{
	protected $_attributes;
	
	public function __get( $name )
	{
		if( array_key_exists( $name, (array)$this->_attributes ) ) {
			return $this->_attributes[ $name ];
		}
		else {
			return parent::__get( $name );
		}
	}
	
	public function __set( $name, $value )
	{
		if( array_key_exists( $name, (array)$this->_attributes ) ) {
			$this->_attributes[ $name ] = $value;
		}
		else {
			parent::__set( $name, $value );
		}
	}
	
	public function attributes()
	{
		$names = parent::attributes();
		
		return $names + array_keys( (array)$_attributes );
	}
}