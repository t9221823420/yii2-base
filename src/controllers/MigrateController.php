<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 25.03.2018
 * Time: 17:06
 */

namespace yozh\base\controllers;

use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yozh\base\traits\controllers\ConsoleControllerTrait;

class MigrateController extends \yii\console\controllers\MigrateController
{
	use ConsoleControllerTrait;
	
	protected $_dropIdxs;
	
	protected $_truncateIdxs;
	
	protected $_dropFks;
	
	public function options( $actionID )
	{
		return array_unique( array_merge( parent::options( $actionID ), [ 'dropFks', 'dropIdxs', 'truncateIdxs', ] ) );
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
	
	public function beforeAction( $action )
	{
		if( parent::beforeAction( $action ) ) {
			
			if( is_string( $this->migrationPath ) ) {
				$this->migrationPath = [ $this->migrationPath ];
			}
			
			if( is_array( $this->migrationPath ) ) {
				foreach( $this->migrationPath as $path ) {
					if( is_dir( $path ) && $dirs = FileHelper::findDirectories( $path ) ) {
						
						foreach( $dirs as $key => $dir ) {
							if( strpos( $dir, 'namespaced' ) ) {
								unset( $dirs[ $key ] );
							}
						}
						
						$this->migrationPath = array_merge( $this->migrationPath, $dirs );
					}
				}
			}
			
			return true;
		}
		
		return false;
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
	public function actionUp( $data = 0 )
	{
		$result = $this->getNewMigrations( $data );
		
		if( $result == ExitCode::OK ) {
			return ExitCode::OK;
		}
		else {
			$migrations = (array)$result;
		}
		
		$total = count( $migrations );
		$n     = count( $migrations );
		if( $n === $total ) {
			$this->stdout( "Total $n new " . ( $n === 1 ? 'migration' : 'migrations' ) . " to be applied:\n", Console::FG_YELLOW );
		}
		else {
			$this->stdout( "Total $n out of $total new " . ( $total === 1 ? 'migration' : 'migrations' ) . " to be applied:\n", Console::FG_YELLOW );
		}
		
		foreach( $migrations as $key => $migration ) {
			
			$nameLimit = $this->getMigrationNameLimit();
			
			if( $nameLimit !== null && strlen( $migration ) > $nameLimit ) {
				
				$this->stdout( "\nThe migration name '$migration' is too long. Its not possible to apply this migration.\n", Console::FG_RED );
				
				return ExitCode::UNSPECIFIED_ERROR;
			}
			
			$migrationPath = preg_replace( '/\d+(.*)/', '$1', $key );
			
			$this->stdout( "\t$migrationPath\n" );
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
			$this->stdout( "\nMigrated up successfully.\n", Console::FG_GREEN );
		}
	}
	
	/**
	 * added $dataas mixed
	 * - if $data is array - expects list of migration names.
	 * - if $data is integer - used instead of $limit from @see \yii\console\controllers\MigrateController::actionUp()
	 * ( static::actionUp does not use $limit at all)
	 *
	 * @param null $data
	 * @return array|int|null
	 *
	 * if $data is array - expects list of migration names. ca
	 */
	protected function getNewMigrations( $data = null )
	{
		$applied = [];
		
		foreach( $this->getMigrationHistory( null ) as $class => $time ) {
			$applied[ trim( $class, '\\' ) ] = true;
		}
		
		/**
		 * this block used by actionUp after actionMask prepares list of migrations
		 */
		if( is_array( $data ) && count( $data ) ) {
			$migrations = $data;
		}
		else {
			
			$migrationPaths = [];
			
			if( is_array( $this->migrationPath ) ) {
				foreach( $this->migrationPath as $path ) {
					$migrationPaths[] = [ $path, '' ];
				}
			}
			else if( !empty( $this->migrationPath ) ) {
				$migrationPaths[] = [ $this->migrationPath, '' ];
			}
			foreach( $this->migrationNamespaces as $namespace ) {
				$migrationPaths[] = [ $this->getNamespacePath( $namespace ), $namespace ];
			}
			
			$migrations = [];
			
			foreach( $migrationPaths as $item ) {
				
				list( $migrationPath, $namespace ) = $item;
				
				if( is_dir( $migrationPath ) ) {
					$files = FileHelper::findFiles( $migrationPath, [ 'only' => [ '*.php' ], 'recursive' => false ] );
				}
				else if( is_file( $migrationPath ) ) {
					$files = [ $migrationPath ];
				}
				else {
					continue;
				}
				
				if( $files ) {
					
					$migrationsGroup = [];
					
					foreach( $files as $file ) {
						
						if( preg_match( '/(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches ) ) {
							
							$class = $matches[1];
							
							if( !empty( $namespace ) ) {
								$class = $namespace . '\\' . $class;
							}
							
							$time = str_replace( '_', '', $matches[2] );
							
							$suffix = preg_replace( '/.*migrations(.*)\.php/', '$1', $file );
							
							if( !isset( $applied[ $class ] ) ) {
								
								$migrationsGroup[ $time . $suffix ] = $class;
								
							}
						}
					}
					
					// time sort
					ksort( $migrationsGroup );
					$migrations = array_merge( $migrations, $migrationsGroup );
				};
				
			}
			
			$migrations = array_unique( $migrations );
			
			if( empty( (array)$migrations ) ) {
				
				$this->stdout( "No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN );
				
				return ExitCode::OK;
			}
			
			if( is_numeric( $data ) ) {
				$data = (int)$data;
				
				if( $data > 0 ) {
					$migrations = array_slice( $migrations, 0, $data );
				}
			}
			
		}
		
		return $migrations;
	}
	
	protected function addMigrationHistory( $version )
	{
		if( !preg_match( '/_dev$/', $version ) ) {
			parent::addMigrationHistory( $version ); // TODO: Change the autogenerated stub
		}
	}
}