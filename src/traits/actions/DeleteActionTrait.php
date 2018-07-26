<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.06.2018
 * Time: 20:33
 */

namespace yozh\base\traits\actions;

trait DeleteActionTrait
{
	
	public function run( $id )
	{
		$this->controller->findModel( $id )->delete();
		
		return $this->controller->redirect( [ 'index' ] );
		
	}
}