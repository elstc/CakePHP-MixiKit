<?php
/**
 * MixiKit TwitterUser Model
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
 * @subpackage mixi_kit.models
 * @since      MixiKit 1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/
class MixiKitUser extends MixiKitAppModel {

    public $name = 'MixiKitUser';

    public $alias = 'MixiUser';

    public $useTable = 'mixi_users';

    public $displayField = 'username';

    public $validate = array(
		'username' => array(
			'notempty' => array('rule' => array('notempty'))));

    public $actsAs = array(
        'MixiKit.Mixi',
    );

}
