<?php
	abstract class PBCachePrototype {
		abstract public function store($token, $data);
		abstract public function fetch($token);
		abstract public function destroy($token);
	}
