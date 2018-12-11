<?php

namespace yozh\base\models;

use Yii;
use yii\base\Model;
use yozh\base\traits\DBRecordTrait;
use yozh\base\traits\ObjectTrait;
use yozh\base\traits\YiiActiveRecordTrait;

class BaseStaticRecord extends Model
{
	use ObjectTrait, DBRecordTrait, YiiActiveRecordTrait;
	
	/**
	 * @event Event an event that is triggered when the record is initialized via [[init()]].
	 */
	const EVENT_INIT = 'init';

	/**
	 * @event Event an event that is triggered after the record is created and populated with query result.
	 */
	const EVENT_AFTER_FIND = 'afterFind';
}