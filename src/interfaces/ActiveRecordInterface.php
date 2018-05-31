<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 28.05.2018
 * Time: 12:34
 */

namespace yozh\base\intarfaces;

interface ActiveRecordInterface extends \yii\db\ActiveRecordInterface
{
	public static function getList();
	
	public static function getListQuery( $condition = [], $key = null, $value = null, $indexBy = true, $orderBy = true, $alias = null );
}