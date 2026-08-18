<?php

// Copyright (C) 2014 DLH
// See LICENSE.txt for the MIT license.

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'TimeConvert' );
	return;
}

die( 'TimeConvert requires MediaWiki 1.43 or later.' );
