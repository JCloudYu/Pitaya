<?php
	
	final class PBBase64 {
		public static function URLEncode( $data ){
			return strtr(rtrim(base64_encode( $data ), '='), '+/', '-_');
		}

		public static function URLDecode( $data ){
			$length = strlen( $data );
			$repeat = 4 - ($length % 4);
			return base64_decode( strtr( $data . str_repeat( "=", $repeat ), '-_', '+/'), TRUE );
		}

		public static function Encode( $data ){
			return base64_encode($data);
		}

		public static function Decode( $data ) {
			return base64_decode( $data, TRUE );
		}
	}
	
	
	/*
	// Base64 Encoding Decoding Using Native PHP
	final class PBBase64 {
		const BASE64URL_MAP = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
		const BASE64_MAP	= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

		public static function URLEncodde(){
			ob_start();
			self::__ENCODE( $data, self::BASE64URL_MAP, "" );
			return ob_get_clean();
		}

		public static function URLDecode(){
			ob_start();
			self::__DECODE( $data, [ '-' => 62, '_' => 63 ], "" );
			return ob_get_clean();
		}

		public static function Encode( $data ){
			ob_start();
			self::__ENCODE( $data, self::BASE64_MAP, "=" );
			return ob_get_clean();
		}

		public static function Decode( $data ) {
			ob_start();
			self::__DECODE( $data, [ '+' => 62, '/' => 63 ], "=" );
			return ob_get_clean();
		}

		private static function __ENCODE( $data, $map = self::BASE64_MAP, $padChar = "=" ){
			$length	  = strlen($data);
			$dangling = $length % 3;
			$loopEnd  = (($length / 3 ) | 0) * 3;

			for( $i = 0; $i < $loopEnd; $i += 3 ) {
				$value = unpack( 'C', $data[$i])[1] << 16 | unpack( 'C', $data[$i+1])[1] << 8 | unpack( 'C', $data[$i+2])[1];
				


				echo self::BASE64_MAP[ $value >> 18 ];

				$value = ($value << 6) & 0xFFFFFF;
				echo self::BASE64_MAP[ $value >> 18 ];

				$value = ($value << 6) & 0xFFFFFF;
				echo self::BASE64_MAP[ $value >> 18 ];

				$value = ($value << 6) & 0xFFFFFF;
				echo self::BASE64_MAP[ $value >> 18 ];
			}

			if ( $dangling > 0 ) {
				if ( $dangling == 1 ) {
					$value = unpack( 'C', $data[$loopEnd] )[1] << 16;
					echo self::BASE64_MAP[ $value >> 18 ];

					$value = ($value << 6) & 0xFFFFFF;
					echo self::BASE64_MAP[ $value >> 18 ];
					echo "{$padChar}{$padChar}";
				}
				else {
					$value = unpack( 'C', $data[$loopEnd] )[1] << 16 | unpack( 'C',$data[$loopEnd+1] )[1] << 8;
					echo self::BASE64_MAP[ $value >> 18 ];

					$value = ($value << 6) & 0xFFFFFF;
					echo self::BASE64_MAP[ $value >> 18 ];

					$value = ($value << 6) & 0xFFFFFF;
					echo self::BASE64_MAP[ $value >> 18 ];
					echo "{$padChar}";
				}
			}
		}

		private static function __DECODE( $data, $chrMap = [ '+' => 62, '/' => 63 ], $padChar = "=" ){
			$data	  = rtrim( $data, $padChar );
			$length	  = strlen($data);
			$dangling = $length % 4;
			$loopEnd  = (($length / 4 ) | 0) * 4;

			for( $i = 0; $i < $loopEnd; $i += 4 ) {
				$value = self::__DECODE_CHR( $data[$i],   $chrMap ) << 18 |
						 self::__DECODE_CHR( $data[$i+1], $chrMap ) << 12 |
						 self::__DECODE_CHR( $data[$i+2], $chrMap ) << 6  |
						 self::__DECODE_CHR( $data[$i+3], $chrMap );

				echo pack( 'C', $value >> 16 );

				$value = ($value << 8) & 0xFFFFFF;
				echo pack( 'C', $value >> 16 );

				$value = ($value << 8) & 0xFFFFFF;
				echo pack( 'C', $value >> 16 );
			}

			if ( $dangling > 0 ) {
				$value = self::__DECODE_CHR( $data[$loopEnd], $chrMap ) << 18;

				if ( $dangling == 2 ) {
					$value = $value | self::__DECODE_CHR( $data[$loopEnd+1], $chrMap ) << 12;
					echo pack( 'C', $value >> 16 );
				}
				else {
					$value = $value | self::__DECODE_CHR( $data[$loopEnd+1], $chrMap ) << 12 |
									  self::__DECODE_CHR( $data[$loopEnd+2], $chrMap ) << 6;

					echo pack( 'C', $value >> 16 );

					$value = ($value << 8) & 0xFFFFFF;
					echo pack( 'C', $value >> 16 );
				}
			}
		}

		private static function __DECODE_CHR( $chr, $chrMap = [ '+' => 62, '/' => 63 ] ) {
			$ascii = ord( $chr );
			if ( $ascii >= 65 && $ascii <= 90 )  // A-Z
				return $ascii - 65;

			if ( $ascii >= 97 && $ascii <= 122 ) // a-z
				return $ascii - 71;

			if ( $ascii >= 48 && $ascii <= 57 )  // 0-9
				return $ascii + 4;
			
			return $chrMap[ $chr ];
		}
	}
	*/
