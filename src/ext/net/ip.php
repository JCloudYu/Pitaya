<?php
/**
 * IMSIS - ip.php
 * Created by JCloudYu on 2013/10/11 13:32
 */

	function NormalizeIPv6($ipAddr)
	{
		// Only strings are allowed here...
		if (!is_string($ipAddr)) return NULL;

		// INFO: Decide whether the given ip address is in shortened format
		$ipComponents = explode('::', $ipAddr);
		$shortened = count($ipComponents);
		// :: only allow once
		if ($shortened > 2) return NULL;
		$shortened = ($shortened > 1) ? TRUE : FALSE;



		// INFO: Validate and fill the components
		$msb = $lsb = $matches = array();

		// Validate MSB part
		if (!preg_match('/^([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4})*)$|^$/', $ipComponents[0], $matches) || $matches[0] !== $ipComponents[0])
			return NULL;

		// Extract and analysis MSB part
		$buff = explode(':', $ipComponents[0]);
		foreach ($buff as $component) $msb[] = hexdec($component);

		if ($shortened)
		{
			// Validate LSB part
			if (!preg_match('/^(([0-9a-fA-F]{1,4}:)*[0-9a-fA-F]{1,4})$|^$/', $ipComponents[1], $matches) || $matches[0] !== $ipComponents[1])
				return NULL;

			// Extract and analysis LSB part
			$buff = explode(':', $ipComponents[1]);
			foreach ($buff as $component) $lsb[] = hexdec($component);
		}

		// INFO: Check length
		$totalCount = count($msb) + count($lsb);
		if ($totalCount >= 8) return NULL;


		// INFO: Merge the components along with the missing 0s
		return array_merge($msb, array_fill(0, 8 - $totalCount, 0), $lsb);
	}