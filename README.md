TimeConvert
===========

A MediaWiki extension that provides a parser function and Scribunto Lua library
to convert a time to a different time zone.

* Project site: https://github.com/dlh/MediaWiki-TimeConvert
* MediaWiki page: https://www.mediawiki.org/wiki/Extension:TimeConvert

Examples
--------

    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York}}       => 2014-01-01T08:00:00-0500
    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York|g:i A}} => 8:00 AM
    {{#timeconvert:2014-01-01 8:00 AM EST|Etc/GMT|G:i}}          => 13:00

Extension Documentation
-----------------------

    {{#timeconvert:date time|time zone|format}}

* `date time`: A [date time
  string](https://www.php.net/manual/en/datetime.formats.php).
* `time zone`: The [time zone](https://www.php.net/manual/en/timezones.php) to
  convert `date time` to.
* `format`: The [output format](https://www.php.net/manual/en/function.date.php)
  to use. The default is [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601).

Scribunto Lua Library
---------------------

TimeConvert provides a [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto) library,
`mw.ext.timeconvert`. Examples:

    local timeconvert = mw.ext.timeconvert.timeconvert
    timeconvert("2014-01-01 13:00 GMT", "America/New_York")          => "2014-01-01T08:00:00-0500"
    timeconvert("2014-01-01 13:00 GMT", "America/New_York", "g:i A") => "8:00 AM"
    timeconvert("2014-01-01 8:00 AM EST", "Etc/GMT", "G:i")          => "13:00"

Download
--------

Using git:

    git clone https://github.com/dlh/MediaWiki-TimeConvert.git TimeConvert

A zip file snapshot of the repository is also available on the project site.

Installation
------------

TimeConvert requires MediaWiki 1.43 or later. The parser function works on its
own; the Lua library is available when Scribunto is installed.

1. Move the `TimeConvert` directory to your site's `extensions` directory.
2. Edit `LocalSettings.php` and add the following line near the bottom:

        wfLoadExtension( 'TimeConvert' );

Docker Test Environment
-----------------------

This repository includes a minimal Docker Compose setup for smoke testing the
extension against MediaWiki 1.43 with MariaDB:

    docker compose up -d

Open <http://localhost:8080> and test:

    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York}}

To stop the test wiki:

    docker compose down

To recreate the database from scratch:

    docker compose down -v
    docker compose up -d

The test wiki creates an `Admin` user with password
`TimeConvertAdminPass123`.

License
-------

MIT license. See LICENSE.txt.
