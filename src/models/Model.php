<?php

namespace yozh\base\models;

use Yii;

class Model extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
	    return $this->attributeEditList();
    }
	
	public function attributeIndexList()
	{
		return $this->attributeEditList();
	}
	
	public function attributeViewList()
	{
		return $this->attributeEditList();
	}
	
	public function attributeCreateList()
	{
		return $this->attributeEditList();
	}
	
	public function attributeUpdateList()
	{
		return $this->attributeEditList();
	}
	
	public function attributeEditList()
	{
		return array_diff( array_keys($this->attributes),  $this->primaryKey(true) );;
	}
    
    /**
     * @return \yii\db\ActiveQuery
     */
	/*
    public function getRelationRecords()
    {
        return $this->hasMany(RefModel::className(), ['ref_id' => 'table_id']);
    }
	*/
}
