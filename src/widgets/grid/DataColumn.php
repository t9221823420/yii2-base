<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 13.05.2018
 * Time: 14:39
 */

namespace yozh\base\widgets\grid;

use yii\helpers\Html;

class DataColumn extends \yii\grid\DataColumn
{
	protected static $_columnNumber = 0;
	protected static $_rowNumber    = 1;
	
	protected static $_columnSpanCounter = 1;
	protected static $_rowSpanCounter    = [];
	
	public $colSpan = 1;
	public $rowSpan = 1;
	
	public function renderDataCell( $model, $key, $index )
	{
		if( static::$_columnNumber == count( $this->grid->columns ) ) {
			static::$_columnNumber      = 0;
			static::$_columnSpanCounter = 1;
			static::$_rowNumber++;
		}
		
		static::$_columnNumber++;
		
		if( $this->contentOptions instanceof Closure ) {
			$options = call_user_func( $this->contentOptions, $model, $key, $index, $this );
		}
		else {
			$options = $this->contentOptions;
		}
		
		if( $this->colSpan instanceof \Closure ) {
			$colSpan = call_user_func( $this->colSpan, $model, $key, $index, $this );
		}
		else {
			$colSpan = $this->colSpan;
		}
		
		if( $this->rowSpan instanceof \Closure ) {
			$rowSpan = call_user_func( $this->rowSpan, $model, $key, $index, $this );
		}
		else {
			$rowSpan = $this->rowSpan;
		}
		
		if( !isset( static::$_rowSpanCounter[ static::$_columnNumber ] ) ) {
			static::$_rowSpanCounter[ static::$_columnNumber ] = 1;
		}
		
		if( static::$_columnSpanCounter == 1 && static::$_rowSpanCounter[ static::$_columnNumber ] == 1 ) {
			
			static::$_rowSpanCounter[ static::$_columnNumber ] = $rowSpan;
			
			static::$_columnSpanCounter = $colSpan;
			
			if( $rowSpan > 1 ) {
				$options['rowspan'] = $rowSpan;
			}
			
			if( $colSpan > 1 ) {
				$options['colspan'] = $colSpan;
			}
			
			return Html::tag( 'td', $this->renderDataCellContent( $model, $key, $index ), $options );
		}
		
		if( static::$_rowSpanCounter[ static::$_columnNumber ] > 1 ) {
			static::$_rowSpanCounter[ static::$_columnNumber ]--;
		}
		
		if( static::$_columnSpanCounter > 1 ) {
			static::$_columnSpanCounter--;
		}
		
	}
	
}