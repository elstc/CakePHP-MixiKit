<?php
/**
 * MixiKit users/login view
 *
 * for CakePHP 1.3+
 * PHP version 5.2+
 *
 * Copyright 2010, ELASTIC Consultants Inc. (http://elasticconsultants.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @version    1.0
 * @author     nojimage <nojima at elasticconsultants.com>
 * @copyright  2010, ELASTIC Consultants Inc.
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.views.users
 * @since      MixiKit 1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/
$this->set('title_for_layout', __d('mixi_kit', 'Login', true));
?>
<?php if (!$this->Session->check('Auth.User')) : /* 未ログインの場合 */ ?>
<?php echo $this->MixiGoodies->oauthLink($linkOptions); ?>
<?php else: ?>
<div id="logout-wrap">
<p><?php echo $this->Html->link(__d('mixi_kit', 'Logout', true), '/users/logout')?></p>
</div>
<?php endif ; ?>
<?php echo $this->MixiGoodies->Js->writeBuffer(); ?>
