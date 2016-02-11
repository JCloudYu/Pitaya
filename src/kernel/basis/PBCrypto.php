<?php
/**
 * 1017.NeighborApp - PBCrypto.php
 * Created by JCloudYu on 2015/02/20 03:15
 */
	class PBCrypto
	{
		const CANDIDATES_LOWER_NO_SYM = "0123456789abcdefghijklmnopqrstuvwxyz";
		const CANDIDATES_MIXED_NO_SYM = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		const CANDIDATES_SYMBOLS	  = " !\"$%^&*()-_=+[{]};:'@#~|,<.>/?\\/`";

		public static function GenPass( $length, $caseSensitive = TRUE, $withSymbol = TRUE )
		{
			$candidate = ($caseSensitive) ? self::CANDIDATES_MIXED_NO_SYM : self::CANDIDATES_LOWER_NO_SYM;
			$candidate = str_split( ($withSymbol) ? $candidate . self::CANDIDATES_SYMBOLS : $candidate );
			shuffle($candidate);

			do
			{
				$pass = '';
				while ( strlen($pass) != $length )
				{
					$pass .= $candidate[array_rand($candidate)];
					$pass = trim($pass);
				}
			}
			while( !self::ValidatePass($pass, $caseSensitive, $withSymbol) );

			return $pass;
		}

		public static function GenTOTP( $raw_secret, $refTime = NULL, $length = 6, $alg = 'sha1', $timeQuantum = 30 ) {
			$refTime = ( $refTime === NULL ) ? time() : $refTime;
			return self::GenHOTP( $raw_secret, (($refTime * 1000) / ($timeQuantum * 1000)) | 0, $length, $alg );
		}

		public static function GenHOTP( $raw_secret, $counter, $length = 6, $alg = 'sha1' ) {
			if ( PHP_VERSION_ID >= 50603 )
				$counter = pack( 'J', $counter );
			else
				$counter = str_pad( pack( 'N', $counter ), 8, chr(0), STR_PAD_LEFT );

			$hash = hash_hmac( $alg, $counter, $raw_secret, TRUE );
			$hasLen = strlen( $hash );

			$offset = ord($hash[ $hasLen - 1 ]) & 0xf;
			$otp = (
				((ord($hash[ $offset + 0 ]) & 0x7f) << 24 ) |
				((ord($hash[ $offset + 1 ]) & 0xff) << 16 ) |
				((ord($hash[ $offset + 2 ]) & 0xff) <<  8 ) |
				 (ord($hash[ $offset + 3 ]) & 0xff)
			) % pow( 10, $length );

			return str_pad( $otp, $length, "0", STR_PAD_LEFT );
		}

		public static function ValidatePass($target, $caseSensitive = TRUE, $checkSymbol = TRUE)
		{
			$target	  = str_split($target);
			$checkRes = array('upper' => FALSE, 'lower' => FALSE, 'digit' => FALSE, 'symbol' => FALSE);

			foreach ( $target as $data )
			{
				$val = ord($data);

				if ( $val >= 65 && $val <= 90 ) // A-Z
					$checkRes['upper'] = $checkRes['upper'] || TRUE;
				else
				if ( $val >= 97 && $val <= 122 ) // a-z
					$checkRes['lower'] = $checkRes['lower'] || TRUE;
				else
				if ( $val >= 48 && $val <= 57 ) // 0-9
					$checkRes['digit'] = $checkRes['digit'] || TRUE;
				else
					$checkRes['symbol'] = $checkRes['symbol'] || TRUE;
			}

			$result = $checkRes['lower'];
			$result = $result && $checkRes['digit'];

			if ( $caseSensitive )	$result = $result && $checkRes['upper'];
			if ( $checkSymbol ) $result = $result && $checkRes['symbol'];

			return $result;
		}
	}

