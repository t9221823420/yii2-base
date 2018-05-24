<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 24.10.2017
 * Time: 22:48
 */

namespace yozh\base\components\utils;

use Yii;
use yii\base\Model;

class Inflector extends \yii\helpers\Inflector
{
    /**
     * convert {{%ent_entity_item}} to ent_entity_item
     * @param $tableName
     * @return string
     * @used-by \common\modules\relations\Relation::link
     */
    /*
        public static function tableName()
        {
            return '{{%' . Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '}}';
        }

     */
    public static function resolveTableName($tableName)
    {
        $db = Yii::$app->db;

        $tablePrefix = $db->tablePrefix ?? '';

        preg_match( '/(?<tableName>[\w_-]+)/', $tableName, $matches );

        return $tablePrefix . $matches[ 'tableName' ];
    }
	
	public static function attributes2id( Model $model, Array $attributes = null )
	{
		return static::array2html( $model->getAttributes( $attributes ) );
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