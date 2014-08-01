<?php
/**
 * License - rsa.php
 * Created by JCloudYu on 2013/08/24 11:25
 */
	using('kernel.basis.PBObject');

	class RSA extends PBObject
	{
		const DEFAULT_KEY_LENGTH = 1024;
		const DEFAULT_TAILING_SIZE = 11;

		public static function Key($keyInfo = NULL)
		{
			// INFO: Preprocessing
			if (empty($keyInfo))	// 0, '', NULL, array()
			{
				$keyLength = self::DEFAULT_KEY_LENGTH;
				$keyInfo = NULL;
			}
			else
			if (is_int($keyInfo))
			{
				$keyLength = $keyInfo;
				$keyInfo = NULL;
			}
			else
			if (!is_string($keyInfo))
				return NULL;



			// $keyInfo will be ether NULL nor string from now on....


			if (empty($keyInfo))
			{
				$keyInst = openssl_pkey_new(array("private_key_bits"	=> $keyLength,
												  "private_key_type"	=> OPENSSL_KEYTYPE_RSA));

				$keyType = 'private';
			}
			else
			{
				$keyInst = openssl_pkey_get_private($keyInfo);
				if ($keyInst)
				{
					$keyType = 'private';
				}
				else
				{
					$keyInst = openssl_pkey_get_public($keyInfo);
					if ($keyInst) $keyType = 'public';
					else
					{
						return NULL;
					}
				}

			}



			$keyInfo = openssl_pkey_get_details($keyInst);


			$inst = new RSA();
			$inst->_hKey		= $keyInst;
			$inst->_keyLen		= $keyInfo['bits'];
			$inst->_keyType		= $keyType;

			$inst->_chunkSize	= intval(floor(floatval($keyInfo['bits']) / 8.0));

			$inst->_hKeyAlt = ($inst->_keyType == 'private') ? openssl_pkey_get_public($keyInfo['key']) : $inst->_hKey;

			return $inst;
		}

		private $_hKey = NULL;
		private $_hKeyAlt = NULL;
		private $_keyLen = self::DEFAULT_KEY_LENGTH;
		private $_keyType = NULL;

		private function __construct(){}

		public function __get_publicKey()
		{
			$info = @openssl_pkey_get_details($this->_hKey);
			return $info['key'];
		}

		public function __get_privateKey()
		{
			@openssl_pkey_export($this->_hKey, $key);
			return empty($key) ? '' : $key;
		}

		public function __get_bits() { return $this->_keyLen; }

		public function __get_type() { return $this->_keyType; }


		// INFO: Encryption and Decryption
		private $_chunkSize = 0;
		public function encrypt($data)
		{
			$chunks = str_split($data, $this->_chunkSize - self::DEFAULT_TAILING_SIZE);

			$result = '';
			foreach ($chunks as $chunk)
			{
				if (openssl_public_encrypt($chunk, $encrypted, $this->_hKeyAlt))
					$result .= base64_encode($encrypted);
				else
					return '';
			}

			return $result;
		}

		public function decrypt($data)
		{
			if ($this->_keyType == 'public') return '';

			$result = '';
			$chunks = str_split($data, intval(ceil(floatval($this->_chunkSize) / 3.0) * 4));

			foreach ($chunks as $chunk)
			{
				if (openssl_private_decrypt(base64_decode($chunk), $decrypted, $this->_hKey))
					$result .= $decrypted;
				else
					return '';
			}

			return $result;
		}
	}
