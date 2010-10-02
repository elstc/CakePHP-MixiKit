<?php

/**
 * Twitter API Datasource Test Case
 *
 * for CakePHP 1.3+
 * PHP version 5.2+
 *
 * Copyright 2010, ELASTIC Consultants Inc. (http://elasticconsultants.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @version    1.0
 * @author     nojimage <nojima at elasticconsultants.com>
 * @copyright  2010, ELASTIC Consultants Inc.
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://elasticconsultants.com
 * @package    twitter_kit
 * @subpackage twitter_kit.tests.cases.datasources
 * @since      MixiKit 1.0
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 */
App::import('Datasource', 'MixiKit.MixiGraphApi');
App::import('Model', array('AppModel', 'Model'));
App::import('Core', array('Router'));

ConnectionManager::create('test_mixi_graph_api',
                array(
                    'datasource' => 'MixiKit.MixiGraphApi',
                    'oauth_consumer_key' => 'cf60e9095216f4ca5bd6',
                    'oauth_consumer_secret' => '51f0044ddab1f3acd3bfcbf40931b6c709bef89d',
                    'oauth_callback' => 'http://nojimage.local/mixi_connect',
        ));

class TestModel extends CakeTestModel {

    public $name = 'TestModel';
    public $useDbConfig = 'test_mixi_graph_api';
    public $useTable = false;

}

class MixiGraphApiSourceTestCase extends CakeTestCase {

    /**
     *
     * @var MixiGraphApiSource
     */
    public $TestSource;
    /**
     *
     * @var TestModel
     */
    public $TestModel;


    function startTest() {
        $this->TestModel = new TestModel();
    }

    function endTest() {
        unset($this->TestModel);
    }

    function testInit() {

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        $this->assertIsA($ds, 'MixiGraphApiSource');

        $this->assertIsA($this->TestModel->getDataSource(), 'MixiGraphApiSource');

        $this->assertEqual($ds->oauth_consumer_key, 'cf60e9095216f4ca5bd6');
        $this->assertEqual($ds->oauth_consumer_secret, '51f0044ddab1f3acd3bfcbf40931b6c709bef89d');
    }

    function testGetRequestUrl() {

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        $options = array();
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('platform' => MixiGraphApiSource::AUTHORIZATION_PLATFORM_PC);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('platform' => MixiGraphApiSource::AUTHORIZATION_PLATFORM_MOBILE);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'http://m.mixi.jp/connect_authorize.pl?display=pc&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('display' => MixiGraphApiSource::AUTHORIZATION_DISPLAY_PC);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('display' => MixiGraphApiSource::AUTHORIZATION_DISPLAY_SMARTPHONE);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=smartphone&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('scope' => MixiGraphApiSource::SCOPE_R_PROFILE);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_profile&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('scope' => MixiGraphApiSource::SCOPE_R_UPDATES);
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_updates&client_id=cf60e9095216f4ca5bd6&response_type=code');

        $options = array('scope' => array(MixiGraphApiSource::SCOPE_R_VOICE, MixiGraphApiSource::SCOPE_W_VOICE));
        $url = $ds->getRequestUrl($options);
        $this->assertEqual($url, 'https://mixi.jp/connect_authorize.pl?display=pc&scope=r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code');
    }

    function testGetAccessToken() {

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        $result = $ds->getAccessToken();
        $this->assertFalse($result);

        $code = 'aa';
        $result = $ds->getAccessToken($code);
        $this->assertFalse($result, 'Invalid code: %s');

        return $this->skipIf(true);
        // -- 以下をテストする場合、codeは3分のみ有効なので、以下のURLにアクセスして毎回取得する
        // https://mixi.jp/connect_authorize.pl?display=pc&scope=r_profile%20r_updates%20r_voice%20w_voice&client_id=cf60e9095216f4ca5bd6&response_type=code
        $code = '5fc3e68f53708f102f627cc854bbf832b3cc5a98';
        $result = $ds->getAccessToken($code);
        $this->assertTrue(is_array($result), 'Success get token: %s');
        $this->assertTrue(isset($result['refresh_token']), 'return refresh_token: %s');
        $this->assertTrue(isset($result['expires_in']), 'return expires_in: %s');
        $this->assertTrue(isset($result['access_token']), 'return access_token: %s');
        $this->assertTrue(isset($result['scope']), 'return scope: %s');
        $this->assertTrue(isset($result['expires']), 'return expires: %s');

        debug($result);
    }

    function testRefreshAccessToken() {

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        $tokens = array(
            'refresh_token' => 'a6065549ec8e7be1b0505c31142ff200a7de8869',
            'expires_in' => '900',
            'access_token' => '0bca91686a196368e3e135beef38a6ad70808941',
            'scope' => 'r_voice w_voice',
        );

        $result = $ds->refreshAccessToken();
        $this->assertFalse($result, 'empty refresh_token: %s');

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $result = $ds->refreshAccessToken($refreshToken);
        $this->assertTrue(is_array($result), 'Success get token: %s');
        $this->assertTrue(isset($result['refresh_token']), 'return refresh_token: %s');
        $this->assertTrue(isset($result['expires_in']), 'return expires_in: %s');
        $this->assertTrue(isset($result['access_token']), 'return access_token: %s');
        $this->assertTrue(isset($result['scope']), 'return scope: %s');
        $this->assertTrue(isset($result['expires']), 'return expires: %s');

        debug($result);
    }

    function testOauthRequestToken() {

        return $this->skipIf(true);
        $result = $this->TestSource->oauth_request_token(Router::url('/mixi_kit/callback', true));

        $this->assertTrue(is_array($result));
        $this->assertTrue(is_string($result['code']));
    }

    function testOauthAuthorize() {

        return $this->skipIf(true);
        $result = $this->TestSource->oauth_authorize('dummy_token');
        $this->assertEqual('http://api.twitter.com/oauth/authorize?oauth_token=dummy_token', $result);

        $token = $this->TestSource->oauth_request_token(Router::url('/twitter_kit/callback', true));
        $result = $this->TestSource->oauth_authorize();
        $this->assertEqual('http://api.twitter.com/oauth/authorize?oauth_token=' . $token['oauth_token'], $result);
    }

    function testOauthAuthenticate() {

        return $this->skipIf(true);
        $result = $this->TestSource->oauth_authenticate('dummy_token');
        $this->assertEqual('http://api.twitter.com/oauth/authenticate?oauth_token=dummy_token', $result);

        $token = $this->TestSource->oauth_request_token(Router::url('/twitter_kit/callback', true));
        $result = $this->TestSource->oauth_authenticate();
        $this->assertEqual('http://api.twitter.com/oauth/authenticate?oauth_token=' . $token['oauth_token'], $result);
    }

    function testOauthAccessToken() {

        return $this->skipIf(true);

        $requestToken = $this->TestSource->oauth_request_token(Router::url('/openlist/twitter_kit/callback', true));
        $authUrl = $this->TestSource->oauth_authorize();

        debug($authUrl);

        $url = 'http://localhost/openlist/twitter_kit/callback?oauth_token=ly4DCCcq4gddZMuFNp7vbJgQiSna7Hoq4Xd7CuGOOk&oauth_verifier=Nvnw5OnMkVFv5S4tjLvKLLmsMbDyKEM92HeEILC6u7g';

        $oauth_token = 'ly4DCCcq4gddZMuFNp7vbJgQiSna7Hoq4Xd7CuGOOk';
        $oauth_verifier = 'Nvnw5OnMkVFv5S4tjLvKLLmsMbDyKEM92HeEILC6u7g';

        $token = $this->TestSource->oauth_access_token($oauth_token, $oauth_verifier);

        if (is_string($token)) {

            $this->assertEqual('Invalid / expired Token', $token);
        } else {

            $this->assertTrue(is_array($token));
            $this->assertTrue(is_string($token['oauth_token']));
            $this->assertTrue(is_string($token['oauth_token_secret']));
            $this->assertTrue(is_string($token['user_id']));
            $this->assertTrue(is_string($token['screen_name']));
            $this->assertEqual($token['oauth_token'], $this->TestSource->oauth_token);
            $this->assertEqual($token['oauth_token_secret'], $this->TestSource->oauth_token_secret);
        }
    }

    function testSetToken() {

        return $this->skipIf(true);
        $this->TestSource->reset();
        $result = $this->TestSource->setToken('');
        $this->assertFalse($result);
        $this->assertEqual('', $this->TestSource->oauth_token);
        $this->assertEqual('', $this->TestSource->oauth_token_secret);

        $this->TestSource->reset();
        $result = $this->TestSource->setToken(array('oauth_token' => 'dummy_token', 'oauth_token_secret' => 'dummy_secret'));
        $this->assertTrue($result);
        $this->assertEqual('dummy_token', $this->TestSource->oauth_token);
        $this->assertEqual('dummy_secret', $this->TestSource->oauth_token_secret);

        $this->TestSource->reset();
        $this->assertEqual('', $this->TestSource->oauth_token);
        $this->assertEqual('', $this->TestSource->oauth_token_secret);

        $result = $this->TestSource->setToken('dummy_token2', 'dummy_secret2');
        $this->assertTrue($result);
        $this->assertEqual('dummy_token2', $this->TestSource->oauth_token);
        $this->assertEqual('dummy_secret2', $this->TestSource->oauth_token_secret);
    }

}