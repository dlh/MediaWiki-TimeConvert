<?php

namespace MediaWiki\Extension\TimeConvert;

use MediaWiki\Extension\Scribunto\Hooks\ScribuntoExternalLibrariesHook;

class ScribuntoHooks implements ScribuntoExternalLibrariesHook {
	public function onScribuntoExternalLibraries( string $engine, array &$extraLibraries ): void {
		if ( $engine === 'lua' ) {
			$extraLibraries['mw.ext.timeconvert'] = LuaLibrary::class;
		}
	}
}
