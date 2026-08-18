<?php

namespace MediaWiki\Extension\TimeConvert;

use DateTime;
use DateTimeZone;
use Exception;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;

class TimeTableParserFunction {
	private const DEFAULT_FORMAT = 'H:i';
	private const DEFAULT_MAX_ROWS = 20;
	private const DEFAULT_MAX_ZONES = 10;
	private const DEFAULT_MAX_FORMAT_LENGTH = 100;

	private const DEFAULT_ZONES = [
		[
			'name' => 'America/Los_Angeles',
			'title' => 'America/Los_Angeles',
		],
		[
			'name' => 'America/New_York',
			'title' => 'America/New_York',
		],
		[
			'name' => 'Etc/GMT',
			'title' => 'Greenwich Mean Time',
		],
		[
			'name' => 'Europe/Amsterdam',
			'title' => 'Europe/Amsterdam',
		],
	];

	private const LONDON_ZONE = [
		'name' => 'Europe/London',
		'title' => 'Europe/London',
	];

	public static function render(
		Parser $parser,
		string $anchorDateTime = '',
		string ...$args
	): array {
		[ $dateTimes, $options ] = self::parseArgs( $args );

		if ( trim( $anchorDateTime ) === '' ) {
			return [ '', 'nowiki' => true ];
		}

		array_unshift( $dateTimes, trim( $anchorDateTime ) );

		$limits = self::getLimits();

		if ( count( $dateTimes ) > $limits['rows'] ) {
			return [
				wfMessage( 'timeconvert-timetable-too-many-rows', $limits['rows'] )->text(),
				'nowiki' => true,
			];
		}

		$zones = self::getZones( $anchorDateTime, $options, $limits['zones'] );
		$format = $options['format'] ?? self::DEFAULT_FORMAT;

		if ( $zones === null || count( $zones ) > $limits['zones'] ) {
			return [
				wfMessage( 'timeconvert-timetable-too-many-zones', $limits['zones'] )->text(),
				'nowiki' => true,
			];
		}

		if ( strlen( $format ) > $limits['formatLength'] ) {
			return [
				wfMessage( 'timeconvert-timetable-format-too-long', $limits['formatLength'] )->text(),
				'nowiki' => true,
			];
		}

		$showDateMarkers = self::showDateMarkers( $options );
		$rows = [];

		$rows[] = Html::rawElement(
			'tr',
			[],
			Html::element( 'th', [ 'scope' => 'col' ] ) . self::renderHeaders( $anchorDateTime, $zones )
		);

		foreach ( $dateTimes as $index => $dateTime ) {
			$label = $options['label' . ( $index + 1 )] ?? self::getDefaultLabel( $index + 1 );
			$rows[] = Html::rawElement(
				'tr',
				[],
				self::renderRowHeader( $label, $dateTime ) .
					self::renderCells( $dateTime, $zones, $format, $showDateMarkers )
			);
		}

		$firstLabel = $options['label1'] ?? self::getDefaultLabel( 1 );
		$convertedFirstTime = ParserFunction::convert(
			$anchorDateTime,
			'Etc/GMT',
			'Y-m-d g:i A T'
		);
		$query = 'convert ' . $convertedFirstTime . ' to current geoIP location';
		$link = Html::rawElement(
			'p',
			[],
			Html::rawElement(
				'a',
				[
					'rel' => 'nofollow',
					'class' => 'external text',
					'href' => 'https://www.wolframalpha.com/input/?i=' . rawurlencode( $query ),
				],
				'Convert ' . Html::element( 'i', [], $firstLabel ) . ' to my time zone'
			)
		);

		return [
			Html::rawElement(
				'table',
				[
					'border' => '1',
					'cellpadding' => '10',
					'style' => 'text-align: center; border-collapse: collapse;',
				],
				implode( '', $rows )
			) . $link,
			'isHTML' => true,
		];
	}

	private static function getLimits(): array {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		return [
			'rows' => max(
				1,
				(int)$config->get( 'TimeConvertTimetableMaxRows' ) ?: self::DEFAULT_MAX_ROWS
			),
			'zones' => max(
				1,
				(int)$config->get( 'TimeConvertTimetableMaxZones' ) ?: self::DEFAULT_MAX_ZONES
			),
			'formatLength' => max(
				1,
				(int)$config->get( 'TimeConvertTimetableMaxFormatLength' ) ?: self::DEFAULT_MAX_FORMAT_LENGTH
			),
		];
	}

	private static function parseArgs( array $args ): array {
		$dateTimes = [];
		$options = [];

		foreach ( $args as $arg ) {
			$arg = trim( $arg );

			if ( $arg === '' ) {
				continue;
			}

			if ( strpos( $arg, '=' ) !== false ) {
				[ $name, $value ] = explode( '=', $arg, 2 );
				$name = trim( $name );

				if (
					$name === 'format' ||
					$name === 'showdate' ||
					$name === 'showdates' ||
					$name === 'zones' ||
					preg_match( '/^label[1-9][0-9]*$/', $name ) ||
					preg_match( '/^zone[1-9][0-9]*$/', $name )
				) {
					$options[$name] = trim( $value );
					continue;
				}
			}

			$dateTimes[] = $arg;
		}

		return [ $dateTimes, $options ];
	}

	private static function getZones( string $anchorDateTime, array $options, int $maxZones ): ?array {
		if ( self::hasCustomZoneOptions( $options ) ) {
			return self::getCustomZones( $options, $maxZones );
		}

		$zones = self::DEFAULT_ZONES;

		if ( self::shouldShowLondonColumn( $anchorDateTime ) ) {
			array_splice( $zones, 3, 0, [ self::LONDON_ZONE ] );
		}

		return $zones;
	}

	private static function hasCustomZoneOptions( array $options ): bool {
		if ( isset( $options['zones'] ) ) {
			return true;
		}

		foreach ( $options as $name => $value ) {
			if ( preg_match( '/^zone[1-9][0-9]*$/', $name ) ) {
				return true;
			}
		}

		return false;
	}

	private static function getCustomZones( array $options, int $maxZones ): ?array {
		$zoneNames = [];
		$zoneCount = 0;

		$addZoneName = static function ( string $zoneName, ?int $index = null ) use ( &$zoneNames, &$zoneCount, $maxZones ): bool {
			$zoneName = trim( $zoneName );

			if ( $zoneName === '' ) {
				return true;
			}

			if ( $zoneCount >= $maxZones ) {
				return false;
			}

			$zoneCount++;
			if ( $index === null ) {
				$zoneNames[] = $zoneName;
			} else {
				$zoneNames[$index] = $zoneName;
			}

			return true;
		};

		if ( isset( $options['zones'] ) ) {
			$splitZones = preg_split(
				'/\s*,\s*/',
				$options['zones'],
				$maxZones + 1,
				PREG_SPLIT_NO_EMPTY
			) ?: [];

			foreach ( $splitZones as $zoneName ) {
				if ( !$addZoneName( $zoneName ) ) {
					return null;
				}
			}
		}

		foreach ( $options as $name => $value ) {
			if ( preg_match( '/^zone[1-9][0-9]*$/', $name ) ) {
				if ( !$addZoneName( $value, (int)substr( $name, 4 ) - 1 ) ) {
					return null;
				}
			}
		}

		ksort( $zoneNames );

		$zones = [];
		$seen = [];
		foreach ( $zoneNames as $zoneName ) {
			try {
				$timeZone = new DateTimeZone( $zoneName );
			} catch ( Exception ) {
				continue;
			}

			$zoneName = $timeZone->getName();
			if ( isset( $seen[$zoneName] ) ) {
				continue;
			}

			$seen[$zoneName] = true;
			$zones[] = [
				'name' => $zoneName,
				'title' => self::getZoneTitle( $zoneName ),
			];
		}

		return $zones;
	}

	private static function shouldShowLondonColumn( string $anchorDateTime ): bool {
		return ParserFunction::convert(
			$anchorDateTime,
			'Etc/GMT',
			'T'
		) !== ParserFunction::convert(
			$anchorDateTime,
			'Europe/London',
			'T'
		);
	}

	private static function renderHeaders(
		string $anchorDateTime,
		array $zones
	): string {
		$html = '';

		foreach ( $zones as $zone ) {
			$title = $zone['title'];

			if ( $zone['name'] !== 'Etc/GMT' ) {
				$title .= ' (GMT' . ParserFunction::convert(
					$anchorDateTime,
					$zone['name'],
					'P'
				) . ')';
			}

			$html .= Html::rawElement(
				'th',
				[ 'scope' => 'col' ],
				Html::element( 'abbr', [ 'title' => $title ], ParserFunction::convert(
					$anchorDateTime,
					$zone['name'],
					'T'
				) )
			);
		}

		return $html;
	}

	private static function renderCells(
		string $dateTime,
		array $zones,
		?string $format,
		bool $showDateMarkers
	): string {
		$html = '';
		[ $sourceDate ] = self::formatSourceDateTime( $dateTime );

		foreach ( $zones as $zone ) {
			$cellText = ParserFunction::convert(
				$dateTime,
				$zone['name'],
				$format
			);

			$fullDateTime = ParserFunction::convert( $dateTime, $zone['name'], 'Y-m-d H:i T' );
			$cellText = Html::element( 'abbr', [ 'title' => $fullDateTime ], $cellText );

			if ( $showDateMarkers ) {
				$convertedDate = ParserFunction::convert( $dateTime, $zone['name'], 'Y-m-d' );
				if (
					self::isDateString( $sourceDate ) &&
					self::isDateString( $convertedDate ) &&
					$convertedDate !== $sourceDate
				) {
					$cellText .= Html::element( 'br' ) .
						Html::element( 'small', [], $convertedDate );
				}
			}

			$html .= Html::rawElement( 'td', [], $cellText );
		}

		return $html;
	}

	private static function renderRowHeader( string $label, string $dateTime ): string {
		[ $sourceDate, $sourceDateTime ] = self::formatSourceDateTime( $dateTime );

		return Html::rawElement(
			'th',
			[ 'scope' => 'row' ],
			Html::element( 'span', [], $label ) .
				Html::element( 'br' ) .
				Html::rawElement(
					'small',
					[],
					Html::element( 'abbr', [ 'title' => $sourceDateTime ], $sourceDate )
				)
		);
	}

	private static function formatSourceDateTime( string $dateTime ): array {
		try {
			$sourceDateTime = new DateTime( $dateTime );
		} catch ( Exception ) {
			return [ $dateTime, $dateTime ];
		}

		return [
			$sourceDateTime->format( 'Y-m-d' ),
			$sourceDateTime->format( 'Y-m-d H:i T' ),
		];
	}

	private static function isDateString( string $value ): bool {
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) === 1;
	}

	private static function getZoneTitle( string $zoneName ): string {
		return $zoneName === 'Etc/GMT' ? 'Greenwich Mean Time' : $zoneName;
	}

	private static function getDefaultLabel( int $number ): string {
		$labels = [
			1 => 'First Round',
			2 => 'Second Round',
			3 => 'Third Round',
			4 => 'Fourth Round',
			5 => 'Fifth Round',
			6 => 'Sixth Round',
			7 => 'Seventh Round',
			8 => 'Eighth Round',
			9 => 'Ninth Round',
		];

		return $labels[$number] ?? self::getOrdinal( $number ) . ' Round';
	}

	private static function getOrdinal( int $number ): string {
		$mod100 = $number % 100;

		if ( $mod100 >= 11 && $mod100 <= 13 ) {
			return $number . 'th';
		}

		return match ( $number % 10 ) {
			1 => $number . 'st',
			2 => $number . 'nd',
			3 => $number . 'rd',
			default => $number . 'th',
		};
	}

	private static function showDateMarkers( array $options ): bool {
		$value = $options['showdate'] ?? $options['showdates'] ?? '';

		return !in_array( strtolower( $value ), [ '0', 'false', 'no', 'off' ], true );
	}
}
