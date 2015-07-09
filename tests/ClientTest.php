<?php

use Mockery as m;

use Devfactory\Mollom\Client;
use Devfactory\Mollom\Mollom;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

use GuzzleHttp\Client as Guzzle;

class ClientTest extends PHPUnit_Framework_TestCase {

  protected $client;
  protected $guzzle;
  protected $request;

  protected $session;
  protected $log;
  protected $config;

  protected $privateKey = 'MOCK_PRIVATE';
  protected $publicKey  = 'MOCK_PUBLIC';
  protected $language   = 'MOCK_LANGUAGE';

  /**
   * Setup resources and dependencies.
   *
   * @return void
   */
  public function setUp() {
    // Setup app
    $this->app = m::mock('AppMock');
    $this->app->shouldReceive('instance')->andReturn($this->app);

    // Mock facades
    \Illuminate\Support\Facades\Facade::setFacadeApplication($this->app);
    \Illuminate\Support\Facades\Session::swap($this->session = m::mock('validatorMock'));
    \Illuminate\Support\Facades\Log::swap($this->log = m::mock('authMock'));
    \Illuminate\Support\Facades\Config::swap($this->config = m::mock('urlMock'));

    // Mock facades
    $this->config->shouldReceive('get')->with('mollom.dev', false)->andReturn(true);
    $this->config->shouldReceive('get')->with("mollom.mollom_public_key")->andReturn($this->publicKey);
    $this->config->shouldReceive('get')->with("mollom.mollom_private_key")->andReturn($this->privateKey);

    // Mock
    $this->client = new Client(
      $this->guzzle     = m::mock('GuzzleHttp\Client'),
      $this->request    = m::mock('Illuminate\Http\Request')
    );
  }

  public function tearDown()  {
    m::close();
  }

  /**
   * Test the dev server
   */
  public function testClientDevConfig() {
    $this->assertEquals('dev.mollom.com', $this->client->server);
  }

  /**
   * Test load configuration Public Key
   */
  public function testloadConfigurationPublicKey() {
    $name = 'publicKey';

    // Mock
    $this->config->shouldReceive('get')->with('mollom.mollom_public_key')->andReturn($this->publicKey);

    // Act
    $this->assertEquals($this->publicKey, $this->client->loadConfiguration($name));
  }

  /**
   * Test load configuration Private Key
   */
  public function testloadConfigurationPrivateKey() {
    $name = 'privateKey';

    // Mock
    $this->config->shouldReceive('get')->with('mollom.mollom_private_key')->andReturn($this->privateKey);

    // Act
    $this->assertEquals($this->privateKey, $this->client->loadConfiguration($name));
  }

  /**
   * Test load configuration Private Key
   */
  public function testloadConfigurationExpectedLanguages() {
    $name = 'expectedLanguages';

    // Mock
    $this->config->shouldReceive('get')->with('mollom.mollom_languages_expected')->andReturn($this->language);

    // Act
    $this->assertEquals($this->language, $this->client->loadConfiguration($name));
  }

  /**
   * Test get request
   */
  public function testRequestGET() {
    // Prepare
    $method  = 'GET';
    $server  = 'MOCK_SERVER';
    $path    = 'MOCK_PATH';
    $query   = 'MOCK=param';
    $headers = array('MOCK_HEADERS');

    $key = 'query';

    // Mock
    $this->mockRequest($method, $server, $path, $query, $headers, $key);

    // Act
    $reflector = new ReflectionClass('Devfactory\Mollom\Client');
    $methodRequest = $reflector->getMethod('request');
    $methodRequest->setAccessible(true);

    $information = $methodRequest->invokeArgs($this->client, array($method, $server, $path, $query, $headers));

    // Assert
    $this->assertObjectHasAttribute('code', $information);
    $this->assertObjectHasAttribute('message', $information);
    $this->assertObjectHasAttribute('headers', $information);
    $this->assertArrayHasKey('content-type', $information->headers);
    $this->assertObjectHasAttribute('body', $information);

    $this->assertEquals('MOCK_CODE', $information->code);
    $this->assertEquals('MOCK_PHRASE', $information->message);
    $this->assertEquals('MOCK_HEADERS', $information->headers['content-type']);
    $this->assertEquals('MOCK_BODY', $information->body);
  }

  /**
   * Test post request
   */
  public function testRequestPOST() {
    // Prepare
    $method  = 'POST';
    $server  = 'MOCK_SERVER';
    $path    = 'MOCK_PATH';
    $query   = 'MOCK=param';
    $headers = array('MOCK_HEADERS');

    $key = 'body';

    // Mock
    $this->mockRequest($method, $server, $path, $query, $headers, $key);

    // Act
    $reflector = new ReflectionClass('Devfactory\Mollom\Client');
    $methodRequest = $reflector->getMethod('request');
    $methodRequest->setAccessible(true);

    $information = $methodRequest->invokeArgs($this->client, array($method, $server, $path, $query, $headers));

    // Assert
    $this->assertObjectHasAttribute('code', $information);
    $this->assertObjectHasAttribute('message', $information);
    $this->assertObjectHasAttribute('headers', $information);
    $this->assertArrayHasKey('content-type', $information->headers);
    $this->assertObjectHasAttribute('body', $information);

    $this->assertEquals('MOCK_CODE', $information->code);
    $this->assertEquals('MOCK_PHRASE', $information->message);
    $this->assertEquals('MOCK_HEADERS', $information->headers['content-type']);
    $this->assertEquals('MOCK_BODY', $information->body);
  }

  /**
   * Test request Exception
   */
  public function testRequestException(){
    // Prepare
    $method  = 'POST';
    $server  = 'MOCK_SERVER';
    $path    = 'MOCK_PATH';
    $query   = 'MOCK=param';
    $headers = array('MOCK_HEADERS');

    $key = 'body';

    // Mock
    $requestInterface  = m::mock('GuzzleHttp\Message\RequestInterface');
    $this->guzzle->shouldReceive('createRequest')->with($method, $server . '/' . $path, array($key => array('MOCK' => 'param')))->andThrow(new \GuzzleHttp\Exception\RequestException('MOCK_MESSAGE', $requestInterface));

    // Act
    $reflector = new ReflectionClass('Devfactory\Mollom\Client');
    $methodRequest = $reflector->getMethod('request');
    $methodRequest->setAccessible(true);

    $information = $methodRequest->invokeArgs($this->client, array($method, $server, $path, $query, $headers));

    // Assert
    $this->assertObjectHasAttribute('code', $information);
    $this->assertObjectHasAttribute('message', $information);
    $this->assertObjectHasAttribute('body', $information);
  }

  /**
   * Test request transfert Exception
   */
  public function testRequestTransferException(){
    // Prepare
    $method  = 'POST';
    $server  = 'MOCK_SERVER';
    $path    = 'MOCK_PATH';
    $query   = 'MOCK=param';
    $headers = array('MOCK_HEADERS');

    $key = 'body';

    // Mock
    $this->guzzle->shouldReceive('createRequest')->with($method, $server . '/' . $path, array($key => array('MOCK' => 'param')))->andThrow(new \GuzzleHttp\Exception\TransferException('MOCK_MESSAGE'));

    // Act
    $reflector = new ReflectionClass('Devfactory\Mollom\Client');
    $methodRequest = $reflector->getMethod('request');
    $methodRequest->setAccessible(true);

    $information = $methodRequest->invokeArgs($this->client, array($method, $server, $path, $query, $headers));

    // Assert
    $this->assertObjectHasAttribute('code', $information);
    $this->assertObjectHasAttribute('message', $information);
    $this->assertObjectHasAttribute('body', $information);
  }

  /**
   * Helper to mock the request
   */
  public function mockRequest($method, $server, $path, $query, $headers, $key) {
    $mockRequest = m::mock('GuzzleHttp\Message\RequestInterface');
    $mockRequest->shouldReceive('setHeaders')->with($headers)->andReturn(true);
    $this->guzzle->shouldReceive('createRequest')->with($method, $server . '/' . $path, array($key => array('MOCK' => 'param')))->andReturn($mockRequest);

    $response = m::mock('repsonse');
    $response->shouldReceive('getStatusCode')->andReturn('MOCK_CODE');
    $response->shouldReceive('getReasonPhrase')->andReturn('MOCK_PHRASE');
    $response->shouldReceive('getHeader')->andReturn('MOCK_HEADERS');

    $body = m::mock('body');
    $body->shouldReceive('getContents')->andReturn('MOCK_BODY');

    $response->shouldReceive('getBody')->andReturn($body);

    $this->guzzle->shouldReceive('send')->with($mockRequest)->andReturn($response);
  }

  /**
   * Test captcha Audio
   */
  public function testCaptchaAudio() {
    // Prepare
    $id = 'MOCK_ID';
    $type = 'audio';

    $captcha = array(
      'id' => 'MOCK_ID',
      'url' => 'MOCK_URL'
    );

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('createCaptcha'));
    $mock->expects($this->once())->method('createCaptcha')->with(
      array(
        'contentId' => $id,
        'type' => $type,
      ))->will($this->returnValue($captcha));

    // Mock session
    $this->session->shouldReceive('put')->with('mollom' . $id, $captcha['id'])->andReturn(true);

    // Act
    $captcha = $mock->captcha($id, $type);

    // Assert
    $this->assertEquals('<audio controls><source src="MOCK_URL" type="audio/mpeg">Your browser does not support the audio tag.</audio>', $captcha);
  }

  /**
   * Test captcha Image
   */
  public function testCaptchaImage() {
    // Prepare
    $id = 'MOCK_ID';
    $type = 'image';

    $captcha = array(
      'id' => 'MOCK_ID',
      'url' => 'MOCK_URL'
    );

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('createCaptcha'));
    $mock->expects($this->once())->method('createCaptcha')->with(
      array(
        'contentId' => $id,
        'type' => $type,
      ))->will($this->returnValue($captcha));

    // Mock session
    $this->session->shouldReceive('put')->with('mollom' . $id, $captcha['id'])->andReturn(true);

    // Act
    $captcha = $mock->captcha($id, $type);

    // Assert
    $this->assertEquals('<img src="MOCK_URL" alt="Type the characters you see in this picture." />', $captcha);
  }

  /**
   * Test captcha error
   */
  public function testCaptchaError() {
    // Prepare
    $id = 'MOCK_ID';
    $type = 'image';

    $captcha = 'WRONG_RESPONSE';

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('createCaptcha'));
    $mock->expects($this->once())->method('createCaptcha')->with(
      array(
        'contentId' => $id,
        'type' => $type,
      ))->will($this->returnValue($captcha));

    // Act
    $captcha = $mock->captcha($id, $type);

    // Assert
    $this->assertFalse($captcha);
  }

  /**
   * Test validation solved
   */
  public function testValidateSolved() {
    // Prepare
    $id = 'MOCK_ID';
    $solution = 'MOCK_SOLUTION';
    $parameters = array('MOCK_ID');

    $response = array(
      'solved' => true
    );

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('checkCaptcha'));
    $mock->expects($this->once())->method('checkCaptcha')->with(
      array(
        'id' => $id,
        'solution' => $solution,
      ))->will($this->returnValue($response));

    $this->session->shouldReceive('get')->with('mollom' . $parameters[0])->andReturn($id);

    $this->assertTrue($mock->validate($solution, $parameters));
  }

  /**
   * Test validation solved
   */
  public function testValidateUnSolved() {
    // Prepare
    $id = 'MOCK_ID';
    $solution = 'MOCK_SOLUTION';
    $parameters = array('MOCK_ID');

    $response = array(
      'solved' => false
    );

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('checkCaptcha'));
    $mock->expects($this->once())->method('checkCaptcha')->with(
      array(
        'id' => $id,
        'solution' => $solution,
      ))->will($this->returnValue($response));

    $this->session->shouldReceive('get')->with('mollom' . $parameters[0])->andReturn($id);

    $this->assertFalse($mock->validate($solution, $parameters));
  }

  public function mockComment($spamClassification, $response = false) {
    // Prepare
    $comment = array(
      'title' => 'MOCK_TITLE',
      'body' => 'MOCK_BODY',
      'name' => 'MOCK_NAME',
      'mail' => 'MOCK_MAIL'
    );

    $userid = 'MOCK_USER_ID';
    $ip = 'MOCK_IP';

    if(!$response){
      $response = array('id' => 'MOCK_ID', 'spamClassification' => $spamClassification);
    }

    $this->request->shouldReceive('getClientIp')->andReturn($ip);

    // Mock internal method
    $mock = $this->getMock('Devfactory\Mollom\Client', array('checkContent'), array(null, $this->request));
    $mock->expects($this->once())->method('checkContent')->with(array(
        'checks' => array('spam'),
        'postTitle' => $comment['title'],
        'postBody' => $comment['body'],
        'authorMail' => $comment['mail'],
        'authorName' => $comment['name'],
        'authorIp' => $ip,
        'authorId' => $userid, // If the author is logged in.
      ))->will($this->returnValue($response));

    return $mock->comment($comment, $userid);
  }

  /**
   * Test comment ham
   */
  public function testCommentHam() {
    $result = $this->mockComment('ham');
    $this->assertEquals('ham', $result);
  }


  /**
   * Test comment unsure
   */
  public function testCommentUnsure() {
    $result = $this->mockComment('unsure');
    $this->assertEquals('unsure', $result);
  }

  /**
   * Test comment spam
   */
  public function testCommentSpam() {
    $result = $this->mockComment('spam');
    $this->assertEquals('spam', $result);
  }

  /**
   * Test comment exception unclassified
   */
  public function testCommentUnclassified() {
    $this->setExpectedException('\Devfactory\Mollom\Exceptions\UnknownSpamClassificationException');
    $result = $this->mockComment('unclassified');
  }

/**
   * Test comment exception unclassified
   */
  public function testCommentUnavailable() {
    $response = array('spamClassification' => 'unclassified');
    $this->setExpectedException('\Devfactory\Mollom\Exceptions\SystemUnavailableException');
    $result = $this->mockComment('unclassified', $response);
  }
}