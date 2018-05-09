<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 08.05.2018
 * Time: 15:22
 */

namespace yozh\base\traits;

trait ActiveQueryTrait
{
	public function getRawTableName( $alias = null )
	{
		
		if( !$alias ) {
			list( $tableName, $alias ) = $this->getTableNameAndAlias();
		}
		
		return \Yii::$app->db->schema->getRawTableName( $alias );
		
	}
	
	protected function getTableNameAndAlias()
	{
		if( empty( $this->from ) ) {
			$tableName = $this->getPrimaryTableName();
		}
		else {
			$tableName = '';
			foreach( $this->from as $alias => $tableName ) {
				if( is_string( $alias ) ) {
					return [ $tableName, $alias ];
				}
				break;
			}
		}
		
		if( preg_match( '/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches ) ) {
			$alias = $matches[2];
		}
		else {
			$alias = $tableName;
		}
		
		return [ $tableName, $alias ];
	}
}