<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.12.2018
 * Time: 9:42
 */

namespace yozh\base\components\helpers;

use Yii;
use Closure;

class FileHelper extends \yii\helpers\FileHelper
{
	public static function resolvePath( $path, $options = [] )
	{
		$paths = null;
		
		if( Yii::$app->extensions[ $path ]['alias'] ?? false ) {
			
			foreach( Yii::$app->extensions[ $path ]['alias'] as $alias => $aliasPath ) {
				
				$paths[] = $aliasPath;
				
			}
			
		}
		else if( ( $result = Yii::getAlias( '@' . str_replace( '\\', '/', $path ), false ) )
			|| $result = Yii::getAlias( $path, false )
		) {
			$paths[] = $result;
		}
		
		foreach( $paths as $resolvedPath ) {
			if( is_dir( $resolvedPath ) && ( $options['recursive'] ?? true )
				&& $dirs = static::findDirectories( $resolvedPath, $options )
			) {
				array_unshift( $dirs, $resolvedPath );
				
				foreach( $paths as $key => $searchPath ) {
					if( $searchPath == $resolvedPath ) {
						array_splice( $paths, $key, 1, $dirs );
						break;
					}
				}
				
			}
		}
		
		foreach( $paths as $key => $path ) {
			
			if( !file_exists( $path ) || !static::filterPath( $path, $options ) ) {
				
				unset( $paths[ $key ] );
				
			}
			else {
				$paths[ $key ] = static::normalizePath( $path );
			}
			
		}
		
		return $paths;
	}
	
	public static function filterPath( $path, $options )
	{
		$filterResult = true;
		
		if( $options['filter'] ?? false ) {
			
			if( $options['filter'] instanceof Closure ) {
				$filterResult = call_user_func( $options['filter'], $path );
			}
			else if( is_string( $options['filter'] ) ) {
				$filterResult = preg_match( $options['filter'], $path );
			}
			
			unset( $options['filter'] );
		}
		
		if( $filterResult && ( isset( $options['only'] ) || isset( $options['except'] ) ) ) {
			
			$options = static::_setBasePath( pathinfo( $path )['dirname'], $options );
			
			return parent::filterPath( $path, $options );
		}
		
		return $filterResult;
	}
	
	public static function extract_namespace( $file )
	{
		$ns     = null;
		$handle = fopen( $file, "r" );
		if( $handle ) {
			while( ( $line = fgets( $handle ) ) !== false ) {
				if( strpos( $line, 'namespace' ) === 0 ) {
					$parts = explode( ' ', $line );
					$ns    = rtrim( trim( $parts[1] ), ';' );
					break;
				}
			}
			fclose( $handle );
		}
		
		return $ns;
	}
	
	/**
	 * @param string $dir
	 */
	protected static function _setBasePath( $dir, $options )
	{
		if( !isset( $options['basePath'] ) ) {
			// this should be done only once
			$options['basePath'] = realpath( $dir );
			$options             = static::normalizeOptions( $options );
		}
		
		return $options;
	}
}