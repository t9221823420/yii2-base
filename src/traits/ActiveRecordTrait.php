<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 21.04.2018
 * Time: 12:32
 */

namespace yozh\base\traits;

use Yii;
use yozh\base\models\BaseActiveRecord as ActiveRecord;

trait ActiveRecordTrait
{
	use DBRecordTrait, ReadOnlyAttributesTrait;
	
	public function emptyPrimaryKey(): ActiveRecord
	{
		$this->setAttributes( array_fill_keys( $this->primaryKey(), null ), false );
		
		return $this;
	}
	
}