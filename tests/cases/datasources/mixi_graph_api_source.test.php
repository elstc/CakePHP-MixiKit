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
                    'oauth_callback' => 'http://nojimage.local/mixi_connect/mixi_kit/oauth/callback',
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
        $this->assertTrue(isset($result['error']), 'Invalid code: %s');

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

    function testSetToken() {

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        $ds->reset();
        $result = $ds->setToken('');
        $this->assertFalse($result);
        $this->assertEqual('', $ds->oauth_token);

        $ds->reset();
        $result = $ds->setToken(array('access_token' => 'dummy_token'));
        $this->assertTrue($result);
        $this->assertEqual('dummy_token', $ds->oauth_token);

        $ds->reset();
        $this->assertEqual('', $ds->oauth_token);

        $result = $ds->setToken('dummy_token2');
        $this->assertTrue($result);
        $this->assertEqual('dummy_token2', $ds->oauth_token);
    }

    function testGetVoiceStatusesUserTimeline() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getVoiceStatusesUserTimeline();
        $this->assertTrue(is_array($result));
        $this->assertTrue(Set::numeric(array_keys($result)));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['text']));
        $this->assertTrue(isset($result[0]['created_at']));
        $this->assertTrue(is_array($result[0]['user']));

        #debug($result);
    }

    function testGetVoiceStatusesFriendsTimeline() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getVoiceStatusesFriendsTimeline();
        $this->assertTrue(is_array($result));
        $this->assertTrue(Set::numeric(array_keys($result)));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['text']));
        $this->assertTrue(isset($result[0]['created_at']));
        $this->assertTrue(is_array($result[0]['user']));

        debug($result);
    }

    function testGetVoiceStatusesShow() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getVoiceStatusesShow();
        $this->assertFalse($result);

        $id = '5q1w3ft5tfixp-20101003004857';
        $result = $ds->getVoiceStatusesShow($id);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        #debug($result);
    }

    function testGetVoiceRepliesShow() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getVoiceRepliesShow();
        $this->assertFalse($result);

        $id = '5q1w3ft5tfixp-20101003004857';
        $result = $ds->getVoiceRepliesShow($id);
        $this->assertTrue(is_array($result));
        $this->assertTrue(Set::numeric(array_keys($result)));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['text']));
        $this->assertTrue(isset($result[0]['created_at']));
        $this->assertTrue(is_array($result[0]['user']));

        #debug($result);
    }

    function testGetVoiceFavoritesShow() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getVoiceFavoritesShow();
        $this->assertFalse($result);

        $id = '5q1w3ft5tfixp-20101003004857';
        $result = $ds->getVoiceFavoritesShow($id);
        $this->assertTrue(is_array($result));
        $this->assertTrue(Set::numeric(array_keys($result)));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['url']));
        $this->assertTrue(isset($result[0]['profile_image_url']));
        $this->assertTrue(isset($result[0]['screen_name']));

        #debug($result);
    }

    function testPostVoiceStatuses() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $text = 'つぶやきの投稿';
        $result = $ds->postVoiceStatuses($text);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);
        $postId = $result['id'];

        // -- コメントの投稿
        $result = $ds->postVoiceReplies(array('post_id' => $postId, 'text' => 'コメントテスト'));
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);
        $commentId = $result['id'];

        // -- コメントの削除
        $result = $ds->deleteVoiceReplies(array('post_id' => $postId, 'comment_id' => $commentId));
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);

        // -- つぶやきの削除
        $result = $ds->deleteVoiceStatuses($postId);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);
    }

    function testPostVoiceFavorites() {

        return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        // -- イイネ!の投稿
        $postId = '5q1w3ft5tfixp-20101003004857';
        $result = $ds->postVoiceFavorites(array('post_id' => $postId));
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);
        $userId = $result['id'];

        // -- イイネ!の削除
        $result = $ds->postVoiceFavorites(array('post_id' => $postId, 'user_id' => $userId));
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['id']));
        $this->assertTrue(isset($result['text']));
        $this->assertTrue(isset($result['created_at']));
        $this->assertTrue(is_array($result['user']));

        debug($result);
    }

    function testGetPeople() {

        #return $this->skipIf(true);

        $ds = ConnectionManager::getDataSource('test_mixi_graph_api');
        /* @var $ds MixiGraphApiSource */

        // -- refreshTokenが無効になる場合あり
        $refreshToken = 'a6065549ec8e7be1b0505c31142ff200a7de8869';
        $ds->setToken($ds->refreshAccessToken($refreshToken));

        $result = $ds->getPeople();
        $this->assertTrue(is_array($result));
        $this->assertTrue(Set::numeric(array_keys($result)));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['text']));
        $this->assertTrue(isset($result[0]['created_at']));
        $this->assertTrue(is_array($result[0]['user']));

        debug($result);
    }

}