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
		
		$tableOptions = null;
		if( $this->db->driverName === 'mysql' ) {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
		}
		
		$refTable = $refColumns = $columns = null;
		
		$this->alterTable( $params );
		
		$tableSchema = \Yii::$app->db->schema->getTableSchema( $table, true );
		
		foreach( static::getIndices() as $index ) {
			
			/**
			 * @var $refTable
			 * @var $refColumns
			 * @var $columns
			 */
			extract( $index, EXTR_IF_EXISTS );
			
			$idxName      = $this->_getIdxName( $table, $columns );
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
				$columns
			);
			
		}
		
		foreach( static::getReferences() as $ref ) {
			
			/**
			 * @var $refTable
			 * @var $refColumns
			 * @var $columns
			 */
			extract( $ref, EXTR_IF_EXISTS );
			
			$this->createForeignKey( $table, $columns, $refTable, $refColumns);
			
			/*
			$fkName           = $this->_getFkName( $table, $column );
			$idxName          = $this->_getIdxName( $table, $column );
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
				$refColumns,
				'CASCADE'
			);
			*/
			
		}
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
		
		$columns = null;
		
		foreach( $references as $ref ) {
			
			extract( $re, EXTR_IF_EXISTS );
			
			$this->dropForeignKey(
				$this->_getFkName( $table, $columns ),
				$table
			);
			
			$this->dropIndex(
				$this->_getIdxName( $table, $columns ),
				$table
			);
			
		}
		
		foreach( $indices as $index ) {
			
			extract( $index, EXTR_IF_EXISTS );
			
			$this->dropIndex(
				$this->_getIdxName( $table, $columns ),
				$table
			);
			
		}
		
		$this->dropTable( $table );
	}
	
	/*
	 * By default Indices are generating from References
	 */

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
				'refColumns' => 'id',
				'column'    => 'tree_id',
			],
			*/
		], $references );
	}
	
	public function createForeignKey( $table, $columns, $refTable, $refColumnss = 'id', $onDelete = 'CASCADE', $onUpdate = 'CASCADE' )
	{
		$idxName = "idx-$table-" . implode( '-', (array)$columns );
		$fkName  = "fk-$table-" . implode( '-', (array)$columns );
		
		echo "\nCreating foreign key '$fkName' for:\n"
			. "\ttable: '$table', columns: " . implode( ', ', (array)$columns ) . "\n"
			. "\treference: '$refTable', columns: " . implode( ', ', (array)$columns ) . "\n"
		;
		
		try {
			$this->dropForeignKey( $fkName, $table );
		} catch( \Exception $e ) { echo "not exists\n"; }
		
		try {
			$this->dropIndex( $idxName, $table );
		} catch( \Exception $e ) { echo "not exists\n"; }
		
		$message = "Empty column '" . implode( ', ', (array)$columns ) . "' in table '$table' before creating?";
		
		if( Yii::$app->controller->confirm( $message ) ) {
			$this->update( $table, array_fill_keys( (array)$columns, null ) );
		}
		
		try {
			$this->createIndex(
				$idxName,
				$table,
				$columns
			);
		} catch( \Exception $e ) { echo "can not create index $idxName\n"; }
		
		try {
			$this->createIndex(
				$idxName,
				$table,
				$columns
			);
		} catch( \Exception $e ) { echo "can not create index $idxName\n"; }
		
		$this->addForeignKey(
			$fkName,
			$table,
			$columns,
			$refTable,
			$refColumnss,
			$onDelete,
			$onUpdate
		);
		
		echo "\n";
	}
	
	public function boolean( $defaultValue = 0 )
	{
		return parent::boolean()->notNull()->defaultValue( $defaultValue ? 1 : 0 );
	}
	
	public function createdAt()
	{
		return $this->timestamp( null, true, false );
	}
	
	public function deletedAt()
	{
		return $this->timestamp( null, null, false );
	}
	
	public function updatedAt()
	{
		return $this->timestamp( null, true, true );
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
	
	public function timestamp( $precision = null, $defaultCreate = true, $defaultUpdate = false )
	{
		$builder = parent::timestamp( $precision );
		
		if( $defaultCreate === true ) {
			$defaultValue = ' CURRENT_TIMESTAMP';
		}
		else{
			$defaultValue = ' 0';
		}
		
		if( $defaultUpdate ) {
			$defaultValue .= ' ON UPDATE CURRENT_TIMESTAMP';
		}
		
		$builder->defaultExpression( $defaultValue );
		
		return $builder;
	}
	
	protected function _getIdxName( $table, $columns )
	{
		return $this->_getConstrateName( 'idx', $table, $columns);
	}
	
	protected function _getUidxName( $table, $columns )
	{
		return $this->_getConstrateName( 'uidx', $table, $columns);
	}
	
	protected function _getFkName( $table, $columns )
	{
		return $this->_getConstrateName( 'fk', $table, $columns);
	}
	
	protected function _getConstrateName( $prefix, $table, $columns)
	{
		return "$prefix-$table-" . implode( '-', (array)$columns );
	}
	
}