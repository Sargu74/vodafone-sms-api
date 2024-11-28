<?php

interface MessageInterface
{
  public function send(array $data);
}

class VodafoneAdapter implements MessageInterface
{
  /**
   * Vodafone API Credentails
   * @var array
   */
  protected $credentials;
  /**
   * HMAC Secure HASH
   * @var string
   */
  protected $secureHash;

  public function __construct($credentials)
  {
    $this->credentials = $credentials;
  }

  /**
   * Send SMS
   * @param  array  $data send to, message text
   * @return json
   */
  public function send(array $data)
  {
    $result = $this->processMessageSend($data);

    header('Content-type: application/json');
    return $result;
  }

  /**
   * Proccess HTTP Request to send the SMS
   * @param  array $data
   * @return string|bool response data
   */
  protected function processMessageSend(array $data): string | bool
  {
    $url = 'https://smsws.vodafone.pt:443/SmsBroadcastWs/service.web';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
      $ch,
      CURLOPT_POSTFIELDS,
      "<soapenv:Envelope
        xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
        xmlns:sms='http://smswebservices.vodafone.pt/SmsBroadcast'>
        <soapenv:Header/>
        <soapenv:Body>
          <sms:sendShortMessage>
            <authentication>
              <msisdn>{$this->credentials['accountId']}</msisdn>
              <password>{$this->credentials['password']}</password>
            </authentication>
            <responseReception>false</responseReception>
            <destination type='msisdn'>{$data['to']}</destination>
            <text>{$data['text']}</text>
          </sms:sendShortMessage>
        </soapenv:Body>
      </soapenv:Envelope>"
    );

    $result = str_replace(array("\n", "\r", "\t"), '', curl_exec($ch));
    $xml = $this->jsonSerialize($result);
    $object = new stdClass();
    $object->response[] = $xml;

    curl_close($ch);

    return json_encode($object, JSON_PRETTY_PRINT);
  }

  protected function jsonSerialize($result): array | null
  {
    // text node (or mixed node represented as text or self closing tag)
    if (!count($result)) {
      return $result[0] == $result
        ? trim($result) : null;
    }

    // process all child elements and their attributes
    foreach ($this as $tag => $element) {
      // attributes first
      foreach ($element->attributes() as $name => $value) {
        $array[$tag][$name] = $value;
      }
      // child elements second
      foreach ($element as $name => $value) {
        $array[$tag][$name] = $value;
      }
    }

    return $array;
  }
}
