<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 7:05
 */

namespace yozh\base\traits\query;

use yozh\base\components\db\Schema;
use yozh\base\traits\ActiveQueryTrait;

trait EnabledOnlyTrait
{
	use ActiveQueryTrait;

	public function enabledOnly( $alias = null )
	{
		$tableName = $this->getRawTableName( $alias );
		
		return $this->andWhere( [ $tableName . '.' . Schema::SERVICE_FIELD_ENABLED => true, ] );
	}
	
}