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
use yozh\base\components\ArrayHelper as arr;

abstract class Migration extends \yii\db\Migration
{
	const ALTER_MODE_UPDATE = 'alter_mode_update';
	const ALTER_MODE_DROP   = 'alter_mode_drop';
	const ALTER_MODE_IGNORE = 'alter_mode_ignore';
	
	protected static $_columns = [];
	
	public function safeUp( $params = [] )
	{
		$defaults = [
			'table' => static::$_table,
			'mode'  => self::ALTER_MODE_UPDATE,
		];
		
		extract( arr::setDefaults( $params, $defaults ) );
		
		$refTable = $refColumn = $column = null;
		
		$this->alterTable( $params );
		
		foreach( static::getIndices() as $index ) {
			
			extract( $index );
			
			$this->createIndex(
				$this->_getIdxName( $table, $column ),
				$table,
				$column
			);
			
		}
		
		foreach( static::getReferences() as $ref ) {
			
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
			'columns'    => static::getColumns(),
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
			'mode'       => self::ALTER_MODE_UPDATE,
			'options'    => null,
		];
		
		extract( arr::setDefaults( $params, $defaults ) );
		
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
			
			foreach( $columns as $column ) {
				$column->after( null );
			}
			
			parent::createTable( $table, $columns, $options );
		}
		
	}
	
	public function getColumns( $columns = [] )
	{
		return arr::merge( [
			'id' => $this->primaryKey(),
		], $columns );
	}
	
	/*
	 * By default Indices are generating from References
	 */
	public function getIndices( $indices = [] )
	{
		$indices = arr::merge( [
			/*
			[
				'column' => 'tree_id',
			],
			*/
		], $indices );
		
		$references = $this->getReferences();
		
		$indicesColumns    = arr::getColumn( $indices, 'column' );
		$referencesColumns = arr::getColumn( $references, 'column' );
		
		foreach( array_diff( $indicesColumns, $referencesColumns ) as $column ) {
			
			$indices[] = [ 'column' => $column ];
			
		}
		
		return $indices;
	}
	
	public function getReferences( $references = [] )
	{
		return arr::merge( [
			/*
			[
				'refTable'  => 'tree',
				'refColumn' => 'id',
				'column'    => 'tree_id',
			],
			*/
		], $references );
	}
	
	public function safeDown( $params = [] )
	{
		$defaults = [
			'table'      => static::$_table,
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
		];
		
		extract( arr::setDefaults( $params, $defaults ) );
		
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
	
	
}