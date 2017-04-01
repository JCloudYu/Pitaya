<?php
	interface PBIBootResolver {
		public function resolve( $basis, $request, $attribute );
	}