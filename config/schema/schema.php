<?php

/**
 * MixiKit Schema
 *
 * PHP version 5
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
 * @subpackage mixi_kit.config.schema
 * @since      MixiKit 1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
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
