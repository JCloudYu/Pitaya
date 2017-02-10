<?php
	using( 'sys.process.PBEXECState' );

	class PBErrorPage extends PBModule
	{
		public function __construct() {
			DEPRECATION_WARNING( "PBErrorPage is marked as deprecated and will be removed in the following versions!" );
		}
		
		private $_customStyle = FALSE;

		public function __set_customStyle( $value ){ $this->_customStyle = !!$value; }
		public function __get_hasCustomStyle(){ return $this->_customStyle; }

		public function __get_errIconURI(){ return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSINCgkgaWQ9InN2ZzMwMjYiIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIiB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiIHhtbG5zOmlua3NjYXBlPSJodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy9uYW1lc3BhY2VzL2lua3NjYXBlIiBzb2RpcG9kaTpkb2NuYW1lPSJNb3JlLnN2ZyIgaW5rc2NhcGU6dmVyc2lvbj0iMC40OC40IHI5OTM5Ig0KCSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjIwcHgiIGhlaWdodD0iMjBweCINCgkgdmlld0JveD0iMCAwIDIwIDIwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAyMCAyMCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8c29kaXBvZGk6bmFtZWR2aWV3ICBib3JkZXJvcGFjaXR5PSIxIiBpbmtzY2FwZTp6b29tPSIzNy4yIiBndWlkZXRvbGVyYW5jZT0iMTAiIGlua3NjYXBlOmN5PSIxMCIgaW5rc2NhcGU6Y3g9IjEwIiBncmlkdG9sZXJhbmNlPSIxMCIgb2JqZWN0dG9sZXJhbmNlPSIxMCIgYm9yZGVyY29sb3I9IiM2NjY2NjYiIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIgc2hvd2dyaWQ9ImZhbHNlIiBpZD0ibmFtZWR2aWV3MzAzNCIgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iZzMwMjgiIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiIGlua3NjYXBlOndpbmRvdy15PSItOCIgaW5rc2NhcGU6d2luZG93LXg9Ii04IiBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSI5NjEiIGlua3NjYXBlOndpbmRvdy13aWR0aD0iMTI4MCIgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAiPg0KCTxpbmtzY2FwZTpncmlkICB0eXBlPSJ4eWdyaWQiIGlkPSJncmlkMzA0MCI+PC9pbmtzY2FwZTpncmlkPg0KPC9zb2RpcG9kaTpuYW1lZHZpZXc+DQo8ZyBpZD0iZzMwMjgiPg0KCTxjaXJjbGUgaWQ9ImNpcmNsZTMwMzAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI0Q0MDAwMCIgc3Ryb2tlLXdpZHRoPSIyLjIzIiBjeD0iMTAiIGN5PSIxMCIgcj0iOC41NjUiLz4NCgk8cGF0aCBpZD0icGF0aDM4MTgiIHNvZGlwb2RpOm5vZGV0eXBlcz0iY2NjY2MiIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiIGZpbGw9IiNENDAwMDAiIGQ9Ik0zLjM1MSwxNS4xMTJsMS41OTEsMS41OTENCgkJTDE2Ljc1Nyw0Ljg4OGwtMS41OTEtMS41OTFMMy4zNTEsMTUuMTEyeiIvPg0KPC9nPg0KPC9zdmc+DQo="; }

		public function execute( ...$arguments )
		{
			$param = @$arguments[0];
			$param = ( !is_array( $param ) ) ? array( 'status' => PBEXECState::ERROR, 'msg' => @"{$param}" ) : $param;

			$status = @$param['status'];
			$msg	= @$param['msg'];
			$desc	= @$param['data'];

			if ( !$this->hasCustomStyle )
			{
				$style = <<<HTML
					<style>
						body { font-size:16px; font-family: "Courier New", "文泉驛正黑", "WenQuanYi Zen Hei", "儷黑 Pro", "LiHei Pro", "微軟正黑體", "Microsoft JhengHei", "標楷體", DFKai-SB, sans-serif; }
						.error-view			{ position:absolute; top:0; left:0; width:100vw; height:100vh; text-align:center; }
						.error-view:before	{ content:' '; display: inline-block; vertical-align:middle; height:100%; margin-right:-0.25em; }
						.error-view > *		{ display:inline-block; vertical-align:middle; }

						.error { width:640px; text-align:center; }
						.error .logo img { width:300px; height:300px; }
						.error .status	{ color:#D40000; font-size:2em; font-weight:bolder; }
							.error .status label { font-size:1.5em; }
							.error .status div { margin-top:10px; }
						.error .message { font-size:2em; font-weight:bolder; }
						.error .message:before	{ content:'<< '; }
						.error .message:after	{ content:' >>'; }
						.error .desc { font-size:2em; line-height:normal; margin-top:20px; text-align:center; }
					</style>
HTML;
			}


			return <<<HTML
				{$style}
				<main class="error-view"><div class="error">
					<div class="logo"><img src="{$this->errIconURI}"></div>
					<div class="status"><label>ERROR({$status})</label></div>
					<div class="message">{$msg}</div>
					<div class="desc">{$desc}</div>
				</div></main>
HTML;
		}
	}
