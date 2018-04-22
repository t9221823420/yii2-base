<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 7:05
 */

namespace yozh\base\traits\query;

use yozh\base\components\db\Schema;

trait NotDeletedTrait
{
	public function notDeleted( $alias = null )
	{
		$table = $alias ?? call_user_func( [ $this->modelClass, 'tableName' ] );
		
		return $this->andWhere( [ $table . '.' . Schema::SERVICE_FIELD_DELETED_AT => 0, ] );
	}
	
}