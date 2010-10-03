<?php

/**
 * MixiKit Users Controller
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
class UsersController extends MixiKitAppController {

    public $name = 'Users';
    public $uses = array();
    public $components = array('Auth');
    public $helpers = array('Html', 'Form', 'Js', 'MixiKit.MixiGoodies');

    /**
     * (non-PHPdoc)
     * @see cake/libs/controller/Controller#beforeFilter()
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login', 'logout');
    }

    public function login() {
        $linkOptions = array();

        if (!empty($this->params['named']['datasource'])) {
            $linkOptions['datasource'] = $this->params['named']['datasource'];
        }

        $this->set('linkOptions', $linkOptions);
    }

    public function logout() {
        $this->Session->destroy();
        $this->Session->setFlash(__d('mixi_kit', 'Signed out', true));
        $this->redirect($this->Auth->logoutRedirect);
    }

}
