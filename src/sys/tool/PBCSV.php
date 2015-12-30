<?php
/**
 * 1003.IMSIS - PBCSV.php
 * Created by JCloudYu on 2014/06/16 13:34
 */
	using ('kernel.basis.PBObject');
	using ('sys.prototype.IDataFetcher');

	final class PBCSV extends PBObject implements IDataFetcher
	{
		public static function ToCSV($data, $encoding = 'UTF-8', $fEnc = 'UTF-8')
		{
			if (!is_array($data)) $data = array($data);

			$fields = array();
			foreach ($data as $val)
			{
				$val = str_replace("\"", "\"\"", "{$val}");
				$fields[] = "\"{$val}\"";
			}

			$result = implode(',', $fields);

			if ($encoding != $fEnc)
				$result = iconv($fEnc, $encoding, $result);

			return $result;
		}

		public static function FromFile($path, $encoding = 'UTF-8')
		{
			if (is_dir($path) || !is_readable($path)) return NULL;
			return new PBCSV(fopen($path, "rb"), $encoding);
		}

		public static function FromString($string, $encoding = 'UTF-8')
		{
			if (!is_string($string)) return NULL;
			$stream = tmpfile();
			fwrite($stream, $string);
			rewind($stream);

			return new PBCSV($stream, $encoding);
		}

		public static function FromStream($stream, $encoding = 'UTF-8')
		{
			if (!is_resource($stream)) return NULL;
			return new PBCSV($stream, $encoding);
		}



		private $_stream		  = NULL;
		private $_dataEncoding	  = '';

		private $_dataHeader	  = array();

		public function __construct($stream, $streamEncoding = 'UTF-8')
		{
			$this->_stream		 = $stream;
			$this->_dataEncoding = $streamEncoding;
		}

		public function __set_header($value)
		{
			if (is_string($value))
				$this->_dataHeader = explode(',', $value);
			else
			if (is_array($value))
				$this->_dataHeader = $value;
			else
				$this->_dataHeader = array();
		}

		public function __get_header() { return $this->_dataHeader; }



		public function fetch($fetchOptions = IDataFetcher::FETCH_BOTH, $encoding = 'UTF-8')
		{
			if (feof($this->_stream)) return FALSE;

			$rawData = fgetcsv($this->_stream);

			if ($this->_dataEncoding != $encoding)
				foreach ($rawData as $idx => $val) $rawData[$idx] = iconv($this->_dataEncoding, $encoding, "{$val}");

			switch ($fetchOptions)
			{
				case IDataFetcher::FETCH_BOTH:
					foreach ($rawData as $index => $val)
						if (isset($this->_dataHeader[$index])) $rawData["{$this->_dataHeader[$index]}"] = $val;
					break;
				case IDataFetcher::FETCH_ASSOC:
					$assoc = array();
					foreach ($rawData as $index => $val)
						if (isset($this->_dataHeader[$index])) $assoc["{$this->_dataHeader[$index]}"] = $val;
					$rawData = $assoc;
					break;
				case IDataFetcher::FETCH_OBJ:
					$obj = new stdClass();
					foreach ($rawData as $index => $val)
						if (isset($this->_dataHeader[$index])) $obj->{"{$this->_dataHeader[$index]}"} = $val;
					$rawData = $obj;
					break;
				default:
					break;
			}

			return $rawData;
		}

		public function fetchAll($fetchOptions = IDataFetcher::FETCH_BOTH, $encoding = 'UTF-8')
		{
			$data = array();
			while ( ($raw = $this->fetch( $fetchOptions, $encoding )) !== FALSE ) $data[] = $raw;

			return $data;
		}
	}
