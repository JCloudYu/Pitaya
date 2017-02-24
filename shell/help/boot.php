<?php
	fwrite( STDERR, <<<TEXT
Usage: pitaya <command> [args...]
       pitaya <path_to_the_vector> [args...]
       pitaya [args...] (The working directory must be an available pitaya space)
       pitaya -h (Brief usage instructions)
       pitaya --help (Detailed usage instructions)

TEXT
);

	if ( empty($detailedInfo) ) return;
	fwrite( STDERR, <<<TEXT

Pitaya accepts following commands.
    -c    Create pitaya space (please visit pitaya -c -h for more details)

TEXT
);
