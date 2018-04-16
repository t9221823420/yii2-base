<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 14.04.2018
 * Time: 19:50
 */

namespace yozh\base\widgets;

use yii\data\Pagination;
use yii\jui\Widget;
use yii\bootstrap\Html;
use yozh\base\components\ArrayHelper;

class LimitPager extends Widget
{
	
	/**
	 * @var array the HTML attributes for the container tag. The following special options are recognized:
	 *
	 * - tag: string, defaults to "div", the name of the container tag.
	 *
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 * @since 2.0.1
	 */
	public $containerOptions = [];
	
	public $dropdownOptions = [];
	
	/**
	 * @var array the HTML attributes of the button.
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $options = [];
	
	/**
	 * @var string name of a class to use for rendering dropdowns withing this widget. Defaults to [[Dropdown]].
	 * @since 2.0.7
	 */
	public $dropdownClass = 'yii\bootstrap\Dropdown';
	
	public $limits;
	
	public $caret = '<b class="caret"></b>';
	
	/**
	 * @var Pagination the pagination object that this pager is associated with.
	 * You must set this property in order to make LinkPager work.
	 */
	public $pagination;
	
	public function init()
	{
		parent::init();
		
		if( !is_array( $this->limits ) ) {
			throw new InvalidConfigException( 'The "limits" property must be set as Array.' );
		}
		
		if( !$this->pagination instanceof Pagination) {
			throw new InvalidConfigException( 'The "pagination" property have to be an instance of yii\data\Pagination.' );
		}
	}
	
	public function run()
	{
		Html::addCssClass( $this->containerOptions, [ 'widget' => 'pagination-limits dropdown' ] );
		$options = $this->containerOptions;
		$tag     = ArrayHelper::remove( $options, 'tag', 'div' );
		
		return implode( "\n", [
			Html::beginTag( $tag, $options ),
			$this->_renderButton(),
			$this->_renderDropdown(),
			Html::endTag( $tag ),
		] );
		
	}
	
	protected function _renderButton()
	{
		$this->options['data-toggle'] = 'dropdown';
		
		Html::addCssClass( $this->options, [ 'toggle' => 'dropdown-toggle' ] );
		
		$label = $this->pagination->getPageSize() . " {$this->caret}";
		
		$tag = ArrayHelper::remove( $this->options, 'tag', 'div' );
		$id  = ArrayHelper::remove( $this->options, 'id' );
		
		return Html::tag( $tag, $label, $this->options );
	}
	
	protected function _renderDropdown()
	{
		
		$currentPage = $this->pagination->getPage();
		
		$items = [];
		foreach( $this->limits as $value => $label ) {
			$items[] = [
				'label' => $label,
				'url'   => $this->pagination->createUrl( $currentPage, $value ),
			];
		}
		
		$config = $this->dropdownOptions;
		
		$config['items'] = $items;
		$config['options']['id'] = false;
		
		/** @var Widget $dropdownClass */
		$dropdownClass = $this->dropdownClass;
		
		return $dropdownClass::widget( $config );
	}
	
}