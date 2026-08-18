<?php

namespace MediaWiki\Extension\TimeConvert;

use MediaWiki\Parser\Parser;

class Hooks {
	public function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook( 'timeconvert', [ ParserFunction::class, 'render' ] );
	}

	public function onScribuntoExternalLibraries( string $engine, array &$extraLibraries ): void {
		if ( $engine === 'lua' ) {
			$extraLibraries['mw.ext.timeconvert'] = LuaLibrary::class;
		}
	}
}
