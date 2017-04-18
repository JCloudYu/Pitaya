<?php
/**
 * IMSIS - ip.php
 * Created by JCloudYu on 2013/10/11 13:32
 */

	

	function NormalizeIPv6($ipAddr)
	{
		// INFO: Only strings are allowed here...
		if (!is_string($ipAddr)) return NULL;

		$bitFilter = explode('/', $ipAddr);
		if (count($bitFilter) > 2) return NULL;

		$bitFieldSize = is_numeric(@$bitFilter[1]) ? intval($bitFilter[1]) : 128;



		// INFO: Decide whether the given ip address is in shortened format
		$ipComponents = explode('::', $bitFilter[0]);
		$shortened = count($ipComponents);
		// :: only allow once
		if ($shortened > 2) return NULL;
		$shortened = ($shortened > 1) ? TRUE : FALSE;



		// INFO: Validate and fill the components
		$msb = $lsb = $matches = array();

		if (!$shortened)
		{
			// INFO: Detect IPv4 syntax
			$buff = preg_split('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $ipComponents[0], -1, PREG_SPLIT_DELIM_CAPTURE);
			if (count($buff) > 3 || (end($buff) != "")) return NULL;


			if (count($buff) > 1)
			{
				array_pop($buff);
				$ipv4 = ip2long($buff[1]);
				$ipComponents[0] = $buff[0] . sprintf('%x:%x', (($ipv4 >> 16) & 0xFFFF), ($ipv4 & 0xFFFF));
			}



			// Validate MSB part
			if (!preg_match('/(^[0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4})*$)|(^$)/', $ipComponents[0], $matches) || $matches[0] !== $ipComponents[0])
				return NULL;

			// Extract and analysis MSB part
			$buff = explode(':', $ipComponents[0]);
			foreach ($buff as $component) $msb[] = hexdec($component);
		}
		else
		{
			// Validate MSB part
			if (!preg_match('/(^[0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4})*$)|(^$)/', $ipComponents[0], $matches) || $matches[0] !== $ipComponents[0])
				return NULL;

			// Extract and analysis MSB part
			$buff = explode(':', $ipComponents[0]);
			foreach ($buff as $component) $msb[] = hexdec($component);




			// INFO: Detect IPv4 syntax
			$buff = preg_split('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $ipComponents[1], -1, PREG_SPLIT_DELIM_CAPTURE);
			if (count($buff) > 3 || (end($buff) != "")) return NULL;


			if (count($buff) > 1)
			{
				array_pop($buff);
				$ipv4 = ip2long($buff[1]);
				$ipComponents[1] = $buff[0] . sprintf('%x:%x', (($ipv4 >> 16) & 0xFFFF), ($ipv4 & 0xFFFF));
			}

			// Validate LSB part
			if (!preg_match('/^(([0-9a-fA-F]{1,4}:)*[0-9a-fA-F]{1,4})$|^$/', $ipComponents[1], $matches) || $matches[0] !== $ipComponents[1])
				return NULL;

			// Extract and analysis LSB part
			$buff = explode(':', $ipComponents[1]);
			foreach ($buff as $component) $lsb[] = hexdec($component);
		}

		// INFO: Check length
		$totalCount = count($msb) + count($lsb);

		if ($totalCount > 8) return NULL;


		// INFO: Merge the components along with the missing 0s
		$result = array_merge($msb, ary_fill(8 - $totalCount, 0), $lsb);


		// INFO: Generate bit field for subnet masking
		$bitField = array_fill(0, 8, 0x0000FFFF);
		$runner = 128 - $bitFieldSize;
		while ($runner > 0)
		{
			$shift = min(16, $runner);

			$buff = ($bitField[0] << $shift) & 0xFFFF;
			for( $i = 0; $i < 8; $i++)
			{
				$prev = (($i + 1) < 8) ? (($bitField[$i+1] & 0xFFFF) << 16) : 0;
				$value = ($bitField[$i] & 0xFFFF) + $prev;
				$value = $value << $shift;

				$bitField[$i] = $buff;
				$buff = ($value & 0xFFFF0000) >> 16;
			}

			$runner -= $shift;
		}
		$bitField = array_reverse($bitField);


		// INFO: Mask out the final result
		foreach ($bitField as $idx => $mask)
			$result[$idx] = $result[$idx] & $mask;

		return $result;
	}

	function RemoteIP($_SERVER_VAR, $allowPrivate = TRUE, $allowReservedRanged = TRUE)
	{
		if (!is_array($_SERVER_VAR)) return NULL;



		static $seq = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		foreach ($seq as $key)
		{
			if (!empty($_SERVER_VAR[$key]))
			{
				$ipSeq = explode(',', $_SERVER_VAR[$key]);

				foreach ($ipSeq as $ip)
				{
					$ip = trim($ip);

					$filterOpt =  0;

					if (!$allowReservedRanged) $filterOpt = $filterOpt | FILTER_FLAG_NO_RES_RANGE;
					if (!$allowPrivate) $filterOpt = $filterOpt | FILTER_FLAG_NO_PRIV_RANGE;




					$checkIPV4 = (bool) filter_var($ip, FILTER_VALIDATE_IP, $filterOpt | FILTER_FLAG_IPV4);
					if ($checkIPV4) return $ip;


					$checkIPV6 = (bool) filter_var($ip, FILTER_VALIDATE_IP, $filterOpt | FILTER_FLAG_IPV6);
					if ($checkIPV6) return $ip;
				}
			}
		}

		return NULL;
	}