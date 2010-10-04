<?php

/**
 * MixiKit Oauth Controller
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
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.controller
 * @since      MixiKit 1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * */
class OauthController extends AppController {

    public $uses = array();
    public $components = array('Auth', 'MixiKit.Mixi');
    /**
     *
     * @var MixiComponent
     */
    public $Mixi;
    /**
     *
     * @var AuthComponent
     */
    public $Auth;

    /**
     * (non-PHPdoc)
     * @see cake/libs/controller/Controller#beforeFilter()
     */
    public function beforeFilter() {
        parent::beforeFilter();

        if (!empty($this->Auth) && is_object($this->Auth)) {

            $this->Auth->allow('callback');
        }
    }

    /**
     * OAuth callback
     */
    public function callback($datasource = null) {
        $this->Mixi->setMixiSource($datasource);

        // 正当な返り値かチェック
        if (empty($this->params['url']['code'])) {
            $this->flash(__d('mixi_kit', 'Authorization failure.', true), '/', 5);
            return;
        }

        // $tokenを取得
        $token = $this->Mixi->getAccessToken();

        if (isset($token['error'])) {

            $this->flash(__d('mixi_kit', 'Authorization Error: ', true) . $token['error'], '/', 5);
            return;
        }

        $model = null;
        if (ClassRegistry::isKeySet('MixiUser')) {
            /* @var $model TwitterUser */
            $model = ClassRegistry::init('MixiUser');
        } else {
            /* @var $model MixiKitUser */
            $model = ClassRegistry::init('MixiKit.MixiKitUser');
        }

        // 保存データの作成
        $data = $model->createSaveDataByToken($token);

        if (!$model->save($data)) {
            $this->flash(__d('mixi_kit', 'The user could not be saved', true), array('plugin' => 'mixi_kit', 'controller' => 'users', 'action' => 'login'), 5);
            return;
        }

        $this->Auth->login($data);

        // Redirect
        if (ini_get('session.referer_check') && env('HTTP_REFERER')) {
            $this->flash(sprintf(__d('mixi_kit', 'Redirect to %s', true), Router::url($this->Auth->redirect(), true) . ini_get('session.referer_check')), $this->Auth->redirect(), 0);
            return;
        }

        $this->redirect($this->Auth->redirect());
    }

}
