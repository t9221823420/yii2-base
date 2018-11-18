<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.09.2018
 * Time: 3:41
 */

namespace yozh\base\traits;

use yii\db\ActiveRecord;
use yozh\base\models\BaseActiveQuery;

trait ActiveRecordDecoratorTrait
{
	protected $_object;
	
	/*
	public function __construct( array $attributes = [], array $config = [] )
	{
		$this->_object = new ActiveRecord();
		
		parent::__construct( $attributes, $config );
	}
	*/
	
	public static function __callStatic( string $name, array $params )
	{
		switch ( $name ) {

		    /*
		    case 'find':
		    case 'instantiate':
		    case 'populateRecord':
			
			    return forward_static_call_array( "\yii\db\ActiveRecord::$name", $params );
			    
		        break;

		    */
		}
		
	}

    public static function find()
    {
        $class = static::class . 'Query';

        if( class_exists( $class ) ) {
            return new $class( static::class );
        }
        else {
            return new BaseActiveQuery( static::class );
        }

    }

    public static function instantiate($row)
    {
        return new static();
    }

    public static function populateRecord($record, $row)
    {
        $columns = static::getTableSchema()->columns;
        foreach ($row as $name => $value) {
            if (isset($columns[$name])) {
                $row[$name] = $columns[$name]->phpTypecast($value);
            }
        }
        parent::populateRecord($record, $row);
    }

    /**
     * Returns the schema information of the DB table associated with this AR class.
     * @return TableSchema the schema information of the DB table associated with this AR class.
     * @throws InvalidConfigException if the table for the AR class does not exist.
     */
    public static function getTableSchema()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;
    }

	/*
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
	*/
}