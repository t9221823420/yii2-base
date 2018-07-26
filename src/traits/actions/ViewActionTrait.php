<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.06.2018
 * Time: 20:33
 */

namespace yozh\base\traits\actions;

trait ViewActionTrait
{
	
	public function process( $id )
	{
		return [
			'Model' => $this->controller->findModel( $id ),
		];
	}
	
	
	public function run( $id )
	{
		return $this->controller->render( $this->id, $this->process( $id ) );
	}
}