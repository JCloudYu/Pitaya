<?php
	/**
 * Pitaya - db.php
 * Created by JCloudYu on 2013/09/25 22:37
 */

	// DEPRECATED: The package along with APIs will be removed in v1.4.0

	using('sys.db.ExtPDO');
	using('sys.db.PBDBCtrl');


	function DB($param = NULL, $option = array('CREATE_VAR')) { return PBDBCtrl::DB($param, $option); }
	function CONNECT($param = NULL, $option = array('CREATE_VAR')) { return PBDBCtrl::CONNECT($param, $option); }
	function LIMIT($SQL, $page = NULL, $pageSize = NULL, &$pageInfo = NULL) {
		$limitClause = PBDBCtrl::LIMIT($SQL, $page, $pageSize, $pageInfo);
		return empty($limitClause) ? "" : "LIMIT {$limitClause}";
	}
	function SET($data, &$param = NULL, $varIndex = FALSE) { return PBDBCtrl::SET($data, $param, $varIndex); }
	function ORDER($orderOpt = array()) { return PBDBCtrl::ORDER($orderOpt); }
