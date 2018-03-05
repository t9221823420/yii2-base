<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 22:07
 */

namespace yozh\base\components\db;

use yozh\base\components\db\Schema;

class ColumnSchemaBuilder extends \yii\db\ColumnSchemaBuilder
{
	
	const CATEGORY_LIST = 'list';
	
	public $listValues = [];
	
	public function __construct( string $type, $length = null, Connection $db = null, array $config = [] )
	{
		parent::__construct( $type, $length, $db, $config );
		
		$this->categoryMap = array_merge( $this->categoryMap, [
			Schema::TYPE_ENUM => self::CATEGORY_LIST,
			Schema::TYPE_SET  => self::CATEGORY_LIST,
		] );
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function __toString()
	{
		switch( $this->getTypeCategory() ) {
			case self::CATEGORY_LIST:
				$format = $this->type . "('" . implode( "','", $this->listValues ) . "')" . '{notnull}{default}{check}{comment}{append}';
				break;
			default:
				return parent::__toString();
		}
		
		return $this->buildCompleteString( $format );
	}
	
}