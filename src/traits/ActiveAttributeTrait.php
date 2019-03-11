<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.12.2018
 * Time: 18:17
 */

namespace yozh\base\traits;

use yii\base\Model;
use yozh\base\components\helpers\Inflector;

trait ActiveAttributeTrait
{
	use ArrayableTrait {
		jsonSerialize as private _jsonSerialize;
	}
	
	protected $_model;
	
	protected $_name;
	
	protected $_value;
	
	protected $_format;
	
	protected $_label;
	
	protected $_visible = true;
	
	protected $_readOnly = false;
	
	protected $_traversable = [
		'attribute',
		'name',
		'value',
		'format',
		'label',
		'visible',
		'readOnly',
	];
	
	
	public function __construct( $config = [] )
	{
		if( $config['model'] ?? false && $config['model'] instanceof Model) {
			
			$this->_model = $config['model'];
			
			unset($config['model']);
			
		}
		else {
			throw new InvalidConfigException( '"model" property is not set or is not instance of ' . Model::class . ' class.' );
		}
		
		parent::__construct( $config );
	}
	
	
	public function __get( $name )
	{
		if( $name == 'attribute' ) {
			return $this->_name;
		}
		else if( in_array( $name, $this->_traversable ) ) {
			return $this->{"_$name"};
		}
		else {
			return parent::__get( $name );
		}
		
	}
	
	public function __set( $name, $value )
	{
		switch( $name ) {
			
			case 'attribute':
				
				$this->_name = $value;
				
				break;
			
			case 'value':
				
				$this->_readOnly ?: $this->_value = $value;
				
				break;
			
			case 'visible':
			case 'readOnly':
				
				$this->{"_$name"} = (bool)$value;
				
				break;
			
			case 'name':
			case 'format':
			case 'label':
				
				$this->{"_$name"} = $value;
				
				break;
			
			default:
				
				return parent::__set( $name, $value );
		}
		
	}
	
	public function getLabel()
	{
		if( !$this->_label ) {
			return Inflector::titleize( $this->_name );
		}
		else {
			return $this->_label;
		}
	}
	
	public function toArray( ?array $only = [], ?array $except = [] )
	{
		//return json_decode( json_encode( $this ), true );
		
		return $this->jsonSerialize( $only, $except );
	}
	
	public function jsonSerialize( ?array $only = [], ?array $except = [] )
	{
		$except = array_unique( array_merge( $except, [
			'attribute',
		] ) );
		
		$result = array_merge( [
			'attribute' => $this->_name,
		], $this->_jsonSerialize( $only, $except ) );
		
		return $result;
	}
}