<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.04.2018
 * Time: 7:05
 */

namespace yozh\base\traits\query;

use Yii;
use yozh\base\components\db\Schema;
use yozh\base\traits\ActiveQueryTrait;

trait NotDeletedTrait
{
	use ActiveQueryTrait;
	
	public function notDeleted( $alias = null )
	{
		$tableName = $this->getRawTableName( $alias );
		
		return $this->andWhere( [ $tableName . '.' . Schema::SERVICE_FIELD_DELETED_AT => null, ] );
	}
	
}