<?php

namespace MediaWiki\Extension\TimeConvert;

use DateTime;
use DateTimeZone;
use Exception;
use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;

class ParserFunction {
	private const DEFAULT_TIME = 'now';
	private const DEFAULT_ZONE = 'Etc/GMT';
	private const DEFAULT_FORMAT = DateTime::ISO8601;

	public static function render(
		Parser $parser,
		string $time = '',
		string $zoneName = '',
		string $format = ''
	): string {
		return self::convert(
			$time,
			$zoneName,
			$format,
			MediaWikiServices::getInstance()->getContentLanguage()
		);
	}

	public static function convert(
		string $time = '',
		string $zoneName = '',
		string $format = '',
		?Language $language = null
	): string {
		try {
			$errors = [];

			if ( trim( $time ) === '' ) {
				$time = self::DEFAULT_TIME;
				$errors[] = wfMessage( 'timeconvert-notime', $time )->text();
			}

			if ( trim( $zoneName ) === '' ) {
				$zoneName = self::DEFAULT_ZONE;
				$errors[] = wfMessage( 'timeconvert-nozone', $zoneName )->text();
			}

			if ( $format === '' ) {
				$format = self::DEFAULT_FORMAT;
			}

			$dateTime = new DateTime( $time );
			$dateTime->setTimezone( new DateTimeZone( $zoneName ) );
			$formattedTime = $dateTime->format( $format );

			if ( $errors ) {
				$language ??= MediaWikiServices::getInstance()->getContentLanguage();
				return '(' . $language->commaList( $errors ) . ') ' . $formattedTime;
			}

			return $formattedTime;
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}
}
