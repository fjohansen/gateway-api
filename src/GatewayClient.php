<?php

namespace Finna\GatewayApi;

class GatewayClient
{
    public const API_ENDPOINT = 'https://gatewayapi.com/rest/mtsms';

    private string $api_key;
    private string $sender;
    private string $message;
    private array $recipients = [];

    /**
     * @param string|null $api_key
     *
     * @throws \Finna\GatewayApi\ParameterException
     */
    public function __construct(?string $api_key)
    {
        if (!$api_key) {
            throw new ParameterException("Missing parameter api_key");
        }
        $this->api_key = $api_key;
    }

    /**
     * @param string $sender
     *
     * @return \Finna\GatewayApi\GatewayClient
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return \Finna\GatewayApi\GatewayClient
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $recipients
     *
     * @return \Finna\GatewayApi\GatewayClient
     */
    public function setRecipients(array $recipients): self
    {
        foreach ($recipients as $recipient) {
            if (strlen($recipient) === 8) {
                // Length is 8, assume it's a Norwegian mobile number
                $recipient = "47" . $recipient;
            }
            $this->recipients[] = ['msisdn' => $recipient];
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getJsonPayload(): string
    {
        $arrPayload = [
            "sender"     => $this->sender,
            "message"    => $this->message,
            "recipients" => $this->recipients
        ];

        return json_encode($arrPayload);
    }

    /**
     * Send off the message
     *
     * @return object
     * @throws \Finna\GatewayApi\ParameterException
     */
    public function send(): object
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ":");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getJsonPayload());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result);
        if (isset($json->code)) {
            // This means an error code.
            throw new ParameterException("Error: Possibly auth error from API. Error code: " . $json->code);
        }

        return $json;
    }

    /**
     * @param string $sender
     * @param array  $recipients
     * @param string $message
     *
     * @return bool
     * @throws \Finna\GatewayApi\ParameterException
     */
    public static function sendMessage(string $sender, array $recipients, string $message): bool
    {
        if (empty($sender)) {
            $sender = "Totaltekst";
        }

        if (empty($recipients)) {
            throw new ParameterException("Missing or invalid parameter 'recipients' (array)");
        }

        if (empty($message)) {
            throw new ParameterException("Missing or invalid parameter 'message'");
        }

        $secret = getenv('SMS_API_TOKEN');
        $client = (new self($secret))
            ->setSender($sender)
            ->setRecipients($recipients)
            ->setMessage($message);


        $result = $client->send();

        return (bool)$result;
    }
}