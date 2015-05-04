<?php
/**
 * 1028.CSMS-BDF - PBShellEXECState.php
 * Created by JCloudYu on 2015/05/04 23:15
 */
	final class PBShellEXECState
	{
		const SUCCESS	= Termination::STATUS_SUCCESS;
		const ERROR		= Termination::STATUS_ERROR;
		const USAGE		= Termination::STATUS_INCORRECT_USAGE;

		const COMMAND_NOT_FOUND			= 127;
		const INVALID_ARGUMENT_TO_EXIT	= 128;
		const SIGNAL					= 129;
		const SIGHUP					= 129; // SIG 1
		const SIGINT					= 130; // SIG 2
		const EXIT_OUT_OF_RANGE			= 255;
	}
