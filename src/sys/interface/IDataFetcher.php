<?php
/**
 * 1003.IMSIS - IDataFetcher.php
 * Created by JCloudYu on 2014/07/08 13:00
 */ 
	interface IDataFetcher
	{
		const FETCH_ASSOC	= PDO::FETCH_ASSOC;
		const FETCH_NUM		= PDO::FETCH_NUM;
		const FETCH_OBJ		= PDO::FETCH_OBJ;
		const FETCH_BOTH	= PDO::FETCH_BOTH;

		public function fetch($fetchOption = IDataFetcher::FETCH_BOTH, $encoding = '');
		public function fetchAll($fetchOption = IDataFetcher::FETCH_BOTH, $encoding = '');
	}
