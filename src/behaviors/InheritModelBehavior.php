<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 16.09.2018
 * Time: 12:40
 */

namespace yozh\base\behaviors;

use yozh\base\models\BaseModel as Model;
use yii\db\ActiveRecord;
use yozh\base\traits\patterns\DecoratorTrait;

class InheritModelBehavior extends \yozh\base\components\Behavior
{
	use DecoratorTrait;
	
	protected $_parents = [];
	
	public function setParents( $parents )
	{
		if( is_string( $parents ) ) {
			$parents = [ $parents ];
		}
		
		foreach( $parents as $className ) {
			if( is_subclass_of( $className, ActiveRecord::class ) ) {
				$this->_parents[] = $className;
			}
			else {
				throw new \yii\base\InvalidParamException( "$className has to be an instance of " . ActiveRecord::class );
			}
		}
		
	}
	
	public function __call( $name, $params )
	{
		
		if( parent::hasMethod( $name ) ) {
			return parent::__call( $name, $params );
		}
		else if( $this->hasMethod( $name ) ) {
			
			foreach( $this->owner->getRelatedRecords() ?? [] as &$Relation ) {
				
				if( $Relation->hasMethod( $name ) ) {
					return call_user_func_array( [ &$Relation, $name ], $params );
				}
				
			}
			
		}
		else {
			throw new UnknownMethodException( 'Calling unknown method: ' . get_class( $this ) . "::$name()" );
		}
		
	}
	
	public function __get( $name )
	{
		if( parent::canGetProperty( $name ) ) {
			parent::__get( $name );
		}
		else if( $this->canGetProperty( $name ) ) {
			
			foreach( $this->owner->getRelatedRecords() ?? [] as &$Relation ) {
				if( $Relation instanceof Model && ( !$this->_parents || in_array( get_class( $Relation ), $this->_parents ) ) ) {
					return $Relation->$name;
				}
			}
			
		}
		else {
			throw new UnknownPropertyException( 'Getting unknown property: ' . get_class( $this->owner ) . '::' . $name );
		}
		
	}
	
	public function __set( $name, $value )
	{
		if( parent::canSetProperty( $name ) ) {
			parent::__set( $name, $value );
		}
		else if( $this->canSetProperty( $name ) ) {
			
			foreach( $this->owner->getRelatedRecords() ?? [] as &$Relation ) {
				if( $Relation instanceof Model && ( !$this->_parents || in_array( get_class( $Relation ), $this->_parents ) ) ) {
					$Relation->$name = $value;
				}
			}
			
		}
		else {
			throw new UnknownPropertyException( 'Setting unknown property: ' . get_class( $this->owner ) . '::' . $name );
		}
		
	}
	
	public function canGetProperty( $name, $checkVars = true )
	{
		return parent::canGetProperty( $name, $checkVars ) || $this->_canProperty( $name, $checkVars );
	}
	
	public function canSetProperty( $name, $checkVars = true )
	{
		return parent::canSetProperty( $name, $checkVars ) || $this->_canProperty( $name, $checkVars, false );
	}
	
	public function hasMethod( $name )
	{
		foreach( $this->owner->getRelatedRecords() ?? [] as $Relation ) {
			if( $Relation instanceof Model && ( !$this->_parents || in_array( get_class( $Relation ), $this->_parents ) ) ) {
				if( parent::hasMethod( $name ) || method_exists( $Relation, $name ) ) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	protected function _canProperty( $name, bool $checkVars = true, bool $asGetter = true )
	{
		foreach( $this->owner->getRelatedRecords() ?? [] as $Relation ) {
			
			if( $Relation instanceof Model && ( !$this->_parents || in_array( get_class( $Relation ), $this->_parents ) ) ) {
				
				/*
				if( !$Relation instanceof Model ) {
					throw new \yii\base\InvalidParamException( "$Relation have to be an instance of " . ActiveRecord::class );
				}
				*/
				
				if( method_exists( $Relation, $asGetter ? 'get' . $name : 'set' . $name )
					|| $checkVars && property_exists( $Relation, $name )
					|| $checkVars && $Relation->hasAttribute( $name )
				) {
					return true;
				}
				
			}
			
		}
		
		return false;
		
	}
	
	
}