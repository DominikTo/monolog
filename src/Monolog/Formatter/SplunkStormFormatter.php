<?php

namespace Monolog\Formatter;

/**
 * Encodes message information into JSON in a format compatible with SplunkStorm.
 *
 * @author Dominik Tobschall <dominik@fruux.com>
 */
class SplunkStormFormatter extends JsonFormatter
{
    /**
     * Adds the 'timestamp' parameter for usage with the
     * 'json_predefined_timestamp' sourcetype of SplunkStorm
     *
     * Example Timestamp: 2013-10-24T23:10:12.65
     *
     * @see http://docs.splunk.com/Documentation/Storm/Storm/User/Sourcesandsourcetypes
     * @see \Monolog\Formatter\JsonFormatter::format()
     */
    public function format(array $record)
    {
        if (isset($record["datetime"]) && ($record["datetime"] instanceof \DateTime)) {
            $record["timestamp"] = substr($record["datetime"]->format("Y-m-d\TH:i:s.u"), 0, 23);
            unset($record['datetime']);
        }

        return parent::format($record);
    }
}
