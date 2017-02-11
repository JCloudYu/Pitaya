<?php
/**
 * 1017.NeighborApp - PBICache.php
 * Created by JCloudYu on 2015/02/20 13:34
 */

	abstract class PBCachePrototype {
		abstract public function store($token, $data);
		abstract public function fetch($token);
		abstract public function destroy($token);
	}
