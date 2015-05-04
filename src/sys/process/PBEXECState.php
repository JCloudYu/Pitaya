<?php
/**
 * 1028.CSMS-BDF - PBEXECState.php
 * Created by JCloudYu on 2015/05/04 23:14
 */
	class PBEXECState
	{
		const WARINING					=   1;
		const NORMAL					=   0;
		const ERROR						=  -1;

		const UNKNOWN_ERROR				=  -1;
		const INVALID_EXEC_MODE			=  -2;
		const DATABASE_CONNECTION		=  -3;
		const INTERNET_CONNECTION		=  -4;
		const WRITE_FAILURE				=  -5;
		const READ_FAILURE				=  -6;
		const DELETE_FAILURE			=  -7;
		const COPY_FAILURE				=  -8;
		const MOVE_FAILURE				=  -9;
		const UNEXPEXTED_LOGIC			= -10;

		const INSUFFICIENT_PRIVILEGE	= -11;
		const INSUFFICIENT_ARGUMENTS	= -12;
		const INSUFFICIENT_DATA			= -13;
		const INVALID_METHOD			= -14;
		const INVALID_ARGUMENTS			= -15;
		const INVALID_REQUEST			= -16;
		const INVALID_DATA				= -17;
		const INVALID_RESOURCE			= -18;

		const RESOURCE_NOT_FOUND		= -19;
		const UNSUPPORTED_METHOD		= -20;
		const UNKNOWN_EVENT				= -21;
	}
