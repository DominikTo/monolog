<?php

namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Formatter\SplunkStormFormatter;

/**
 * Sends errors to Splunk Storm.
 *
 * @author Dominik Tobschall <dominik@fruux.com>
 */
class SplunkStormHandler extends AbstractProcessingHandler
{

    const API_VERSION = 1;

    const API_ENDPOINT = 'inputs/http';

    protected $apiHost;

    protected $accessToken;

    protected $projectId;

    /**
     * @param string  $apiHost     Project specific SplunkStorm API Hostname
     * @param string  $accessToken Access Token
     * @param string  $projectId   Project ID
     * @param integer $level       The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble      Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($apiHost, $accessToken, $projectId, $level = Logger::DEBUG, $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new \LogicException('The curl extension is needed to use the SplunkStormHandler');
        }

        $this->apiHost = $apiHost;
        $this->accessToken = $accessToken;
        $this->projectId = $projectId;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $this->send($record["formatted"]);
    }

    protected function send($data, $endpoint)
    {
        $params = array(
            'project' => $this->projectId,
            'sourcetype' => 'json_predefined_timestamp',
        );

        $url = sprintf("https://%s/%s/%s", $this->apiHost, self::API_VERSION, self::API_ENDPOINT);
        $url = $url . '?' . http_build_query($params);

        $headers = array('Content-Type: application/json');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'x:' . $this->accessToken);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        curl_close($ch);
    }

    protected function getDefaultFormatter()
    {
        return new SplunkStormFormatter();
    }
}
