<?php

/**
 * MixiKit OauthController
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
 * @subpackage mixi_kit.controller
 * @since      MixiKit 1.0
 * @license    GNU GENERAL PUBLIC LICENSE Version 3 (http://www.gnu.org/licenses/gpl-3.0.html)
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
