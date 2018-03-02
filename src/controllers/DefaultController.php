<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:57
 */

namespace yozh\base\controllers;

use yozh\properties\models\PropertiesModel;

use yii\web\Controller;
use yii\filters\AccessControl;

class DefaultController extends Controller
{
	protected static function primaryModel()
	{
		return PropertiesModel::className();
	}
	
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => [ '@' ],
					],
				],
			],
		];
	}
	
	
}
