<?php
namespace yozh\base;

use Yii;

class AssetBundle extends \yii\web\AssetBundle
{
	public $sourcePath = __DIR__ .'/../assets/';
	
	public $css = [
		'css/yozh-base-bootstrap.css',
		'css/yozh-base-yii.css',
		//'css/yozh-base.css',
		//['css/yozh-base.print.css', 'media' => 'print'],
	];
	
	public $js = [
		'js/yozh-base.js'
	];
	
	public $publishOptions = [
		'forceCopy'       => true,
	];
	
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	];

}