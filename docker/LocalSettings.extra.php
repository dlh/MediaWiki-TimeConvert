$wgSecretKey = 'timeconvert-docker-development-only-secret-key';
$wgAuthenticationTokenVersion = '1';
$wgUpgradeKey = 'timeconvert-docker-upgrade-key';

$wgMainCacheType = CACHE_NONE;
$wgCacheDirectory = false;

wfLoadExtension( 'Scribunto' );
wfLoadExtension( 'TimeConvert' );
//wfLoadExtension( 'ParserFunctions' );

$wgShowExceptionDetails = true;
