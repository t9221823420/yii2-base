<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.06.2018
 * Time: 20:41
 */

namespace yozh\base\actions;

use yozh\base\models\BaseActiveRecord as ActiveRecord;

class CloneAction extends EditAction
{
	public function run( ActiveRecord $Model = null, bool $clone = false )
	{
		return parent::run( $Model, true );
	}
	
}