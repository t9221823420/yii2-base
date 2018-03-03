<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:24
 */

namespace yozh\base\components;

use yozh\base\components\db\ColumnSchemaBuilder;
use yozh\base\components\db\Schema;

class Migration extends \yii\db\Migration
{
	const ALTER_MODE_UPDATE = 'update';
	const ALTER_MODE_DROP   = 'drop';
	const ALTER_MODE_IGNORE = 'ignore';
	
	protected static $_table   = 'table';
	protected static $_columns = [];
	
	protected static $_references = [
		/*
		[
			'refTable'  => 'tree',
			'refColumn' => 'id',
			'column'    => 'tree_id',
		],
		*/
	];
	
	protected static $_indices = [
		/*
		[
			'column' => 'tree_id',
		],
		*/
	];
	
	public function safeUp()
	{
		$table = $table ?? static::$_table;
		
		$refTable = $refColumn = $column = null;
		
		static::$_columns = [
			'id' => $this->primaryKey(),
		];
		
		$this->alterTable();
		
		foreach( static::$_indices as $index ) {
			
			extract( $index );
			
			$this->createIndex(
				$this->_getIdxName( $table, $column ),
				$table,
				$column
			);
			
		}
		
		foreach( static::$_references as $ref ) {
			
			extract( $ref );
			
			$this->createIndex(
				$this->_getIdxName( $table, $column ),
				$table,
				$column
			);
			
			$this->addForeignKey(
				$this->_getFkName( $table, $column ),
				$table,
				$column,
				$refTable,
				$refColumn,
				'CASCADE'
			);
		}
		
	}
	
	public function alterTable( $params = [] )
	{
		
		/**
		 * @var $table string
		 * @var $columns array
		 * @var $indices array
		 * @var $references array
		 * @var $mode string
		 * @var $options array
		 */
		$defaults = [
			'table'      => static::$_table,
			'columns'    => static::$_columns,
			'indices'    => static::$_indices,
			'references' => static::$_references,
			'mode'       => self::ALTER_MODE_UPDATE,
			'options'    => [],
		];
		
		extract( array_replace( $defaults, array_intersect_key( $params, $defaults ) ) );
		
		if( $tableSchema = \Yii::$app->db->schema->getTableSchema( $table ) ) {
			
			if( $mode == self::ALTER_MODE_DROP ) {
				$this->_safeDown( $table, $indices, $references );
			}
			
			$pk = $tableSchema->primaryKey;
			
			foreach( $columns as $key => $column ) {
				
				if( isset( $tableSchema->columns[ $key ] ) ) {
					if( $mode == self::ALTER_MODE_UPDATE && !in_array( $key, $pk ) ) { //
						$this->alterColumn( $table, $key, $column );
					}
				}
				else {
					$this->addColumn( $table, $key, $column );
				}
			}
			
		}
		else {
			parent::createTable( $table, $columns, $options );
		}
		
	}
	
	protected function _safeDown( $table = null, $indices = null, $references = null )
	{
		$table      = $table ?? static::$_table;
		$indices    = $indices ?? static::$_indices;
		$references = $references ?? static::$_references;
		
		$column = null;
		
		foreach( $references as $ref ) {
			
			extract( $ref );
			
			$this->dropForeignKey(
				$this->_getFkName( $table, $column ),
				$table
			);
			
			$this->dropIndex(
				$this->_getIdxName( $table, $column ),
				$table
			);
			
		}
		
		foreach( $indices as $index ) {
			
			extract( $index );
			
			$this->dropIndex(
				$this->_getIdxName( $table, $column ),
				$table
			);
			
		}
		
		$this->dropTable( $table );
	}
	
	private function _getFkName( $table, $column )
	{
		return "fk-$table-$column";
	}
	
	private function _getIdxName( $table, $column )
	{
		return "idx-$table-$column";
	}
	
	public function enum( $values = [] )
	{
		
		$builder = new ColumnSchemaBuilder( Schema::TYPE_ENUM, null, null, [
			'listValues' => $values,
		] );
		
		return $builder;
	}
	
	public function set( $values = [] )
	{
		
		$builder = new ColumnSchemaBuilder( Schema::TYPE_SET, null, null, [
			'listValues' => $values,
		] );
		
		return $builder;
	}
	
	public function safeDown()
	{
		$this->_safeDown();
	}
	
	
}