<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 19:38
 */

namespace yozh\base\components\db;

use yii\base\BaseObject;
use yozh\base\components\Migration;

/**
 * Class Schema
 * @package yozh\base\components\db
 * @used-by Migration::enum() && Migration::set()
 */
class Schema extends \yii\db\mysql\Schema
{
	const TYPE_ENUM = 'enum';
	const TYPE_SET  = 'set';
	
	const SERVICE_FIELD_CREATED_AT = 'created_at';
	const SERVICE_FIELD_UPDATED_AT = 'updated_at';
	const SERVICE_FIELD_DELETED_AT = 'deleted_at';
	const SERVICE_FIELD_CREATED_BY = 'created_by';
	const SERVICE_FIELD_UPDATED_BY = 'updated_by';
	const SERVICE_FIELD_DELETED_BY = 'deleted_by';
	const SERVICE_FIELD_ENABLED    = 'enabled';
	const SERVICE_FIELD_ACTIVE     = 'active';
	
	protected static $_instance;
	
	public function __construct( array $config = [] )
	{
		parent::__construct( $config );
		
		static::$_instance->typeMap['enum'] = static::TYPE_ENUM;
		static::$_instance->typeMap['set']  = static::TYPE_SET;
		
	}
	
	
}