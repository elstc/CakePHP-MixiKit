<?php
/**
 * MixiKit users/login view
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
 * @filesource
 * @version    1.0
 * @author     nojimage <nojima at elasticconsultants.com>
 * @copyright  2010, ELASTIC Consultants Inc.
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.views.users
 * @since      MixiKit 1.0
 * @license    GNU GENERAL PUBLIC LICENSE Version 3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * */
$this->set('title_for_layout', __d('mixi_kit', 'Login', true));
?>
<?php if (!$this->Session->check('Auth.User')) : /* 未ログインの場合 */ ?>
<?php echo $this->MixiGoodies->oauthLink($linkOptions); ?>
<?php else: ?>
        <div id="logout-wrap">
            <p><?php echo $this->Html->link(__d('mixi_kit', 'Logout', true), '/users/logout') ?></p>
        </div>
<?php endif; ?>
