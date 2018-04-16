<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 13.04.2018
 * Time: 6:47
 */

namespace yozh\base\source;

class Encoder
{
	
	const ENCODE = 'encode';
	const DECODE = 'decode';
	
	//const $codeset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	//readable character set excluded (0,O,1,l)
	const codeset = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
	
	static function intBaseEncode( $n )
	{
		$base      = strlen( self::codeset );
		$converted = '';
		
		while( $n > 0 ) {
			$converted = substr( self::codeset, bcmod( $n, $base ), 1 ) . $converted;
			$n         = self::bcFloor( bcdiv( $n, $base ) );
		}
		
		return $converted;
	}
	
	static function intBaseDecode( $code )
	{
		$base = strlen( self::codeset );
		$c    = '0';
		for( $i = strlen( $code ); $i; $i-- ) {
			$c = bcadd( $c, bcmul( strpos( self::codeset, substr( $code, ( -1 * ( $i - strlen( $code ) ) ), 1 ) )
				, bcpow( $base, $i - 1 ) ) );
		}
		
		return bcmul( $c, 1, 0 );
	}
	
	static private function bcFloor( $x )
	{
		return bcmul( $x, '1', 0 );
	}
	
	static private function bcCeil( $x )
	{
		$floor = bcFloor( $x );
		
		return bcadd( $floor, ceil( bcsub( $x, $floor ) ) );
	}
	
	static private function bcRound( $x )
	{
		$floor = bcFloor( $x );
		
		return bcadd( $floor, round( bcsub( $x, $floor ) ) );
	}
	
	
	protected static function _opensslCrypt( $string, $action )
	{
		// you may change these values to your own
		$secret_key = 'my_simple_secret_key';
		$secret_iv  = 'my_simple_secret_iv';
		
		$output         = false;
		$encrypt_method = "AES-256-CBC";
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );
		
		if( $action == static::ENCODE ) {
			$output = base64_encode( gzdeflate( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) ) );
		}
		else if( $action == static::DECODE ) {
			$output = openssl_decrypt( gzinflate( base64_decode( $string ) ), $encrypt_method, $key, 0, $iv );
		}
		
		return $output;
	}
	
	public static function opensslEncrypt( $string )
	{
		return static::_opensslCrypt( $string, static::ENCODE);
	}
	
	public static function opensslDecrypt( $string )
	{
		return static::_opensslCrypt( $string, static::DECODE);
	}
	
}