<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 09.12.2018
 * Time: 21:07
 */

namespace yozh\base\components\db;

use yozh\base\components\Component;
use yozh\base\components\helpers\ArrayHelper;
use yozh\base\components\helpers\Inflector;
use yozh\base\interfaces\models\ActiveRecordInterface;

class Reference extends Component
{
	protected $_name;
	
	protected $_table;
	
	protected $_refTable;
	
	protected $_link;
	
	protected $_modelClass;
	
	protected $_refModelClass;
	
	protected $_getter;
	
	public function __construct( array $config = [] )
	{
		foreach( $config as $key => $value ) {
			if( property_exists( static::class, "_$key" ) ) {
				
				switch( $key ) {
					
					case 'modelClass':
					case 'refModelClass':
						
						if( !class_exists( $value ) ) {
							throw new \yii\base\InvalidParamException( "Class $value does not esists." );
						}
						
						$this->{"_$key"} = $value;
						
						break;
					
					default:
						$this->{"_$key"} = $value;
				}
				
				unset( $config[ $key ] );
			}
		}
		
		parent::__construct( $config );
	}
	
	public function __get( $name )
	{
		if( property_exists( static::class, '_' . $name ) ) {
			
			$name = '_' . $name;
			
			switch( $name ) {
				
				case '_table':
					
					$modelClass = $this->_modelClass;
					
					if( !$this->_table && $modelClass
						&& ( new \ReflectionClass( $modelClass ) )->implementsInterface( ActiveRecordInterface::class )
					) {
						$this->_table = ( $modelClass )::getRawTableName();
					}
					
					return $this->_table;
				
				case '_refTable':
					
					$refModelClass = $this->_modelClass;
					
					if( !$this->_refTable && $refModelClass
						&& ( new \ReflectionClass( $refModelClass ) )->implementsInterface( ActiveRecordInterface::class )
					) {
						$this->_refTable = ( $refModelClass )::getRawTableName();
					}
					
					return $this->_refTable;
				
				case '_getter':
					
					if( !$this->_getter && $this->_modelClass && !empty( (array)$this->link ) ) {
						
						foreach( $this->link as $childColumn => $parentColumn ) {
							
							$refGetter = 'get' . Inflector::camelize( preg_replace( '/_id$/', '', $childColumn ) );
							
							if( method_exists( $this->_modelClass, $refGetter ) ) {
								$this->_getter = $refGetter;
								break;
							}
							
						}
						
					}
					
					return $this->_getter;
				
				default:
					return $this->$name;
			}
			
		}
		else {
			return parent::__get( $name );
		}
	}
	
}