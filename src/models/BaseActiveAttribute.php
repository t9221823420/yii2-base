<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.12.2018
 * Time: 17:04
 */

namespace yozh\base\models;

use yii\base\ArrayableTrait;
use yii\base\DynamicModel;
use yozh\base\components\Arrayable;
use yozh\base\traits\ActiveAttributeTrait;

class BaseActiveAttribute extends Arrayable
{
	use ActiveAttributeTrait;
	
	const SHORT_FORMAT_PATTERN = '/^(?<attribute>[^:]+)(?:\:(?<format>\w*))?(?:\:(?<label>.*))?$/';
	
	const SHORT_FORMAT_WRONG_PATTERN_MESSAGE = 'The attribute must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"';
	
	const DEFAULT_FORMAT = 'text';
	
	public function __construct( array $config = [] ) {
		
		parent::__construct( $config );
		
		$this->_format = self::DEFAULT_FORMAT;
	}
	
	
	/*
	public static function test()
	{
		$attribute = new self( [
			'name'     => 'foo',
			'visible'  => 'bar',
			'readOnly' => 0,
		] );
		
		$foo = $attribute['attribute'];
		
		$attribute['attribute'] = 'bar';
		
		$foo = $attribute['name'];
		
		$foo = isset( $attribute['label'] );
		
		$attribute['visible']  = 0;
		$attribute['readOnly'] = null;
		$foo = isset( $attribute['visible'] );
		$foo = isset( $attribute['readOnly'] );
		
		unset( $attribute['attribute'] );
		$foo = isset( $attribute['name'] );
		
		$attribute['attribute'] = 'attribute_name';
		
		$foo = $attribute->getLabel();
		
		$foo = $attribute->toArray();
		
		$attribute['bar'] = 'foo';
	}
	*/
	
}