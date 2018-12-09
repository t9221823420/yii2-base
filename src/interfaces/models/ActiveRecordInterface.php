<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 28.05.2018
 * Time: 12:34
 */

namespace yozh\base\interfaces\models;

interface ActiveRecordInterface extends \yii\db\ActiveRecordInterface
{
	public static function getList( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true ): array;
	
	public static function getListQuery( ?array $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true, $alias = null );
	
	public static function shemaReferences();
	
	public function attributes( ?array $only = null, ?array $except = null, ?bool $schemaOnly = false );
	
	public function getRawAttributes( ?array $only = null, ?array $except = [], ?bool $schemaOnly = false );
	
	public function rules( $rules = [], $update = false );
	
}