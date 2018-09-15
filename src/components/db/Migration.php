<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:24
 */

namespace yozh\base\components\db;

use Yii;
use yozh\base\components\db\ColumnSchemaBuilder;
use yozh\base\components\db\Schema;
use yozh\base\components\helpers\ArrayHelper;

abstract class Migration extends \yii\db\Migration
{
	//524288	262144	131072	65536	32768	16384
	const ALTER_MODE_DROP_TABLE = 2;
	
	const ALTER_MODE_COLUMN_UPDATE = 32;
	const ALTER_MODE_COLUMN_DROP   = 64;
	const ALTER_MODE_COLUMN_IGNORE = 128;
	
	const ALTER_MODE_INDEX_UPDATE = 256;
	const ALTER_MODE_INDEX_DROP   = 512;
	const ALTER_MODE_INDEX_IGNORE = 1024;
	
	const ALTER_MODE_FOREIGN_UPDATE = 2048;
	const ALTER_MODE_FOREIGN_DROP   = 4056;
	const ALTER_MODE_FOREIGN_IGNORE = 8192;
	
	const ALTER_MODE_UPDATE = 2336;
	const ALTER_MODE_DROP   = 4632;
	const ALTER_MODE_IGNORE = 9344;
	
	const CONSTRAINTS_ACTION_RESTRICT  = 'RESTRICT';
	const CONSTRAINTS_ACTION_NO_ACTION = 'NO ACTION';
	const CONSTRAINTS_ACTION_CASCADE   = 'CASCADE';
	const CONSTRAINTS_ACTION_SET_NULL  = 'SET NULL';
	
	/**
	 * @var tableName
	 */
	protected static $_table;
	
	//protected static $_tableSchema;
	
	public static function getTableIndices( $tableName, $refresh = false )
	{
		static $data = [];
		
		if( isset( $data['$tableName'] ) ) {
			return $data['$tableName'];
		}
		$indexes = \Yii::$app->db->schema->getTableIndexes( $tableName, $refresh );
		
		foreach( $indexes as $index ) {
			$data[ $tableName ][ $index->name ] = $index;
		}
		
	}
	
	public function safeUp( $params = [] )
	{
		$defaults = [
			'tableName'  => static::$_table,
			'columns'    => static::getColumns(),
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
			'mode'       => static::ALTER_MODE_UPDATE,
			'options'    => null,
		];
		
		/**
		 * @var $tableName string
		 * @var $mode string
		 * @var $indices array
		 * @var $references array
		 */
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		$tableName = Yii::$app->db->schema->getRawTableName( $tableName );
		
		$tableOptions = null;
		if( $this->db->driverName === 'mysql' ) {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
		}
		
		$this->alterTable( $params );
		
		//$tableSchema = \Yii::$app->db->schema->getTableSchema( $tableName, true );
		
		foreach( $indices as $index ) {
			
			/**
			 * @var $refTable
			 * @var $refColumns
			 * @var $columns
			 */
			extract( $index, EXTR_IF_EXISTS );
			
			$idxName      = $this->_getIdxName( $tableName, $columns );
			$tableIndices = static::getTableIndices( $tableName );
			
			if( isset( $tableIndices[ $idxName ] ) && $mode != static::ALTER_MODE_UPDATE ) {
				continue;
			}
			else if( isset( $tableIndices[ $idxName ] ) && $mode == static::ALTER_MODE_UPDATE ) {
				$this->dropIndex(
					$idxName,
					$tableName
				);
			}
			
			$this->createIndex(
				$idxName,
				$tableName,
				$columns
			);
			
		}
		
		foreach( $references as $ref ) {
			
			/**
			 * @var $refTable
			 * @var $refColumns
			 * @var $columns
			 * @var $onDelete
			 * @var $onUpdate
			 */
			
			$defaults = [
				'refTable'   => ArrayHelper::DEFAULTS_REQUIRED,
				'refColumns' => ArrayHelper::DEFAULTS_REQUIRED,
				'columns'    => ArrayHelper::DEFAULTS_REQUIRED,
				'onDelete'   => self::CONSTRAINTS_ACTION_RESTRICT,
				'onUpdate'   => self::CONSTRAINTS_ACTION_CASCADE,
			];
			
			/**
			 * @var $tableName string
			 * @var $mode string
			 * @var $indices array
			 * @var $references array
			 */
			extract( ArrayHelper::setDefaults( $ref, $defaults ) );
			
			$this->createForeignKey( $tableName, $columns, $refTable, $refColumns, $onDelete, $onUpdate );
			
			/*
			$fkName           = $this->_getFkName( $tableName, $column );
			$idxName          = $this->_getIdxName( $tableName, $column );
			$tableForeignKeys = $tableSchema->foreignKeys;
			
			if( isset( $tableForeignKeys[ $fkName ] ) && $mode != static::ALTER_MODE_UPDATE ) {
				continue;
			}
			else if( isset( $tableForeignKeys[ $fkName ] ) && $mode == static::ALTER_MODE_UPDATE ) {
				
				$this->dropForeignKey(
					$fkName,
					$tableName
				);
				
				$this->dropIndex(
					$idxName,
					$tableName
				);
			}
			
			$this->createIndex(
				$idxName,
				$tableName,
				$column
			);
			
			$this->addForeignKey(
				$fkName,
				$tableName,
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
		 * @var $tableName string
		 * @var $indices array
		 * @var $references array
		 */
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		$columns = null;
		
		foreach( $references as $ref ) {
			
			extract( $ref, EXTR_IF_EXISTS );
			
			$this->dropForeignKey(
				$this->_getFkName( $tableName, $columns ),
				$tableName
			);
			
			$this->dropIndex(
				$this->_getIdxName( $tableName, $columns ),
				$tableName
			);
			
		}
		
		foreach( $indices as $index ) {
			
			extract( $index, EXTR_IF_EXISTS );
			
			$this->dropIndex(
				$this->_getIdxName( $tableName, $columns ),
				$tableName
			);
			
		}
		
		$this->dropTable( $tableName );
	}
	
	/*
	 * By default Indices are generating from References
	 */
	
	public function alterTable( $params = [] )
	{
		
		/**
		 * @var $tableName string
		 * @var $columns array
		 * @var $indices array
		 * @var $references array
		 * @var $mode string
		 * @var $options array
		 */
		$defaults = [
			'tableName'      => static::$_table,
			'columns'    => static::getColumns(),
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
			'mode'       => static::ALTER_MODE_UPDATE,
			'options'    => null,
		];
		
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		if( $tableSchema = \Yii::$app->db->schema->getTableSchema( $tableName ) ) {
			
			if( $mode == self::ALTER_MODE_DROP_TABLE ) {
				$this->_safeDown( $tableName, $indices, $references );
			}
			
			$pk = $tableSchema->primaryKey;
			
			foreach( $columns as $key => $column ) {
				
				if( isset( $tableSchema->columns[ $key ] ) ) {
					if( $mode == static::ALTER_MODE_UPDATE && !in_array( $key, $pk ) ) { //
						$this->alterColumn( $tableName, $key, $column );
					}
				}
				else {
					$this->addColumn( $tableName, $key, $column );
				}
			}
			
		}
		else {
			
			foreach( $columns as $column ) {
				$column->after( null );
			}
			
			parent::createTable( $tableName, $columns, $options );
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
				'columns'    => 'tree_id',
				'onDelete'    => self::CONSTRAINTS_ACTION_RESTRICT,
			],
			*/
		], $references );
	}
	
	public function createForeignKey(
		$tableName,
		$columns,
		$refTable,
		$refColumns = 'id',
		$onDelete = self::CONSTRAINTS_ACTION_RESTRICT,
		$onUpdate = self::CONSTRAINTS_ACTION_CASCADE
	)
	{
		$idxName = "idx-$tableName-" . implode( '-', (array)$columns );
		$fkName  = "fk-$tableName-" . implode( '-', (array)$columns );
		
		echo "\nCreating foreign key '$fkName' for:\n"
			. "\ttable: '$tableName', columns: " . implode( ', ', (array)$columns ) . "\n"
			. "\treference: '$refTable', columns: " . implode( ', ', (array)$refColumns ) . "\n";
		
		try {
			$this->dropForeignKey( $fkName, $tableName );
		} catch( \Exception $e ) {
			echo "not exists\n";
		}
		
		try {
			$this->dropIndex( $idxName, $tableName );
		} catch( \Exception $e ) {
			echo "not exists\n";
		}
		
		$message = "Empty column '" . implode( ', ', (array)$columns ) . "' in table '$tableName' before creating?";
		
		if( Yii::$app->controller->confirm( $message ) ) {
			$this->update( $tableName, array_fill_keys( (array)$columns, null ) );
		}
		
		try {
			$this->createIndex(
				$idxName,
				$tableName,
				$columns
			);
		} catch( \Exception $e ) {
			echo "can not create index $idxName\n";
		}
		
		try {
			$this->createIndex(
				$idxName,
				$tableName,
				$columns
			);
		} catch( \Exception $e ) {
			echo "can not create index $idxName\n";
		}
		
		$this->addForeignKey(
			$fkName,
			$tableName,
			$columns,
			$refTable,
			$refColumns,
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
		return $this->timestamp( null, false, false );
	}
	
	public function updatedAt()
	{
		return $this->timestamp( null, true, true );
	}
	
	/**
	 * Creates a medium text column.
	 * @return ColumnSchemaBuilder the column instance which can be further customized.
	 */
	public function mediumText()
	{
		return $this->getDb()->getSchema()->createColumnSchemaBuilder( 'mediumtext' );
	}
	
	/**
	 * Creates a long text column.
	 * @return ColumnSchemaBuilder the column instance which can be further customized.
	 */
	public function longText()
	{
		return $this->getDb()->getSchema()->createColumnSchemaBuilder( 'longtext' );
	}
	
	/**
	 * Creates a tiny text column.
	 * @return ColumnSchemaBuilder the column instance which can be further customized.
	 */
	public function tinyText()
	{
		return $this->getDb()->getSchema()->createColumnSchemaBuilder( 'tinytext' );
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
		$type = Schema::TYPE_TIMESTAMP;
		
		if( $defaultCreate === true ) {
			$defaultValue = ' CURRENT_TIMESTAMP';
		}
		else {
			$type         .= ' NULL';
			$defaultValue = 'NULL';
		}
		
		if( $defaultUpdate ) {
			$defaultValue .= ' ON UPDATE CURRENT_TIMESTAMP';
		}
		
		$builder = $this->getDb()->getSchema()->createColumnSchemaBuilder( $type, $precision );
		
		$builder->defaultExpression( $defaultValue );
		
		return $builder;
	}
	
	protected function _getIdxName( $tableName, $columns )
	{
		return $this->_getConstrateName( 'idx', $tableName, $columns );
	}
	
	protected function _getUidxName( $tableName, $columns )
	{
		return $this->_getConstrateName( 'uidx', $tableName, $columns );
	}
	
	protected function _getFkName( $tableName, $columns )
	{
		return $this->_getConstrateName( 'fk', $tableName, $columns );
	}
	
	protected function _getConstrateName( $prefix, $tableName, $columns )
	{
		return "$prefix-$tableName-" . implode( '-', (array)$columns );
	}
	
}