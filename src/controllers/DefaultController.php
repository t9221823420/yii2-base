<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:57
 */

namespace yozh\base\controllers;

use yii\db\ActiveRecord;

use yii\web\Controller;
use yii\filters\AccessControl;

class DefaultController extends Controller
{
	protected static function primaryModel()
	{
		return ActiveRecord::className();
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
	
	/**
	 * @param $primaryKey
	 * @return null|static
	 * @throws NotFoundHttpException
	 */
	protected function _findModel( $condition )
	{
		
		/** @var ActiveRecord $primaryModel */
		$primaryModel = static::primaryModel();
		
		if( ( $model = $primaryModel::findOne( $condition ) ) !== null ) {
			return $model;
		}
		
		throw new \yii\web\NotFoundHttpException();
		
	}
	
}
