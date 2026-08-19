<?php

namespace MediaWiki\Extension\TimeConvert;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Parser\Parser;

class Hooks implements ParserFirstCallInitHook {
	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ): void {
		$parser->setFunctionHook( 'timeconvert', [ ParserFunction::class, 'render' ] );
		$parser->setFunctionHook( 'timetable', [ TimeTableParserFunction::class, 'render' ] );
	}
}
