<?php

/**
 * MixiKit UsersController
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
