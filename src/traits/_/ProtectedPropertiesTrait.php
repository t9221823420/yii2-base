<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 09.12.2018
 * Time: 22:13
 */

namespace yozh\base\traits;

trait ProtectedPropertiesTrait
{
	public function __construct( array $config = [] )
	{
		foreach( $config as $key => $value ) {
			if( property_exists( static::class, $key ) ) {
				$this->{'_' . $key} = $value;
				unset( $config[ $key ] );
			}
		}
		
		parent::__construct( $config );
	}
	
	public function __get( $name )
	{
		if( property_exists( static::class, $name ) ) {
			return $this->{'_' . $name};
		}
		else {
			return parent::__get( $name );
		}
	}
}