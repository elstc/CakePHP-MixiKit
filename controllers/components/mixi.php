<?php

App::import('Core', 'ConnectionManager');
App::import('Datasource', 'MixiKit.MixiGraphApiSource');

/**
 * MixiKit Mixi Component
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
 * @package    mixi_kit
 * @subpackage mixi_kit.controllers.components
 * @since      MixiKit 1.0
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 */
class MixiComponent extends Object {

    public $name = 'Mixi';
    public $components = array('Cookie');
    public $settings = array(
        'datasource' => 'mixi',
        'fields' => array(
            'oauth_token' => 'oauth_token',
            'oauth_refresh_token' => 'oauth_refresh_token'),
    );
    /**
     *
     * @var AppController
     */
    public $controller;
    /**
     *
     * @var MixiGraphApiSource
     */
    public $DataSource;
    /**
     *
     * @var CookieComponent
     */
    public $Cookie;

    /**
     * default: 3min
     *
     * @var int
     */
    CONST OAUTH_URL_COOKIE_EXPIRE = 180;

    /**
     *
     * @param AppController $controller
     * @param array         $settings
     */
    public function initialize($controller, $settings = array()) {

        $this->settings = Set::merge($this->settings, $settings);

        $this->controller = $controller;

        $this->getMixiGraphApiSource();

        $this->Cookie->path = Router::url('/');
    }

    /**
     * get DataSource Object
     *
     * @return MixiGraphApiSource
     */
    public function getMixiGraphApiSource() {

        $ds = ConnectionManager::getDataSource($this->settings['datasource']);

        if (get_class($ds) == 'MixiGraphApiSource' || is_subclass_of($ds, 'MixiGraphApiSource')) {

            $this->DataSource = $ds;
        }

        return $this->DataSource;
    }

    /**
     * set DataSource Object
     *
     * @param string $datasource
     */
    public function setMixiGraphApiSource($datasource) {

        if (empty($datasource)
                || (!in_array($datasource, array_keys(get_class_vars('DATABASE_CONFIG')))
                && !in_array($datasource, ConnectionManager::sourceList()))) {

            return;
        }

        $this->settings['datasource'] = $datasource;

        $this->getMixiGraphApiSource();
    }

    /**
     *
     * @param AppController $controller
     */
    public function startup($controller) {

        $this->controller = $controller;
    }

    /**
     * make OAuth Authorize URL
     *
     * @param array $options
     * @return string authorize_url
     */
    public function getAuthorizeUrl($options = array()) {
        return $this->DataSource->getRequestUrl($options);
    }

    /**
     * get OAuth Access Token
     *
     * @return array|false
     */
    public function getAccessToken() {

        if (empty($this->controller->params['url']['code'])) {

            return false;
        }

        $accessCode = $this->controller->params['url']['code'];

        $token = $this->DataSource->getAccessToken($accessCode);

        return $token;
    }

    /**
     * set OAuth Access Token
     *
     * @param mixed $token
     * @return true|false
     */
    public function setToken($token) {

        if (is_array($token) && !empty($token[$this->settings['fields']['oauth_token']])) {

            $token = $token[$this->settings['fields']['oauth_token']];
        }

        return $this->DataSource->setToken($token);
    }

    /**
     * set OAuth Access Token by Authorized User
     *
     * @param  array $user
     */
    public function setTokenByUser($user = null) {

        if (empty($user) && !empty($this->controller->Auth) && is_object($this->controller->Auth)) {

            $user = $this->controller->Auth->user();
        }

        return $this->setToken($user['User']);
    }

    /**
     * call MixiGraphApiSource methods
     *
     * @param string $name
     * @param array  $arg
     */
    public function __call($name, $arg) {

        if (in_array($name, get_class_methods('MixiGraphApiSource'))) {

            return call_user_func_array(array($this->DataSource, $name), $arg);
        }
    }

    /**
     *
     * @return string
     */
    protected function _getAuthorizeUrlCookieName() {
        return $this->DataSource->configKeyName . '_authorize_url';
    }

    /**
     *
     * @return string
     */
    protected function _getAuthenticateUrlCookieName() {
        return $this->DataSource->configKeyName . '_authenticate_url';
    }

}