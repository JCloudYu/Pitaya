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

		public static function GenPass($length, $caseSensitive = TRUE, $withSymbol = TRUE)
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

