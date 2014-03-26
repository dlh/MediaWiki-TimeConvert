<?php

// Copyright (C) 2014 DLH
// See LICENSE.txt for the MIT license.

$wgExtensionCredits['parserhook'][] = array(
   "path" => __FILE__,
   "name" => "TimeConvert",
   "description" => "Adds a parser function to convert a time to a different time zone",
   "author" => "dlh",
   "url" => "http://github.com/dlh/MediaWiki-TimeConvert"
);

class TimeConvert
{
    public static function ParserFirstCallInit(&$parser)
    {
        $parser->setFunctionHook("timeconvert", "TimeConvert::TimeConvertFunctionHook");
        return true;
    }

    public static function TimeConvertFunctionHook($parser, $time="", $zoneName="", $format="")
    {
        try
        {
            $errors = array();
            if (empty($time))
            {
                $time = "now";
                $errors[] = "No time specified, using '$time'";
            }
            if (empty($zoneName))
            {
                $zoneName = "Etc/GMT";
                $errors[] = "No zone name specified, using '$zoneName'";
            }
            if (empty($format))
            {
                // Use ISO 8601 as the default
                $format = 'Y-m-d\TH:i:sP';
            }

            $dt = new DateTime($time);
            $dt->setTimezone(new DateTimeZone($zoneName));

            if (!empty($errors))
            {
                $errorMessage = "(" . join(". ", $errors) . ") ";
            }

            return $errorMessage . $dt->format($format);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
}

$wgHooks["ParserFirstCallInit"][] = "TimeConvert::ParserFirstCallInit";
$wgExtensionMessagesFiles["TimeConvert"] = __DIR__ . '/TimeConvert.i18n.php';

?>
