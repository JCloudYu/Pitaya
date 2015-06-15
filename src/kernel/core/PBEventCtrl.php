<?php
/**
 * 1024.QueueCounter - PBEventCtrl.php
 * Created by JCloudYu on 2015/04/30 20:54
 */
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.PBStream' );
	using( 'ext.base.array' );
	using( 'ext.base.string' );

	final class PBEventCtrl extends PBObject
	{
		public static function Fire( $service, $eventInfo , $serializableArgs = array() )
		{
			$EVT_STORAGE = path( 'data.events' );
			if ( !is_dir( $EVT_STORAGE ) ) @mkdir( $EVT_STORAGE, 0755, TRUE );

			// INFO: Normalize event information
			if ( !is_array($eventInfo) ) $eventInfo = array( $eventInfo );



			$PITAYA_EXEC = __WEB_ROOT__ . "/pitaya.sh";
			$EVT_ARGS	 = serialize($serializableArgs);
			$EVENT_HASH	 = md5("{$service} {$EVT_ARGS}" . uniqid("", TRUE));
			$EVENT_ID	 = "E_" . date("Ymd") . "_" . substr($EVENT_HASH, rand(0, strlen($EVENT_HASH) - __EVENT_IDENTIFIER_LEN__), __EVENT_IDENTIFIER_LEN__);

			if ( !is_executable( $PITAYA_EXEC ) ) return FALSE;


			$OUT = array();
			$CLI_SERVICE_NAME	= @escapeshellarg($service);
			$CLI_EVENT_INFO		= implode(' ', ary_filter( $eventInfo, function($item) { return escapeshellarg($item); } ));
			$CLI_EVENT_ARGS		= @escapeshellarg(base64_encode( $EVT_ARGS ));
			exec( "{$PITAYA_EXEC} {$CLI_SERVICE_NAME} Event {$CLI_EVENT_INFO} {$CLI_EVENT_ARGS} 2>&1", $OUT, $STATUS );


			if ( !empty($OUT) ) file_put_contents("{$EVT_STORAGE}/{$EVENT_ID}.out", implode("\n", $OUT));

			$STREAM = PBStream::Rotatable( "{$EVT_STORAGE}/event.history" );
			$STREAM->write( LogStr( "ID:{$EVENT_ID},SERVICE:{$service},INFO:{$CLI_EVENT_INFO},STATUS:{$STATUS},ARGS:{$EVT_ARGS}" . EON ) );


			return $STATUS;
		}
	}
