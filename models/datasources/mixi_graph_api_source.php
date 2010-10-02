<?php

App::import('Core', array('Xml', 'Cache'));
App::import('vendor', 'MixiKit.HttpSocketOauth', array('file' => 'http_socket_oauth' . DS . 'http_socket_oauth.php'));

/**
 * Mixi API Datasource
 *
 * for CakePHP 1.3+
 * PHP version 5.2+
 *
 * Copyright 2010, ELASTIC Consultants Inc. (http://elasticconsultants.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @version    1.0
 * @author     nojimage <nojima at elasticconsultants.com>
 * @copyright  2010, ELASTIC Consultants Inc.
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://elasticconsultants.com
 * @package    twitter_kit
 * @subpackage twitter_kit.models.datasources
 * @since      TwitterKit 1.0
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 *
 * @see http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/api_common_spec
 *
 * This Class use HttpSocketOAuth:
 *
 *   Neil Crookes » OAuth extension to CakePHP HttpSocket
 *     http://www.neilcrookes.com/2010/04/12/cakephp-oauth-extension-to-httpsocket/
 *     http://github.com/neilcrookes/http_socket_oauth
 *
 * Thank you.
 */
class MixiGraphApiSource extends DataSource {

    public $description = 'Mixi Graph API';
    /**
     *
     * @var HttpSocketOauth
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

        $this->Http = & new HttpSocketOauth();

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

        if (is_array($token) && !empty($token['oauth_token'])) {

            $this->oauth_token = $token['oauth_token'];

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
            return false;
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
        /*
          // -- Set Auth parameter
          if (!empty($this->oauth_consumer_key) && !empty($this->oauth_consumer_secret)) {

          // OAuth
          $params['auth']['method'] = 'OAuth';
          $params['auth']['oauth_consumer_key'] = $this->oauth_consumer_key;
          $params['auth']['oauth_consumer_secret'] = $this->oauth_consumer_secret;

          if (!empty($this->oauth_token) && !empty($this->oauth_token_secret)) {

          $params['auth']['oauth_token'] = $this->oauth_token;
          $params['auth']['oauth_token_secret'] = $this->oauth_token_secret;
          }
          }
         */
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

}
