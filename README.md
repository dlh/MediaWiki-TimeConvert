TimeConvert
===========

A MediaWiki extension that provides parser functions for converting times and
building time-zone tables, plus a Scribunto Lua library for time conversion.

* Project site: https://github.com/dlh/MediaWiki-TimeConvert
* MediaWiki page: https://www.mediawiki.org/wiki/Extension:TimeConvert

timeconvert
-----------

    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York}}       => 2014-01-01T08:00:00-0500
    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York|g:i A}} => 8:00 AM
    {{#timeconvert:2014-01-01 8:00 AM EST|Etc/GMT|G:i}}          => 13:00

    {{#timeconvert:date time|time zone|format}}

* `date time`: A [date time
  string](https://www.php.net/manual/en/datetime.formats.php).
* `time zone`: The [time zone](https://www.php.net/manual/en/timezones.php) to
  convert `date time` to.
* `format`: The [output format](https://www.php.net/manual/en/function.date.php)
  to use. The default is [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601).

TimeConvert also provides a [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto)
library, `mw.ext.timeconvert`, when Scribunto is installed:

    local timeconvert = mw.ext.timeconvert.timeconvert
    timeconvert("2014-01-01 13:00 GMT", "America/New_York")          => "2014-01-01T08:00:00-0500"
    timeconvert("2014-01-01 13:00 GMT", "America/New_York", "g:i A") => "8:00 AM"
    timeconvert("2014-01-01 8:00 AM EST", "Etc/GMT", "G:i")          => "13:00"

timetable
---------

    {{#timetable:2014-01-01 13:00 GMT|2014-01-01 14:00 GMT}}

    {{#timetable:datetime 1|datetime 2|...|zones=output zone 1,output zone 2|format=output format|showdate=0|label1=label|label2=label|...}}

* `datetime 1`, `datetime 2`, `...`: Date/time strings for the rows in the
  table, up to 20 rows total by default. Include the source time zone in each value, such as
  `2014-01-01 13:00 GMT` or `2014-01-01 8:00 AM EST`.
* `zones`: Optional. A comma-separated list of output time zones. If omitted,
  the default output zones are used. Up to 10 output time zones may be used by default.
* `zone1`, `zone2`, `...`: Optional alternative to `zones` for specifying
  output time zones as numbered parameters.
* `format`: Optional. A [PHP date format](https://www.php.net/manual/en/function.date.php)
  used for every table cell. The default is `H:i`. Each cell includes the full
  converted datetime in its tooltip. The format may be up to 100 characters by default.
* `showdate`: Optional. Set to `0`, `false`, `no`, or `off` to hide converted
  date markers under cells that cross into a different date. `showdates` is
  also accepted.
* `label1`, `label2`, `...`: Optional row labels. Unspecified labels default
  to `First Round`, `Second Round`, and so on.

By default, the table shows America/Los_Angeles, America/New_York, GMT, and
Europe/Amsterdam. Europe/London is included by default when British Summer Time
applies for the given date. When `zones` or `zone1`, `zone2`, `...` are set,
the table uses exactly those output zones instead. Converted cells include the
date so next-day and previous-day conversions are visible.

By default, cells show compact 24-hour times while keeping the source date
visible in each row header and the full converted datetimes available in the
cell tooltips. Cells that convert to a different date than the row's source
date show that converted date under the formatted time unless `showdate=0` is
set.

Server administrators can adjust the timetable limits in `LocalSettings.php`:

    $wgTimeConvertTimetableMaxRows = 20;
    $wgTimeConvertTimetableMaxZones = 10;
    $wgTimeConvertTimetableMaxFormatLength = 100;

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

License
-------

MIT license. See LICENSE.txt.
