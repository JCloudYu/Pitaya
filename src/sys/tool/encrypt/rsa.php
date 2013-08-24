<?php
/**
 * License - rsa.php
 * Created by JCloudYu on 2013/08/24 11:25
 */
	class RSA
	{
		private $_publicKey = NULL;
		private $_publicChunkSize = 0;

		private $_privateKey = NULL;
		private $_privateChunkSize = 0;

		private static $_tailingSize = 11;

		public function __construct($public, $private)
		{
			if (empty($public) || empty($private))
				throw(new Exception("Either public key nor private key cannot be empty!"));



			// INFO: Store public key information
			$this->_publicKey = openssl_get_publickey($public);
			$buff = openssl_pkey_get_details($this->_publicKey);
			$this->_publicChunkSize =  intval(floor(floatval($buff['bits']) / 8.0)) - self::$_tailingSize;



			// INFO: Store private key information
			$this->_privateKey = openssl_get_privatekey($private);
			$buff = openssl_pkey_get_details($this->_privateKey);
			$this->_privateChunkSize =  intval(floor(floatval($buff['bits']) / 8.0));
		}

		public function encrypt($data)
		{
			$result = '';
			$chunks = str_split($data, $this->_publicChunkSize);

			$buff = arraY();

			foreach ($chunks as $chunk)
			{
				if (openssl_public_encrypt($chunk, $encrypted, $this->_publicKey))
				{
					$result .= base64_encode($encrypted);
					$buff[] = base64_encode($encrypted);
				}
				else
					return '';
			}

			return $result;
		}

		public function decrypt($data)
		{
			$result = '';
			$chunks = str_split($data, intval(ceil(floatval($this->_privateChunkSize) / 3.0) * 4));
			foreach ($chunks as $chunk)
			{
				if (openssl_private_decrypt(base64_decode($chunk), $decrypted, $this->_privateKey))
					$result .= $decrypted;
				else
					return '';
			}

			return $result;
		}
	}