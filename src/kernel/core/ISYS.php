<?php
/*
 * File: ISYS.php
 * Created by Cloud.
 * DateTime: 13/2/11 PM4:06
 */

// ISSUE: We have to define the APIs exposed to the Process and the module
interface ISYS
{
	public function acquireModule($moduleName);
}