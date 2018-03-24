<?php

namespace yozh\base\models;

use Yii;

abstract class Model extends \yii\db\ActiveRecord
{
	
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
    
}
