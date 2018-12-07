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
	public static $depends = [];
	/**
	 * @var tableName
	 */
	protected static $_table;
	public           $mode = self::ALTER_MODE_UPDATE;
	
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
		
		return $data;
	}
	
	public function renameColumn( $tableName, $name, $newName )
	{
		if( \Yii::$app->db->schema->getTableSchema( $tableName )->columns[ $name ] ?? false ) {
			parent::renameColumn( $tableName, $name, $newName );
		}
		
	}
	
	public function dropColumns( $tableName, $columns )
	{
		$columns = array_intersect( (array)$columns, array_keys( \Yii::$app->db->schema->getTableSchema( $tableName )->columns ) );
		
		if( $columns ) {
			
			foreach( $columns as $column ) {
				
				$fkName  = "fk-$tableName-$column";
				$idxName = "idx-$tableName-$column";
				
				if( ( $this->dropExistFks( $tableName, null, $columns ) && $this->dropExistIdx( $tableName, null, $columns ) )
					|| ( $this->dropExistFks( $tableName, $fkName, (array)$column ) && $this->dropExistIdx( $tableName, $idxName, (array)$column ) )
				) {
					
					parent::dropColumn( $tableName, $column );
					
				}
				else {
					echo "Skip dropping column '$column'";
				}
			}
			
		}
		else if( !empty( $columns ) ) {
			echo "Skip dropping columns '" . implode( ', ', (array)$columns ) . "'";
		}
		
	}
	
	
	public function safeUp( $params = [] )
	{
		//return true;
		
		$defaults = [
			'tableName'  => static::$_table,
			'columns'    => static::getColumns(),
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
			'options'    => null,
		];
		
		/**
		 * @var $tableName string
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
			
			$idxName = $this->_getIdxName( $tableName, $columns );
			
			echo "\nCreating Index '$idxName' for:\n"
				. "\ttable: '$tableName'"
				. ( empty( $columns )
					? "\n"
					: ", columns: " . implode( ', ', (array)$columns ) . "\n" );
			
			if( !$this->dropExistIdx( $tableName, $idxName, $columns ) ) {
				
				echo "Skip creation of Index '$idxName'\n";
				continue;
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
			
		}
		
		foreach( $references as $reference ) {
			
			$this->createForeignKey( $reference );
			
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
	
	public function alterTable( $params = [] )
	{
		
		/**
		 * @var $tableName string
		 * @var $columns array
		 * @var $indices array
		 * @var $references array
		 * @var $options array
		 */
		$defaults = [
			'tableName'  => static::$_table,
			'columns'    => static::getColumns(),
			'indices'    => static::getIndices(),
			'references' => static::getReferences(),
			'mode'       => static::ALTER_MODE_UPDATE,
			'options'    => null,
		];
		
		extract( ArrayHelper::setDefaults( $params, $defaults ) );
		
		if( $tableSchema = \Yii::$app->db->schema->getTableSchema( $tableName ) ) {
			
			if( $this->mode == self::ALTER_MODE_DROP_TABLE ) {
				$this->_safeDown( $tableName, $indices, $references );
			}
			
			$pk = $tableSchema->primaryKey;
			
			foreach( $columns as $key => $column ) {
				
				if( isset( $tableSchema->columns[ $key ] ) ) {
					if( $this->mode == static::ALTER_MODE_UPDATE && !in_array( $key, $pk ) ) { //
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
		return array_merge( [
			'id' => $this->primaryKey(),
		], $columns );
	}
	
	public function getIndices( $indices = [] )
	{
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
		return $references;
	}
	
	/**
	 * @param $fkName - foreign key name
	 * @param $fkName - index name for FK
	 * @param $tableName
	 * @param array $columns - only for info
	 * @return bool
	 */
	public function dropExistFks( $tableName, $fkName = null, $columns = [] )
	{
		$fkNames = $fkNames ?? [ "fk-$tableName-" . implode( '-', (array)$columns ) ];
		
		foreach( (array)$fkNames as $fkName ) {
			
			$currentFks = Yii::$app->db->schema->getTableSchema( $tableName, true )->foreignKeys;
			
			if( $currentFks[ $fkName ] ?? false ) {
				
				$message = "Foreign key '$fkName' exists.\n"
					. ( empty( $columns )
						? ''
						: "Drop Foreign key '$fkName' for columns '" . implode( ', ', (array)$columns ) . "' for table '$tableName' ?\n" );
				
				if( Yii::$app->controller->dropExistFks !== false
					&& ( Yii::$app->controller->dropExistFks || Yii::$app->controller->confirm( $message, false ) ) ) {
					
					try {
						$this->dropForeignKey( $fkName, $tableName );
					} catch( \Exception $e ) {
						echo "Foreign key '$fkName' does not exists\n";
					}
					
				}
				else {
					return false;
				}
				
			}
		}
		
		return true;
	}
	
	/**
	 * @param $idxName
	 * @param $tableName
	 * @param array $columns - ony for Info
	 * @return bool
	 */
	public function dropExistIdx( $tableName, $idxNames = null, $columns = [] )
	{
		$idxNames = $idxNames ?? [ "idx-$tableName-" . implode( '-', (array)$columns ) ];
		
		$tableIndices = static::getTableIndices( $tableName )[ $tableName ] ?? [];
		
		foreach( (array)$idxNames as $idxName ) {
			
			if( $tableIndices[ $idxName ] ?? false ) {
				
				$message = "Index '$idxName' exists.\n"
					. "Drop Index '$idxName' for columns '" . implode( ', ', (array)$columns ) . "' for table '$tableName' ?\n";
				
				if( Yii::$app->controller->dropExistIdxs !== false
					&& ( Yii::$app->controller->dropExistIdxs || Yii::$app->controller->confirm( $message, false ) ) ) {
					
					try {
						$this->dropIndex( $idxName, $tableName );
					} catch( \Exception $e ) {
						echo "Index '$idxName' does not exists\n";
					}
					
				}
				else {
					
					return false;
					
				}
			}
		}
		
		return true;
	}
	
	public function createForeignKey( $reference )
	{
		/**
		 * @var $tableName
		 * @var $columns
		 * @var $refTable
		 * @var $refColumns
		 * @var $onDelete
		 * @var $onUpdate
		 */
		$defaults = [
			'tableName'  => static::$_table,
			'columns'    => static::getColumns(),
			'refTable'   => ArrayHelper::DEFAULTS_REQUIRED,
			'refColumns' => ArrayHelper::DEFAULTS_REQUIRED,
			'onDelete'   => self::CONSTRAINTS_ACTION_RESTRICT,
			'onUpdate'   => self::CONSTRAINTS_ACTION_CASCADE,
		];
		
		extract( ArrayHelper::setDefaults( $reference, $defaults ) );
		
		$idxName = "idx-$tableName-" . implode( '-', (array)$columns );
		$fkName  = "fk-$tableName-" . implode( '-', (array)$columns );
		
		echo "\nCreating foreign key '$fkName' for:\n"
			. "\ttable: '$tableName', columns: " . implode( ', ', (array)$columns ) . "\n"
			. "\treference: '$refTable', columns: " . implode( ', ', (array)$refColumns ) . "\n";
		
		if( !$this->dropExistFks( $tableName, $fkName, $columns ) ) {
			
			echo "Skip creation of foreign key '$fkName'\n";
			
			return true;
		}
		
		/**
		 * true if dropped or not Exists
		 */
		if( $this->dropExistIdx( $tableName, $idxName, $columns ) ) {
			
			$message = "Truncate columns '" . implode( ', ', (array)$columns ) . "' for table '$tableName' before creating new Index?\n";
			
			if( Yii::$app->controller->truncateIdxs !== false
				&& ( Yii::$app->controller->truncateIdxs || Yii::$app->controller->confirm( $message, false ) ) ) {
				//echo "Truncate '$fkName'\n";
				$this->update( $tableName, array_fill_keys( (array)$columns, null ) );
			}
			
			try {
				$this->createIndex(
					$idxName,
					$tableName,
					$columns
				);
			} catch( \Exception $e ) {
				echo "can not create Index $idxName\n";
			}
			
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