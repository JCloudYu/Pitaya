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
		public static function Fire( $service, $event, $eventArgs = array() )
		{
			$EVT_STORAGE = path( 'data.events' );
			if ( !is_dir( $EVT_STORAGE ) ) @mkdir( $EVT_STORAGE, 0644, TRUE );
			if ( !is_array( $eventArgs ) ) $eventArgs = array( $eventArgs );
			array_unshift( $eventArgs, $event );



			$PITAYA_EXEC = __WEB_ROOT__ . "/pitaya.sh";
			$EVT_ARGS	 = implode(' ', ary_filter( $eventArgs, function( $item ){ return "{$item}"; } ));
			$EVENT_HASH	 = md5("{$service} {$EVT_ARGS}" . uniqid("", TRUE));
			$EVENT_ID	 = "E_" . date("Ymd") . "_" . substr($EVENT_HASH, rand(0, strlen($EVENT_HASH) - __EVENT_IDENTIFIER_LEN__), __EVENT_IDENTIFIER_LEN__);

			if ( !is_executable( $PITAYA_EXEC ) ) return FALSE;


			$OUT = array();
			exec( "{$PITAYA_EXEC} {$service} Event {$EVT_ARGS}", $OUT, $STATUS );


			if ( !empty($OUT) ) file_put_contents("{$EVT_STORAGE}/{$EVENT_ID}.out", implode("\n", $OUT));

			$STREAM = PBStream::Rotatable( "{$EVT_STORAGE}/event.history" );
			$STREAM->write( LogStr( "ID:{$EVENT_ID},SERVICE:{$service},STATUS:{$STATUS},ARGS:{$EVT_ARGS}" . EOL ) );


			return $STATUS;
		}
	}
