<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 24.05.2018
 * Time: 9:32
 */

namespace yozh\base\components\utils;

use yozh\base\components\BaseComponent;
use yozh\base\traits\ObjectTrait;

class GD extends BaseComponent
{
	public static function createWatermark( $imagePath, $watermarkPath, $params = null )
	{
		switch( pathinfo( $imagePath, PATHINFO_EXTENSION ) ) {
			
			case 'jpeg':
			case 'jpg':
				
				$imageExt = 'jpeg';
				
				break;
			
			case 'png':
				
				$imageExt = 'png';
				
				break;
			
			default :
				
				return;
		}
		
		switch( pathinfo( $watermarkPath, PATHINFO_EXTENSION ) ) {
			
			case 'jpeg':
			case 'jpg':
				
				$watermarkExt = 'jpeg';
				
				break;
			
			case 'png':
				
				$watermarkExt = 'png';
				
				break;
			
			default :
				
				return;
		}
		
		if( ( $image = call_user_func_array( 'imagecreatefrom' . $imageExt, [ $imagePath ] ) ) &&
			( $watermark = call_user_func_array( 'imagecreatefrom' . $watermarkExt, [ $watermarkPath ] ) )
		) {
			
			$w  = imagesx( $image );
			$h  = imagesy( $image );
			$ww = imagesx( $watermark );
			$wh = imagesy( $watermark );
			
			$img_paste_x = 0;
			while( $img_paste_x < $w ) {
				$img_paste_y = 0;
				while( $img_paste_y < $h ) {
					imagecopy( $image, $watermark, $img_paste_x, $img_paste_y, 0, 0, $ww, $wh );
					$img_paste_y += $wh;
				}
				$img_paste_x += $ww;
			}
			
			ob_start();
			
			call_user_func_array( 'image' . $imageExt, [ $image ] );
			
			imagedestroy( $image );
			imagedestroy( $watermark );
			
			return ob_get_clean();
			
		}
	}
	
}