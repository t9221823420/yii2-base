<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 25.03.2018
 * Time: 17:06
 */

namespace yozh\base\controllers;

use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use yozh\base\components\helpers\FileHelper;
use yozh\base\components\db\Migration;
use yozh\base\traits\controllers\ConsoleControllerTrait;

class MigrateController extends \yii\console\controllers\MigrateController
{
	use ConsoleControllerTrait;
	
	const SORT_COMMON   = 'common';
	const SORT_BY_GROUP = 'group';
	
	public $defaultDir = 'migrations';
	
	public $alterMode = Migration::ALTER_MODE_UPDATE;
	
	protected $_dropIdxs;
	
	protected $_truncateIdxs;
	
	protected $_dropFks;
	
	protected $_migrations;
	
	protected $_migrationsByGroup;
	
	protected $_applied;
	
	public function options( $actionID )
	{
		return array_unique( array_merge( parent::options( $actionID ), [
			'dropFks',
			'dropIdxs',
			'truncateIdxs',
			'alterMode',
		] ) );
	}
	
	public function __get( $name )
	{
		switch( $name ) {
			
			case 'dropFks' :
			case 'dropIdxs' :
			case 'truncateIdxs' :
				
				$name = '_' . $name;
				
				return $this->$name;
			
			default:
				return parent::__get( $name );
		}
	}
	
	public function __set( $name, $value )
	{
		switch( $name ) {
			
			case 'dropFks' :
			case 'dropIdxs' :
			case 'truncateIdxs' :
				
				$name = '_' . $name;
				
				if( strtolower( $value ) == 'false' ) {
					$this->$name = false;
				}
				else {
					$this->$name = (bool)$value;
				}
				
				break;
			
			default:
				return parent::__set( $name, $value );
		}
	}
	
	/**
	 * @param $mask
	 * @param null|int $limit - not used yet @TODO
	 * @return int
	 */
	public function actionMask( $mask, $exceptMask = null, $limit = null )
	{
		$newMigrations = $this->getNewMigrations();
		
		$mask       = preg_replace( '/[,;]/', '|', $mask );
		$exceptMask = preg_replace( '/[,;]/', '|', $exceptMask );
		
		$migrations = null;
		foreach( (array)$newMigrations as $key => $migration ) {
			if( preg_match( "/$mask/", $migration ) && !( $exceptMask && preg_match( "/$exceptMask/", $migration ) ) ) {
				$migrations[ $key ] = $migration;
			}
		}
		
		if( $migrations ) {
			return $this->actionUp( $migrations );
		}
		else {
			$this->stdout( "No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN );
		}
		
	}
	
	/**
	 * $limit moved to @see MigrateController::getNewMigrations() as $data
	 * also "No new migrations found." moved to @see MigrateController::getNewMigrations() beacause of responsibility
	 *
	 * with $data as array @used-by MigrateController::actionMask() after get masked migrations
	 *
	 * Upgrades the application by applying new migrations.
	 *
	 * For example,
	 *
	 * ```
	 * yii migrate     # apply all new migrations
	 * yii migrate 3   # apply the first 3 new migrations
	 * ```
	 *
	 *
	 *
	 * @param int|array $data the number of new migrations to be applied. If 0, it means
	 * applying all available new migrations.
	 *
	 * @return int the status of the action execution. 0 means normal, other values mean abnormal.
	 */
	public function actionUp( $data = null )
	{
		$result = $this->getNewMigrations( $data );
		
		if( $result == ExitCode::OK ) {
			
			$this->stdout( "No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN );
			
			return ExitCode::OK;
			
		}
		else {
			$migrations = (array)$result;
		}
		
		$total = count( $migrations );
		$n     = count( $migrations );
		if( $n === $total ) {
			$this->stdout( "Total $n new " . ( $n === 1 ? 'migration' : 'migrations' ) . " to be applied:\n\n", Console::FG_YELLOW );
		}
		else {
			$this->stdout( "Total $n out of $total new " . ( $total === 1 ? 'migration' : 'migrations' ) . " to be applied:\n\n", Console::FG_YELLOW );
		}
		
		$nameLimit = $this->getMigrationNameLimit();
		
		foreach( $this->_migrationsByGroup as $path => $group ) {
			
			$this->stdout( "$path\n" );
			
			foreach( $group as $class => $file ) {
				
				if( $nameLimit !== null && strlen( $class ) > $nameLimit ) {
					
					$this->stdout( "\nThe migration name '$class' is too long. Its not possible to apply this migration.\n", Console::FG_RED );
					
					return ExitCode::UNSPECIFIED_ERROR;
				}
				
				$this->stdout( "\t$class\n" );
			}
		}
		
		$this->stdout( "\n" );
		
		$applied = 0;
		if( $this->confirm( 'Apply the above ' . ( $n === 1 ? 'migration' : 'migrations' ) . '?' ) ) {
			foreach( $migrations as $migration ) {
				if( !$this->migrateUp( $migration ) ) {
					$this->stdout( "\n$applied from $n " . ( $applied === 1 ? 'migration was' : 'migrations were' ) . " applied.\n", Console::FG_RED );
					$this->stdout( "\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED );
					
					return ExitCode::UNSPECIFIED_ERROR;
				}
				$applied++;
			}
			
			$this->stdout( "\n$n " . ( $n === 1 ? 'migration was' : 'migrations were' ) . " applied.\n", Console::FG_GREEN );
			$this->stdout( "\nOrder of applied migrations:\n\n", Console::FG_GREEN );
			
			/**
			 * $this->_applied includes db history
			 */
			foreach( $this->_applied as $class => $val ) {
				if( isset( $this->_migrations[ $class ] ) ) {
					$this->stdout( "\t$class\n", Console::FG_GREEN );
				}
			}
			
			$this->stdout( "\nMigrated up successfully.\n", Console::FG_GREEN );
		}
	}
	
	protected function getNewMigrations( $data = null, $sort = self::SORT_BY_GROUP )
	{
		$migrations = [];
		
		if( is_numeric( $data ) && (int)$data ) {
			$limit = (int)$data;
		}
		else {
			$limit = null;
		}
		
		if( is_array( $data ) ) {
			$migrationPaths = $data;
		}
		else {
			$migrationPaths = array_merge( ( $this->migrationPath ?? [] ), ( $this->migrationNamespaces ?? [] ) );
		}
		
		foreach( $migrationPaths as $key => $path ) {
			if( $paths = FileHelper::resolvePath( $path, [ 'filter' => '/migration/' ] ) ) {
				
				foreach( $migrationPaths as $key => $searchPath ) {
					if( $searchPath == $path ) {
						array_splice( $migrationPaths, $key, 1, $paths );
						break;
					}
				}
				
			}
			else {
				unset( $migrationPaths[ $key ] );
			}
		}
		
		if( is_array( $data ) ) {
			$this->migrationPath = array_unique( array_merge( $this->migrationPath, $migrationPaths ) );
		}
		else {
			$this->migrationPath = $migrationPaths;
		}
		
		foreach( $migrationPaths as $path ) {
			
			if( is_dir( $path ) ) {
				$files = FileHelper::findFiles( $path, [ 'only' => [ '*.php' ], 'recursive' => false ] );
			}
			else if( is_file( $path ) ) {
				$files = [ $path ];
			}
			else {
				continue;
			}
			
			if( $files ) {
				
				$migrationsGroup = [];
				
				foreach( $files as $file ) {
					
					if( preg_match( '/(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches ) && is_file( $file ) ) {
						
						require_once( $file );
						
						$class = $matches[1];
						
						if( $ns = FileHelper::extract_namespace( $file ) ) {
							$class = $ns . '\\' . $class;
						}
						
						$time = str_replace( '_', '', $matches[2] );
						
						$suffix = preg_replace( '/.*' . $this->defaultDir . '(.*)\.php/', '$1', $file );
						
						if( !$this->_isApplied( $class ) ) {
							
							$migrationsGroup[ $time . $suffix ] = $class;
							
							if( !isset( $this->_migrationsByGroup[ pathinfo( $file )['dirname'] ][ $class ] ) ) {
								$this->_migrationsByGroup[ pathinfo( $file )['dirname'] ][ $class ] = $file;
								$this->_migrations[ $class ]                                        = $file;
							}
							
						}
					}
				}
				
				// time sort
				ksort( $migrationsGroup );
				$migrations = array_merge( $migrations, $migrationsGroup );
			};
			
		}
		
		$migrations = array_unique( $migrations );
		
		if( $sort == self::SORT_COMMON ) {
			ksort( $migrations );
		}
		
		if( (int)$limit > 0 ) {
			$migrations = array_slice( $migrations, 0, $limit );
		}
		
		return $migrations;
	}
	
	/**
	 * Creates a new migration instance.
	 * @param string $class the migration class name
	 * @return \yii\db\Migration the migration instance
	 */
	protected function createMigration( $class )
	{
		return Yii::createObject( [
			'class'   => $class,
			'db'      => $this->db,
			'compact' => $this->compact,
			'mode'    => $this->alterMode,
		] );
	}
	
	protected function addMigrationHistory( $version )
	{
		if( !preg_match( '/_dev$/', $version ) ) {
			parent::addMigrationHistory( $version ); // TODO: Change the autogenerated stub
		}
	}
	
	protected function _isApplied( $class, $strict = false )
	{
		if( !$this->_applied ) {
			foreach( $this->getMigrationHistory( null ) as $class => $time ) {
				$this->_applied[ trim( $class, '\\' ) ] = true;
			}
		}
		
		if( $strict && isset( $this->_applied[ $class ] ) ) {
			return true;
		}
		else {
			
			foreach( $this->_applied ?? [] as $key => $value ) {
				if( strpos( $key, ( new\ReflectionClass( $class ) )->getShortName() ) !== false
					|| ( is_subclass_of( $class, $key ) && !is_subclass_of( $class, yozh\base\components\db\Migration::class ) )
				) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	protected function _processDependencies( array $dependencies )
	{
		foreach( $dependencies as $key => $dependency ) {
			if( is_string( $dependency ) && isset( $this->_migrations[ $dependency ] ) ) {
				$dependencies[ $key ] = $this->_migrations[ $dependency ];
			}
		}
		
		$migrations = $this->getNewMigrations( $dependencies );
		
		foreach( $migrations as $class ) {
			
			if( !$this->migrateUp( $class ) ) {
				return false;
			}
			
		}
		
		return true;
	}
	
	protected function migrateUp( $class )
	{
		if( $class == 'yozh\ysell\migrations\product\namespaced\m000000_000000_ysell_product_dev' ) {
			$trap = 1;
		}
		
		if( is_subclass_of( $class, Migration::class ) && $class::$depends ) {
			
			if( !$this->_processDependencies( $class::$depends ) ) {
				return false;
			}
			
		}
		
		if( $this->_isApplied( $class ) ) {
			return true;
		}
		else if( parent::migrateUp( $class ) ) {
			
			$this->_applied[ $class ] = true;
			
			return true;
		}
		
		return false;
		
	}
	
	
}