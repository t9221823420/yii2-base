<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 06.09.2018
 * Time: 14:23
 */

namespace yozh\base\components\validators;

class ReadOnlyValidator extends BaseValidator
{
	public function validateAttribute( $Model, $attribute )
	{
		if( !$Model->isNewRecord && $Model->getOldAttribute( $attribute ) != $Model->getAttribute( $attribute ) ) {
			$Model->setAttribute( $attribute, $Model->getOldAttribute( $attribute ) );
		}
	}
}