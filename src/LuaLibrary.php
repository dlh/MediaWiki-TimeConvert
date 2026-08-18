<?php

namespace MediaWiki\Extension\TimeConvert;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;

class LuaLibrary extends LibraryBase {
	public function register() {
		$this->getEngine()->registerInterface(
			dirname( __DIR__ ) . '/TimeConvert.lua',
			[
				'timeconvert' => [ $this, 'timeconvert' ],
			]
		);
	}

	public function timeconvert( $time = '', $zoneName = '', $format = '' ): array {
		return [
			ParserFunction::convert(
				(string)$time,
				(string)$zoneName,
				(string)$format
			),
		];
	}
}
