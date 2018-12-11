<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.12.2018
 * Time: 16:08
 */

namespace yozh\base\traits;

use Yii;
use yii\helpers\Html;
use yozh\base\models\BaseActiveAttribute as ActiveAttribute;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yozh\base\components\helpers\ArrayHelper;
use yozh\base\components\helpers\Inflector;
use yozh\base\interfaces\models\ActiveRecordInterface;

trait NormalizeAttributesTrait
{
	protected function _normalizeAttributes( $Model, array $attributes = [] )
	{
		if( empty( $attributes ) ) {
			
			if( $Model instanceof Model ) {
				$attributes = $Model->attributes();
			}
			else if( is_object( $Model ) ) {
				$attributes = $Model instanceof Arrayable ? array_keys( $Model->toArray() ) : array_keys( get_object_vars( $Model ) );
			}
			else if( is_array( $Model ) ) {
				$attributes = array_keys( $Model );
			}
			else {
				throw new InvalidConfigException( 'The Model have to be either an array or an object.' );
			}
			
		}
		
		foreach( $attributes as $key => $attribute ) {
			
			if( $attribute instanceof \Closure ) {
				$attribute = call_user_func( $attribute, $Model, $attribute, $key, $this );
			}
			
			if( is_string( $attribute ) || is_numeric( $attribute ) ) {
				
				if( !preg_match( ActiveAttribute::SHORT_FORMAT_PATTERN, $attribute, $matches ) ) {
					throw new InvalidConfigException( ActiveAttribute::SHORT_FORMAT_WRONG_PATTERN_MESSAGE );
				}
				
				$attribute = [
					'attribute' => $matches['attribute'],
					'format'    => $matches['format'] ?? ActiveAttribute::DEFAULT_FORMAT,
					'label'     => $matches['label'] ?? null,
				];
				
			}
			else if( $attribute instanceof ActiveAttribute ) {
				$attribute = $attribute->toArray();
			}
			else if( is_object( $attribute ) ) {
				$attribute = (array)$attribute;
			}
			else if( !is_array( $attribute ) ) {
				throw new InvalidConfigException( 'The attribute configuration must be an array or stdObj or instance of ' . ActiveAttribute::class . ' class' );
			}
			
			foreach( $attribute as $param => $value ) {
				if( $value instanceof \Closure ) {
					$attribute[ $param ] = call_user_func( $value, $Model, $attribute, $key, $this );
				}
			}
			
			if( isset( $attribute['visible'] ) && !$attribute['visible'] ) {
				unset( $attributes[ $key ] );
				continue;
			}
			
			if( isset( $attribute['attribute'] ) || isset( $attribute['name'] ) ) {
				
				$attributeName = $attribute['attribute'] ?? $attribute['name'] ?? null;
				
				if( !isset( $attribute['label'] ) ) {
					$attribute['label'] = $Model instanceof Model
						? $Model->getAttributeLabel( $attributeName )
						: Inflector::camel2words( $attributeName, true );
				}
				
				if( $attribut['value'] ?? true ) {
					$attribute['value'] = ArrayHelper::getValue( $Model, $attributeName );
				}
				
			}
			else if( !isset( $attribute['label'] ) || !array_key_exists( 'value', $attribute ) ) {
				throw new InvalidConfigException( 'The attribute configuration requires the "attribute" element to determine the value and display label.' );
			}
			
			$attribute['label'] = preg_replace( '/\sId$/', '', $attribute['label']);
			
			if( $this->encodeLabel ){
				$attribute['label'] = Html::encode( $attribute['label'] );
			}
			
			if( !isset( $attribute['format'] ) ) {
				
				$value = $attribute['value'];
				
				if( $value instanceof Model || is_object( $value ) || is_array( $value ) ) {
					$attribute['format'] = $format = 'html';
				}
				else {
					$attribute['format'] = $format = 'text';
				}
				
			}
			
			$attributes[ $key ] = $attribute;
		}
		
		return $attributes;
	}
	
	
}