<?php namespace Devfactory\Mollom;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Application;

use GuzzleHttp\Client as Guzzle;

class Client extends Mollom {

  protected $client;

  /**
   * Overrides the connection timeout based on module configuration.
   *
   * @see Mollom::__construct().
   */
  public function __construct(Guzzle $client = null) {
    parent::__construct();

    if(Config::get('mollom::dev', false)){
      $this->server = 'dev.mollom.com';
    }

    if($client) {
      $this->client = $client;
    }else{
      $this->client = new Guzzle();
    }
  }

  /**
   * Mapping of configuration names to Drupal variables.
   *
   * @see Mollom::loadConfiguration()
   */
  public $configuration_map = array(
    'publicKey' => 'mollom_public_key',
    'privateKey' => 'mollom_private_key',
    'expectedLanguages' => 'mollom_languages_expected',
  );

  /**
   * Implements Mollom::loadConfiguration().
   */
  public function loadConfiguration($name) {
    $name = $this->configuration_map[$name];
    return Config::get('mollom::' . $name);
  }

  /**
   * Implements Mollom::saveConfiguration().
   */
  public function saveConfiguration($name, $value) {}

  /**
   * Implements Mollom::deleteConfiguration().
   */
  public function deleteConfiguration($name) {}

  /**
   * Retrieve client information
   */
  public function getClientInformation() {
    $app =  new Application;

    // Retrieve Drupal distribution and installation profile information.
    $profile_info =  array(
      'distribution_name' => 'Laravel',
      'version' => $app::VERSION,
    );

    $data = array(
      'platformName' => $profile_info['distribution_name'],
      'platformVersion' => $profile_info['version'],
      'clientName' => 'devfactory/mollom',
      'clientVersion' => '0.1.x-dev',
    );

    return $data;
  }

  /**
   * Overrides Mollom::writeLog().
   */
  function writeLog() {
    foreach ($this->log as $entry) {
      $entry['Request: ' . $entry['request']] = !empty($entry['data']) ? $entry['data'] : NULL;
      unset($entry['request'], $entry['data']);

      $entry['Request headers:'] = $entry['headers'];
      unset($entry['headers']);

      $entry['Response: ' . $entry['response_code'] . ' ' . $entry['response_message'] . ' (' . number_format($entry['response_time'], 3) . 's)'] = $entry['response'];
      unset($entry['response'], $entry['response_code'], $entry['response_message'], $entry['response_time']);

      // The client class contains the logic for recovering from certain errors,
      // and log messages are only written after that happened. Therefore, we
      // can normalize the severity of all log entries to the overall success or
      // failure of the attempted request.
      // @see Mollom::query()
      Log::error($entry, array('mollom'));
    }

    // After writing log messages, empty the log.
    $this->purgeLog();
  }

  /**
   * Implements Mollom::request().
   *
   * Basic implementation leveraging PHP's cURL extension.
   */
  protected function request($method, $server, $path, $query = NULL, array $headers = array()) {
    $key = false;
    $response = false;

    // Query to array
    parse_str($query, $query);

    // Prepare key
    if($method == 'GET') {
      $key = 'query';
    }elseif($method == 'POST') {
      $key = 'body';
    }
    // Create the request
    try {
      $request = $this->client->createRequest($method, $server . '/' . $path, array($key => $query), $headers);
      $response = $this->client->send($request);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      return (object) array(
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'body' => null,
      );
    } catch (\GuzzleHttp\Exception\TransferException $e) {
      return (object) array(
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'body' => null,
      );
    }

    // Build response
    $response = (object) array(
      'code' => $response->getStatusCode(),
      'message' => $response->getReasonPhrase(),
      'headers' => array('content-type' => $response->getHeader('content-type')),
      'body' => $response->getBody()->getContents(),
    );

    return $response;
  }

  /**
   * Generate a captcha
   *
   * @param id
   * @param type (image/audio)
   *
   * @return string
   */
  public function captcha($id, $type = 'image') {

    // Generate the captcha
    $captcha = $this->createCaptcha(
      array(
        'contentId' => $id,
        'type' => $type,
      ));

    // Check if we have an id
    if(!isset($captcha['id'])){
      return false;
    }

    // Store the captcha id
    Session::put('mollom' . $id, $captcha['id']);

    if($type == 'audio') {
      return '<audio controls><source src="' . $captcha['url'] . '" type="audio/mpeg">Your browser does not support the audio tag.</audio>';
    }

    // Generate the imge
    return '<img src="' . $captcha['url'] . '" alt="Type the characters you see in this picture." />';
  }


  /**
   * Validate if the captcha is good or not
   *
   * @param value
   * @param parameters
   *
   * @return boolean
   */
  public function validate($value, $parameters) {

    $check = $this->checkCaptcha(array('id' => Session::get('mollom' . $parameters[0] ), 'solution' => $value));

    if(!isset($check['solved'])){
      return false;
    }

    return ($check['solved']) ? true : false;
  }

  /**
   * Check if a comment is a spam
   * https://mollom.com/api#api-content
   *
   * @param comment array('title', 'body', 'name', 'mail')
   * @param userid
   *
   * @return string ham,spam,unsure
   *
   * @throw \Devfactory\Mollom\SystemUnavailableException
   * @throw \Devfactory\Mollom\UnknownSpamClassificationException
   */
  public function comment($comment, $userid = null) {

    $result = $this->checkContent(
      array(
        'checks' => array('spam'),
        'postTitle' => $comment['title'],
        'postBody' => $comment['body'],
        'authorMail' => $comment['mail'],
        'authorName' => $comment['name'],
        'authorIp' => $_SERVER['REMOTE_ADDR'],
        'authorId' => $userid, // If the author is logged in.
      ));

    // You might want to make the fallback case configurable:
    if (!is_array($result) || !isset($result['id'])) {
      throw new Exceptions\SystemUnavailableException;
    }

    if($result['spamClassification'] != 'ham'
       && $result['spamClassification'] != 'spam'
       && $result['spamClassification'] != 'unsure'){
      throw new Exceptions\UnknownSpamClassificationException;
    }

    return $result['spamClassification'];
  }

}