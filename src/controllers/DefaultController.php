<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 01.03.2018
 * Time: 17:57
 */

namespace yozh\base\controllers;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use yozh\base\traits\controllers\ControllerTrait;

class DefaultController extends Controller
{
	use ControllerTrait;
}
