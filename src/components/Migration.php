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
use yozh\base\components\ArrayHelper;

abstract class Migration extends \yii\db\Migration
{
	const ALTER_MODE_UPDATE     = 'alter_mode_update';
	const ALTER_MODE_DROP_TABLE = 'alter_mode_drop_table';
	const ALTER_MODE_IGNORE     = 'alter_mode_ignore';
	
	protected static $_tableSchema;
	
	public function safeUp( $params = [] )
	{
		$defaults = [
			'table' => static::$_table,
			'mode'  => self::ALTER_MODE_UPDATE,
		];
		
		/**
		 * @var $table string
		 * @var $mode string
		 */
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		if( $tableSchema = \Yii::$app->db->schema->getTableSchema( $table, true ) ) {
			
			$refTable = $refColumn = $column = null;
			
			$this->alterTable( $params );
			
			foreach( static::getIndices() as $index ) {
				
				/**
				 * @var $refTable
				 * @var $refColumn
				 * @var $column
				 */
				extract( $index );
				
				$idxName      = $this->_getIdxName( $table, $column );
				$tableIndices = static::getTableIndices( $table );
				
				if( isset( $tableIndices[ $idxName ] ) && $mode != self::ALTER_MODE_UPDATE ) {
					continue;
				}
				else if( isset( $tableIndices[ $idxName ] ) && $mode == self::ALTER_MODE_UPDATE ) {
					$this->dropIndex(
						$idxName,
						$table
					);
				}
				
				$this->createIndex(
					$idxName,
					$table,
					$column
				);
				
			}
			
			foreach( static::getReferences() as $ref ) {
				
				/**
				 * @var $refTable
				 * @var $refColumn
				 * @var $column
				 */
				extract( $ref );
				
				$fkName  = $this->_getFkName( $table, $column );
				$idxName = $this->_getIdxName( $table, $column );
				$tableForeignKeys = $tableSchema->foreignKeys;
				
				if( isset( $tableForeignKeys[ $fkName ] ) && $mode != self::ALTER_MODE_UPDATE ) {
					continue;
				}
				else if( isset( $tableForeignKeys[ $fkName ] ) && $mode == self::ALTER_MODE_UPDATE ) {
					
					$this->dropForeignKey(
						$fkName,
						$table
					);
					
					$this->dropIndex(
						$idxName,
						$table
					);
				}
				
				$this->createIndex(
					$idxName,
					$table,
					$column
				);
				
				$this->addForeignKey(
					$fkName,
					$table,
					$column,
					$refTable,
					$refColumn,
					'CASCADE'
				);
				
			}
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
		
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		if( $tableSchema = \Yii::$app->db->schema->getTableSchema( $table ) ) {
			
			if( $mode == self::ALTER_MODE_DROP_TABLE ) {
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
		return ArrayHelper::merge( [
			'id' => $this->primaryKey(),
		], $columns );
	}
	
	/*
	 * By default Indices are generating from References
	 */
	
	public function getIndices( $indices = [] )
	{
		$indices = ArrayHelper::merge( [
			/*
			[
				'column' => 'tree_id',
			],
			*/
		], $indices );
		
		$references = $this->getReferences();
		
		$indicesColumns    = ArrayHelper::getColumn( $indices, 'column' );
		$referencesColumns = ArrayHelper::getColumn( $references, 'column' );
		
		foreach( array_diff( $indicesColumns, $referencesColumns ) as $column ) {
			
			$indices[] = [ 'column' => $column ];
			
		}
		
		return $indices;
	}
	
	public function getReferences( $references = [] )
	{
		return ArrayHelper::merge( [
			/*
			[
				'refTable'  => 'tree',
				'refColumn' => 'id',
				'column'    => 'tree_id',
			],
			*/
		], $references );
	}
	
	private function _getIdxName( $table, $column )
	{
		return "idx-$table-$column";
	}
	
	public static function getTableIndices( $table, $refresh = false )
	{
		static $data = [];
		
		if( isset( $data['$table'] ) ) {
			return $data['$table'];
		}
		$indexes = \Yii::$app->db->schema->getTableIndexes( $table, $refresh );
		
		foreach( $indexes as $index ) {
			$data[ $table ][ $index->name ] = $index;
		}
		
	}
	
	private function _getFkName( $table, $column )
	{
		return "fk-$table-$column";
	}
	
	public function safeDown( $params = [] )
	{
		$defaults = [
			'table'      => static::$_table,
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
		];
		
		/**
		 * @var $table string
		 * @var $indices array
		 * @var $references array
		 */
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
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
	
	public function enum( $values = [] )
	{
		
		$builder = new ColumnSchemaBuilder( Schema::TYPE_ENUM, null, null, [
			'listValues' => $values,
		] );
		
		return $builder;
	}
	
	public function boolean( $defaultValue = 0 )
	{
		return parent::boolean()->notNull()->defaultValue( $defaultValue ? 1 : 0 ) ;
	}
	
	
	public function set( $values = [] )
	{
		
		$builder = new ColumnSchemaBuilder( Schema::TYPE_SET, null, null, [
			'listValues' => $values,
		] );
		
		return $builder;
	}
	
}