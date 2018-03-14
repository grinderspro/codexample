<?php

namespace Grinderspro;

/**
 * Class iqSmsSending
 *
 * Wrapper class for iqsms.ru
 *
 * @author Grigorij Miroshnichenko <grinderspro@gmail.com>
 * @copyright 2015
 */

class iqSmsSending
{
    const API_LOGIN                = SMS_API_LOGIN;
    const API_PASSWORD             = SMS_API_PASSWORD;
    const API_HOST                 = 'api.iqsms.ru';
    const SENDER_NAME              = SMS_SENDER_NAME;
    const ACTIVE                   = true;
    const ERROR_EMPTY_API_LOGIN    = 'Empty api login not allowed';
    const ERROR_EMPTY_API_PASSWORD = 'Empty api password not allowed';
    const ERROR_EMPTY_RESPONSE     = 'errorEmptyResponse';

    public function getHost() {
        return self::API_HOST;
    }

    public function status($messages) {
        return $this->_sendRequest('status', array('messages' => $messages));
    }

    /**
     * Получаем балланс
     * @return mixed
     */
    public function balance() {
        return $this->_sendRequest('balance');
    }

    /**
     * @param string $messages
     * @param null $statusQueueName
     * @param null $scheduleTime
     * @return bool|mixed
     */
    public function send($messages, $statusQueueName = null, $scheduleTime = null) {

        if (!empty($this->sender))
            foreach ($messages as &$message)
                $message['sender'] = self::SENDER_NAME;


        $params = array(
            'messages'        => $messages,
            'statusQueueName' => $statusQueueName,
            'scheduleTime'    => $scheduleTime,
        );

        if (!self::ACTIVE)
            return true;


        return $this->_sendRequest('send', $params);
    }

    private function _sendRequest($uri, $params = null) {
        $url    = $this->_getUrl($uri);
        $data   = $this->_formPacket($params);
        $client = curl_init($url);
        curl_setopt_array($client,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPHEADER     => array('Host: ' . $this->getHost()),
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_CONNECTTIMEOUT => 10,
            ));

        $body = curl_exec($client);

        curl_close($client);

        if (empty($body))
            throw new Exception(self::ERROR_EMPTY_RESPONSE);

        $decodedBody = json_decode($body, true);
        if (is_null($decodedBody))
            throw new Exception($body);

        return $decodedBody;
    }

    private function _getUrl($uri) {
        return 'http://' . self::API_HOST . '/messages/v2/' . $uri . '.json';
    }

    private function _formPacket($params = null) {
        $params['login']    = self::API_LOGIN;
        $params['password'] = self::API_PASSWORD;

        foreach ($params as $key => $value)
            if (empty($value))
                unset($params[$key]);

        $packet = json_encode($params);
        return $packet;
    }

}