<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 24.10.2017
 * Time: 22:48
 */

namespace yozh\base\components\helpers;

use Yii;
use yii\base\Model;

class Inflector extends \yii\helpers\Inflector
{
    public static function resolveTableName($tableName)
    {
        $db = Yii::$app->db;

        $tablePrefix = $db->tablePrefix ?? '';

        preg_match( '/(?<tableName>[\w_-]+)/', $tableName, $matches );

        return $tablePrefix . $matches[ 'tableName' ];
    }
	
	public static function firstCaps( $value )
	{
		$value  = static::humanize($value, true);
		
		if( preg_match_all( '/(?<=\s|^|-|_)[A-Z]/', $value, $matches ) ){
			$value = implode( '', $matches[0] );
		}
		
		return $value;
	}
 
	public static function attributes2id( Model $Model, Array $attributes = null )
	{
		return static::array2html( $Model->getAttributes( $attributes ) );
	}
	
	public static function array2html($value)
    {
        return htmlspecialchars( json_encode( $value ) );
    }

    public static function html2array($value)
    {
        return json_decode( htmlspecialchars_decode( $value ), true );
    }
	
}