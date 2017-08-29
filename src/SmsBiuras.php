<?php

namespace Adumskis\SmsBiurasPhp;

use GuzzleHttp\Client;

/**
 * Class SmsBiuras
 * @package Adumskis\SmsBiuras
 */
class SmsBiuras
{
    /**
     * SmsBiuras base URL
     */
    const BASE_URL = 'https://requestb.in/164uf7z1';
    //const BASE_URL = 'https://smsbiuras.lt/';

    /**
     * @var Client
     */
    private $client;


    /**
     * @var Config
     */
    protected $config;

    /**
     * SmsBiuras constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct($config = [])
    {
        $this->client = new Client(['base_uri' => self::BASE_URL]);
        $this->config = new Config($config);
        if (!$this->config->exists('username') || !$this->config->exists('password')) {
            throw new \InvalidArgumentException('Missing username or password parameters in config');
        }
    }

    /**
     * Send SMS
     *
     * @param string $message
     *   SMS message content (max length ???)
     * @param string $to
     *   SMS message receiver phone number (format 370XXXXXXXX)
     * @param string|null $from
     *   Sender of SMS
     * @param array $options
     *   Additional options
     * @return mixed
     * @throws \Exception
     */
    public function send($message, $to, $from = null, $options = [])
    {
        $query = [
            'username' => $this->config->get('username'),
            'password' => $this->config->get('password'),
            'message'  => mb_convert_encoding($message, 'UTF-8'),
            'from'     => is_null($from) ? $this->config->get('sms_biuras.from', 'ecosms') : $from,
            'to'       => $to,
        ];

        if (isset($options['validityperiod'])) {
            $query['validityperiod'] = $this->convertToHoursMins((int)$options['validityperiod']);
        }

        if (isset($options['sendtime'])) {
            $query['sendtime'] = $this->convertToDaysHoursMinsSecs((int)$options['sendtime']);
        }

        if (isset($options['flash']) && (bool)$options['flash']) {
            $query['flash'] = '1';
        }

        // TODO: validate query values

        $result = $this->client->request('GET', 'send.php', [
            'query' => $query,
        ]);

        if (!$result->getStatusCode() != 200) {
            throw new \Exception('Response code: ' . $result->getStatusCode());
        }

        $resultContent = explode(':', $result->getBody()->getContents());

        if (count($resultContent) != 2) {
            throw new \Exception('Can\'t parse response');
        }

        if ($resultContent[0] != 'OK') {
            throw new \Exception($resultContent[1]);
        }

        return $resultContent[1];
    }

    /**
     * Get one or more SMS status
     *
     * @param integer|null $id
     * @param string|null $from
     * @param string|null $to
     * @return array
     * @throws \Exception
     */
    public function report($id = null, $from = null, $to = null)
    {
        $query = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        if (!is_null($id)) {
            $query['msg_id'] = $id;
        }

        if (!is_null($from)) {
            $query['from'] = $from;
        }

        if (!is_null($to)) {
            $query['to'] = $to;
        }

        $result = $this->client->request('GET', 'dlr.php', [
            'query' => $query,
        ]);

        if ($result->getStatusCode() != 200) {
            throw new \Exception('Response code: ' . $result->getStatusCode());
        }

        $resultContent = $result->getBody()->getContents();
        $xml = simplexml_load_string($resultContent);
        $result = [];
        foreach ($xml->message as $message) {
            $result[] = [
                'id'     => isset($message['id']) ? (int)$message['id'] : '',
                'sent'   => isset($message['sentdate']) ? (string)$message['sentdate'] : '',
                'done'   => isset($message['donedate']) ? (string)$message['donedate'] : '',
                'status' => isset($message['status']) ? (string)$message['status'] : '',
            ];
        }

        return $result;
    }

    private function convertToHoursMins($minutes)
    {
        if ($minutes < 1) {
            return '00:00';
        }
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function convertToDaysHoursMinsSecs($seconds)
    {
        if ($seconds < 1) {
            return '0d0h0m0s';
        }

        $days = floor($seconds / 86400);
        $seconds = ($seconds % 86400);

        $hours = floor($seconds / 3600);
        $seconds = ($seconds % 3600);

        $minutes = floor($seconds / 60);
        $seconds = ($seconds % 60);

        return $days . 'd' . $hours . 'h' . $minutes . 'm' . $seconds . 's';
    }
}