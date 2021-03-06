<?php

App::import('Core', array('Xml', 'Cache'));
App::import('Core', 'HttpSocket');

/**
 * Mixi API Datasource
 *
 * for CakePHP 1.3+
 * PHP version 5.2+
 *
 * Copyright 2010, ELASTIC Consultants Inc. (http://elasticconsultants.com)
 *
 * Licensed under The GNU GENERAL PUBLIC LICENSE Version 3
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @version    1.0
 * @author     nojimage <nojima at elasticconsultants.com>
 * @copyright  2010, ELASTIC Consultants Inc.
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.models.datasources
 * @since      MixiKit 1.0
 * @license    GNU GENERAL PUBLIC LICENSE Version 3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 *
 * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/api_common_spec
 */
class MixiGraphApiSource extends DataSource {

    public $description = 'Mixi Graph API';
    /**
     *
     * @var HttpSocket
     */
    public $Http;
    /**
     *
     * @var string
     */
    public $oauth_consumer_key;
    /**
     *
     * @var string
     */
    public $oauth_consumer_secret;
    /**
     *
     * @var string
     */
    public $oauth_callback;
    /**
     *
     * @var string
     */
    public $oauth_token;
    /**
     *
     * @var string
     */
    public $oauth_token_secret;

    const AUTHORIZATION_REQUEST_URI_PC = 'https://mixi.jp/connect_authorize.pl';

    const AUTHORIZATION_REQUEST_URI_MOBILE = 'http://m.mixi.jp/connect_authorize.pl';

    const AUTHORIZATION_PLATFORM_PC = 'pc';

    const AUTHORIZATION_PLATFORM_MOBILE = 'mobile';

    /**
     * People API/Groups API/People lookup API
     */
    const SCOPE_R_PROFILE = 'r_profile';
    /**
     * Voice API (情報の取得のみ)
     */
    const SCOPE_R_VOICE = 'r_voice';
    /**
     * Voice API (投稿やコメント)
     */
    const SCOPE_W_VOICE = 'w_voice';
    /**
     * Updates API
     */
    const SCOPE_R_UPDATES = 'r_updates';
    /**
     * PCデスクトップ向け画面が表示される
     */
    const AUTHORIZATION_DISPLAY_PC = 'pc';
    /**
     * スマートフォン向け画面が表示される
     */
    const AUTHORIZATION_DISPLAY_SMARTPHONE = 'smartphone';

    /**
     * アクセストークンの取得URI
     */
    const ACCESSTOKEN_REQUEST_URI = 'https://secure.mixi-platform.com/2/token';

    /**
     *
     * @var array
     */
    public $_baseConfig = array(
        'oauth_consumer_key' => '',
        'oauth_consumer_secret' => '',
        'oauth_token' => '',
        'oauth_callback' => '',
        'refresh_token' => '',
        'cache' => false,
        'refresh_cache' => false,
    );

    /**
     *
     * @param array $config
     */
    public function __construct($config) {

        parent::__construct($config);

        $this->Http = & new HttpSocket();

        $this->reset();
    }

    /**
     * Reset object vars
     */
    public function reset() {
        $this->oauth_consumer_key = $this->config['oauth_consumer_key'];
        $this->oauth_consumer_secret = $this->config['oauth_consumer_secret'];
        $this->oauth_token = $this->config['oauth_token'];
        $this->refresh_token = $this->config['refresh_token'];
        $this->oauth_callback = $this->config['oauth_callback'];
    }

    /**
     * set OAuth Token
     *
     * @param mixed  $token
     * @return ture|false
     */
    public function setToken($token) {

        if (is_array($token) && !empty($token['access_token'])) {

            $this->oauth_token = $token['access_token'];

            return true;
        } else if (!empty($token)) {

            $this->oauth_token = $token;

            return true;
        }

        return false;
    }

    /**
     * Enable Cache
     *
     * @params mixed $config
     */
    public function enableCache($config = true) {
        $this->setConfig(array('cache' => $config));
    }

    /**
     * Next request force update cache
     */
    public function refreshCache() {
        $this->setConfig(array('refresh_cache' => true));
    }

    /**
     * Request API and process responce
     *
     * @param array $params
     * @param bool  $is_process
     * @return mixed
     */
    protected function _request($params, $is_process = true) {

        $this->_setupCache();

        $response = null;
        if ($this->_cacheable($params) && !$this->config['refresh_cache']) {

            // get Cache, only GET method
            $response = Cache::read($this->_getCacheKey($params), $this->configKeyName);
        }

        if (empty($response)) {

            $response = $this->Http->request($params);

            if ($this->_cacheable($params)) {
                // save Cache, only GET method
                $cache = Cache::write($this->_getCacheKey($params), $response, $this->configKeyName);
                $this->config['refresh_cache'] = false;
            }
        }

        if ($is_process) {

            $response = json_decode($response, true);
        }

        // -- error logging
        if ($is_process && !empty($response['error'])) {
            $this->log($response['error'] . "\n" . print_r($params, true), LOG_DEBUG);
        }

        return $response;
    }

    /**
     * get Cache key
     *
     * @param array $params
     * @return stirng
     */
    protected function _getCacheKey($params) {
        return sha1($this->oauth_token . serialize($params));
    }

    /**
     *
     */
    protected function _setupCache() {

        if ($this->config['cache'] && !Cache::isInitialized($this->configKeyName)) {

            if (!is_array($this->config['cache'])) {

                $this->config['cache'] = array(
                    'engine' => 'File',
                    'duration' => '+5 min',
                    'path' => CACHE . 'mixi' . DS,
                    'prefix' => 'cake_' . Inflector::underscore($this->configKeyName) . '_',
                );
            }

            Cache::config($this->configKeyName, $this->config['cache']);
        }
    }

    /**
     * is cacheable
     *
     * @param array $params
     * @return bool
     */
    protected function _cacheable($params) {

        return $this->config['cache'] && strtoupper($params['method']) == 'GET' && !preg_match('!/oauth/!i', $params['uri']['path']);
    }

    /**
     * Build request array
     *
     * @param string $url
     * @param string $method
     * @param array  $body   GET: query string POST: post data
     * @return array
     */
    protected function _buildRequest($url, $method = 'GET', $body = array()) {

        $method = strtoupper($method);

        $uri = parse_url($url);

        // add GET params
        if (!empty($body) && $method == 'GET') {

            if (empty($uri['query'])) {
                $uri['query'] = array();
            }
            $uri['query'] = array_merge($uri['query'], $body);
            $body = array();
        }

        $params = compact('uri', 'method', 'body');


        // -- Set Auth parameter
        if (!empty($this->oauth_token)) {
            // OAuth
            $params['header'] = array('Authorization' => 'OAuth ' . $this->oauth_token);
        }

        return $params;
    }

    /**
     * for DebugKit call
     */
    public function getLog() {

        return array('log' => array(), 'count' => array(), 'time' => array());
    }

    // ====================================================
    // == OAuth Methods
    // ====================================================

    public function getRequestUrl($options = array()) {

        $defaults = array(
            'platform' => self::AUTHORIZATION_PLATFORM_PC,
            'display' => self::AUTHORIZATION_DISPLAY_PC,
            'scope' => array(self::SCOPE_R_PROFILE, self::SCOPE_R_UPDATES, self::SCOPE_R_VOICE, self::SCOPE_W_VOICE),
        );

        $options = am($defaults, $options);

        // リクエスト先URLの取得
        $url = $this->_getAuthorizationRequestUrl($options['platform']);
        unset($options['platform']);

        // TODO: スコープをdatabase.phpで指定可能に
        if (is_array($options['scope'])) {
            $options['scope'] = join(' ', $options['scope']);
        }

        $options['client_id'] = $this->oauth_consumer_key;
        $options['response_type'] = 'code';

        $url .= '?' . $this->_httpBuildQuery($options);

        return $url;
    }

    /**
     * アクセスコード取得用URLの取得（PC/携帯別）
     *
     * @param string $type
     * @return string
     */
    protected function _getAuthorizationRequestUrl($type) {

        return ($type != self::AUTHORIZATION_PLATFORM_MOBILE) ? self::AUTHORIZATION_REQUEST_URI_PC : self::AUTHORIZATION_REQUEST_URI_MOBILE;
    }

    /**
     * rawurlencodeでquery stringを生成する
     *
     * @param array $datas
     */
    protected function _httpBuildQuery($datas = array()) {
        $out = array();
        foreach ($datas as $key => $val) {
            $out[] = $key . '=' . rawurlencode($val);
        }
        return join('&', $out);
    }

    /**
     * アクセストークンの取得
     *
     * @param string $code
     * @return array|false
     */
    public function getAccessToken($code = null) {

        if (empty($code)) {
            return false;
        }

        return $this->_getAccessToken(array('redirect_uri' => $this->oauth_callback, 'code' => $code));
    }

    /**
     * アクセストークンの再発行
     *
     * @param string $refreshToken
     * @return array|false
     */
    public function refreshAccessToken($refreshToken = null) {

        if (empty($refreshToken)) {
            if (empty($this->refresh_token)) {
                return false;
            }
            $refreshToken = $this->refresh_token;
        }

        return $this->_getAccessToken(array('grant_type' => 'refresh_token', 'refresh_token' => $refreshToken));
    }

    /**
     * アクセストークンの取得(新規、更新両用)
     *
     * @param array $options
     * @return array|false
     */
    public function _getAccessToken($options = array()) {

        $defaults = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->oauth_consumer_key,
            'client_secret' => $this->oauth_consumer_secret,
        );

        $options = am($defaults, $options);

        $params = $this->_buildRequest(self::ACCESSTOKEN_REQUEST_URI, 'POST', $options);

        $result = $this->_request($params);

        if (!empty($result['expires_in'])) {
            $result['expires'] = time() + $result['expires_in'];
        }

        return $result;
    }

    // == Voice API Read Methods

    /**
     * あるユーザのつぶやき一覧の取得
     * 
     * @param array $options
     *      user_id:  取得したいユーザのID、もしくは認可ユーザ自身を示す”@me”
     *      since_id: このパラメータ値として、あるつぶやきのIDを指定することで、
     *                そのつぶやきよりも新しく投稿されたつぶやきの一覧に限定することができます（since_idは省略可）
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function getVoiceStatusesUserTimeline($options = array()) {

        $params = array('user_id' => '@me');
        if (is_string($options) && !empty($options)) {
            $params['user_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $userId = rawurlencode($params['user_id']);
        unset($params['user_id']);

        $url = sprintf('http://api.mixi-platform.com/2/voice/statuses/%s/user_timeline', $userId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * 友人のつぶやき一覧の取得
     *
     * @param array $options
     *      group_id:  グループのID、省略可
     *      since_id: このパラメータ値として、あるつぶやきのIDを指定することで、
     *                そのつぶやきよりも新しく投稿されたつぶやきの一覧に限定することができます（since_idは省略可）
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function getVoiceStatusesFriendsTimeline($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['group_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $groupId = '';
        if (!empty($params['group_id'])) {
            $groupId = rawurlencode($params['group_id']);
            unset($params['group_id']);
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/statuses/friends_timeline/%s', $groupId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * ある特定のつぶやき情報の取得
     *
     * @param array $options
     *      post_id:  取得したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function getVoiceStatusesShow($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/statuses/show/%s', $postId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * あるつぶやきのコメント一覧取得
     *
     * @param array $options
     *      post_id:  コメント一覧を取得したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function getVoiceRepliesShow($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/replies/show/%s', $postId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * あるつぶやきのイイネ！一覧取得
     *
     * @param array $options
     *      post_id:  コメント一覧を取得したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function getVoiceFavoritesShow($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/favorites/show/%s', $postId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    // == Voice API Update Methods

    /**
     * つぶやきの投稿
     *
     * @param array $options
     *      status:  取得したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function postVoiceStatuses($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['status'] = $options;
        } else {
            $params = am($params, $options);
        }

        if (empty($params['status'])) {
            return false;
        }

        $url = 'http://api.mixi-platform.com/2/voice/statuses';
        $method = 'POST';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * あるつぶやきの削除
     *
     * @param array $options
     *      post_id:  削除したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function deleteVoiceStatuses($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/statuses/%s', $postId);
        $method = 'DELETE';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * コメントの投稿
     *
     * @param array $options
     *      post_id:  コメント一覧を取得したいつぶやきを特定するためのID
     *      text:     コメントの本文
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function postVoiceReplies($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/replies/%s', $postId);
        $method = 'POST';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * あるコメントの削除
     *
     * @param array $options
     *      post_id:  コメント一覧を取得したいつぶやきを特定するためのID
     *      comment_id:     コメントの本文
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function deleteVoiceReplies($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        $commentId = '';
        if (!empty($params['comment_id'])) {
            $commentId = $params['comment_id'];
            unset($params['comment_id']);
        }

        if (empty($postId) || empty($commentId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/replies/%s/%s', $postId, $commentId);
        $method = 'DELETE';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * イイネ！の投稿
     *
     * @param array $options
     *      post_id:  イイネ！を投稿したいつぶやきを特定するためのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function postVoiceFavorites($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        if (empty($postId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/favorites/%s', $postId);
        $method = 'POST';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * あるイイネ！の削除
     *
     * @param array $options
     *      post_id:  イイネ！を投稿したいつぶやきを特定するためのID
     *      user_id:  削除したいイイネ！の投稿者のユーザのID
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/voice-api
     */
    public function deleteVoiceFavorites($options = array()) {

        $params = array();
        if (is_string($options)) {
            $params['post_id'] = $options;
        } else {
            $params = am($params, $options);
        }

        $postId = '';
        if (!empty($params['post_id'])) {
            $postId = $params['post_id'];
            unset($params['post_id']);
        }

        $userId = '';
        if (!empty($params['user_id'])) {
            $userId = $params['user_id'];
            unset($params['user_id']);
        }

        if (empty($postId) || empty($userId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/voice/favorites/%s/%s', $postId, $userId);
        $method = 'POST';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    // == Pepole API Methods

    /**
     * 友人一覧の取得
     *
     * @param array $options
     *      user_id:  取得したいユーザのID、または”@me”
     *      group_id: 取得したいグループのID、または”@self”、”@friends”
     * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/people-api
     */
    function getPeople($options = array()) {

        $defaults = array(
            'user_id' => '@me',
            'group_id' => '@self',
        );

        $params = am($defaults, $options);

        $userId = '';
        if (!empty($params['user_id'])) {
            $userId = $params['user_id'];
            unset($params['user_id']);
        }

        $groupId = '';
        if (!empty($params['group_id'])) {
            $groupId = $params['group_id'];
            unset($params['group_id']);
        }

        if (empty($userId) || empty($groupId)) {
            return false;
        }

        $url = sprintf('http://api.mixi-platform.com/2/people/%s/%s', $userId, $groupId);
        $method = 'GET';

        return $this->_request($this->_buildRequest($url, $method, $params));
    }

    /**
     * 自分自身のプロフィール取得
     */
    function getMyProfile() {

        $result = $this->getPeople(array(
                    'user_id' => '@me',
                    'group_id' => '@self',
                ));

        if (!empty($result['entry'])) {
            return $result['entry'];
        }

        return $result;
    }

}
