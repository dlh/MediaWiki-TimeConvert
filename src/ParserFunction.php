<?php

namespace MediaWiki\Extension\TimeConvert;

use DateTime;
use DateTimeZone;
use Exception;
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
	): array {
		return [
			self::convert( $time, $zoneName, $format ),
			'nowiki' => true,
		];
	}

	public static function convert(
		string $time = '',
		string $zoneName = '',
		string $format = ''
	): string {
		$language = MediaWikiServices::getInstance()->getContentLanguage();
		$errors = [];

		if ( trim( $time ) === '' ) {
			$time = self::DEFAULT_TIME;
			$errors[] = wfMessage( 'timeconvert-notime', $time )->inLanguage( $language )->text();
		}

		if ( trim( $zoneName ) === '' ) {
			$zoneName = self::DEFAULT_ZONE;
			$errors[] = wfMessage( 'timeconvert-nozone', $zoneName )->inLanguage( $language )->text();
		}

		if ( $format === '' ) {
			$format = self::DEFAULT_FORMAT;
		}

		try {
			$dateTime = new DateTime( $time );
		} catch ( Exception ) {
			return wfMessage( 'timeconvert-invalidtime' )->inLanguage( $language )->text();
		}

		try {
			$dateTime->setTimezone( new DateTimeZone( $zoneName ) );
		} catch ( Exception ) {
			return wfMessage( 'timeconvert-invalidzone' )->inLanguage( $language )->text();
		}

		$formattedTime = $dateTime->format( $format );

		if ( $errors ) {
			return '(' . $language->commaList( $errors ) . ') ' . $formattedTime;
		}

		return $formattedTime;
	}
}
