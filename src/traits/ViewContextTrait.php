<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.05.2018
 * Time: 17:20
 */

namespace yozh\base\traits;

use yii\base\Component;
use yii\base\ViewContextInterface;

trait ViewContextTrait
{
	public function getViewPath( $sufix = 'view' )
	{
		if( $this->_viewPath === null ) {
			
			$path = parent::getViewPath();
			
			if( !is_dir( $path ) && $parentPath = $this->getParentPath( $sufix ) ) {
				
				$path = $parentPath;
				
			}
			
			$this->_viewPath = $path;
		}
		
		return $this->_viewPath;
	}
	
	/**
	 * Returns the directory that contains layout view files for this module.
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
	 */
	public function getLayoutPath( $sufix = 'layouts' )
	{
		if( $this->_layoutPath === null ) {
			
			$path = parent::getLayoutPath();
			
			if( !is_dir( $path ) && $parentPath = $this->getParentPath( $sufix ) ) {
				
				$path = $parentPath;
				
			}
			
			$this->_layoutPath = $path;
		}
		
		return $this->_layoutPath;
	}
	
	public function getParentPath( $mixed = null, $sufix = null )
	{
		if( $mixed ) {
			
			if( $mixed instanceof \ReflectionClass ) {
				$class = $mixed;
			}
			else if( is_string( $mixed ) && $sufix ) {
				$class = new \ReflectionClass( $mixed );
			}
			else {
				$class = new \ReflectionClass( $this );
				$sufix = $mixed;
			}
			
		}
		else {
			$class = new \ReflectionClass( $this );
		}
		
		while( ( $class = $class->getParentClass() ) && $class->implementsInterface( ViewContextInterface::class ) ) {
			
			$path = dirname( $class->getFileName() ) . ( $sufix ? DIRECTORY_SEPARATOR . $sufix : null );
			
			if( is_dir( $path ) ) {
				return $path;
			}
		}
		
	}
}