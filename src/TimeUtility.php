<?php

/**
 * Time utility class for handling time format conversions and normalizations
 */
class TimeUtility
{
    /**
     * Convert local datetime to UTC and get normalized ISO format for hashing
     */
    public static function convertLocalToUTCForHashing(string $localDateTime, string $timezone): string
    {
        $localTime = new DateTime($localDateTime, new DateTimeZone($timezone));
        $localTime->setTimezone(new DateTimeZone('UTC'));
        return $localTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Convert local datetime to human-readable format for URL
     */
    public static function convertLocalToReadableFormat(string $localDateTime): string
    {
        // Input format: 2025-08-17T19:24
        // Output format: 2025-08-17_19-24
        $dateTime = new DateTime($localDateTime);
        return $dateTime->format('Y-m-d_H-i');
    }

    /**
     * Convert human-readable format back to local datetime
     */
    public static function convertReadableToLocalFormat(string $readableTime): string
    {
        // Input format: 2025-08-17_19-24
        // Output format: 2025-08-17T19:24:00
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})$/', $readableTime, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}T{$matches[4]}:{$matches[5]}:00";
        }
        throw new Exception('Invalid readable time format: ' . $readableTime);
    }


    /**
     * Parse readable time format and convert to UTC for verification
     */
    public static function parseReadableTimeToUTC(string $readableTime, string $timezone): string
    {
        $localDateTime = self::convertReadableToLocalFormat($readableTime);
        return self::convertLocalToUTCForHashing($localDateTime, $timezone);
    }
}