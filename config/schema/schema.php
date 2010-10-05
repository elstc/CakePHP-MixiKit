<?php

/**
 * MixiKit Schema
 *
 * PHP version 5
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
 * @subpackage mixi_kit.config.schema
 * @since      MixiKit 1.0
 * @license    GNU GENERAL PUBLIC LICENSE Version 3 (http://www.gnu.org/licenses/gpl-3.0.html)
 *
 * cake schema create MixiKit.MixiKit
 * */
class MixiKitSchema extends CakeSchema {

    var $name = 'MixiKit';

    function before($event = array()) {
        return true;
    }

    function after($event = array()) {

    }

    var $mixi_users = array(
        'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'key' => 'primary'),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'username' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'unique'),
        'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 40),
        'oauth_access_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128),
        'oauth_refresh_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128),
        'oauth_expires' => array('type' => 'integer', 'null' => true, 'default' => NULL),
        'oauth_scope' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 256),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'U_username' => array('column' => 'username', 'unique' => 1),
            'IX_expires' => array('column' => 'expires')
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

}
