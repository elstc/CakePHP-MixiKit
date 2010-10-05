<?php

App::import('Helper', 'Form');

/**
 * MixiKit MixiGoodies Helper
 *
 * Copyright 2010, ELASTIC Consultants Inc. http://elasticconsultants.com/
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
 * @copyright  2010 ELASTIC Consultants Inc.
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.views.helpers
 * @since      File available since Release 1.0
 * @license    GNU GENERAL PUBLIC LICENSE Version 3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 */
class MixiGoodiesHelper extends AppHelper {

    public $helpers = array('Html', 'Form', 'Js');
    /**
     *
     * @var HtmlHelper
     */
    public $Html;
    /**
     *
     * @var FormHelper
     */
    public $Form;
    /**
     *
     * @var JsHelper
     */
    public $Js;

    /**
     * create tweet box
     *
     * @param $fieldName
     * @param $options
     *      type: element type (default: textarea)
     *      maxLength:   text max length (default: 140)
     *      counterText: length message
     *      submit: submit button message. if set to false, not create.
     *      jqueryCharCount: path to charCount.js (jquery plugin)
     *      other keys set to input element options.
     */
    public function tweet($fieldName, $options = array()) {

        $this->setEntity($fieldName);
        $domId = !empty($options['id']) ? $options['id'] : $this->domId($fieldName);

        $default = array(
            'type' => 'textarea',
            'maxlength' => 140,
            'jqueryCharCount' => '/mixi_kit/js/charCount.js',
            'counterText' => __d('mixi_kit', 'Characters left: ', true),
            'submit' => __d('mixi_kit', 'Tweet', true),
        );

        $options = am($default, $options);
        $inputOptions = $options;
        unset($inputOptions['jqueryCharCount']);
        unset($inputOptions['counterText']);
        unset($inputOptions['submit']);

        $out = $this->Html->script($options['jqueryCharCount']);

        $out .= $this->Form->input($fieldName, $inputOptions);

        $out .= $this->Js->buffer("
            $('#{$domId}').charCount({
                limit: {$options['maxlength']},
                counterText: '{$options['counterText']}',
                exceeded: function(element) {
                    $('#{$domId}Submit').attr('disabled', true);
                },
                allowed: function(element) {
                    $('#{$domId}Submit').removeAttr('disabled');
                }
            });
        ");

        if ($options['submit']) {
            $out .= $this->Form->submit($options['submit'], array('id' => $domId . 'Submit'));
        }

        return $this->output($out);
    }

    /**
     * create OAuth Link
     *
     * @param $options
     *  loading:      loading message
     *  login:        login link text
     *  datasource:   datasource name (default: twitter)
     *  authenticate: use authenticate link (default: false)
     */
    public function oauthLink($options = array()) {

        $default = array(
            'title' => __d('mixi_kit', 'Login Mixi', true),
            'datasource' => 'mixi',
            'loginElementId' => 'mixi-login-wrap',
            'platform' => MixiGraphApiSource::AUTHORIZATION_PLATFORM_PC,
            'display' => MixiGraphApiSource::AUTHORIZATION_DISPLAY_PC,
            'scope' => array(
                MixiGraphApiSource::SCOPE_R_PROFILE,
                MixiGraphApiSource::SCOPE_R_UPDATES,
                MixiGraphApiSource::SCOPE_R_VOICE,
                MixiGraphApiSource::SCOPE_W_VOICE),
        );

        $options = am($default, $options);

        $ds = ConnectionManager::getDataSource($options['datasource']);
        /* @var $ds MixiGraphApiSource */
        $url = $ds->getRequestUrl(array_intersect_key($options, array('platform', 'display', 'scope')));

        $out = sprintf('<span id="%s">%s</span>',
                        $options['loginElementId'],
                        $this->Html->link($options['title'], $url)
        );

        return $this->output($out);
    }

    /**
     * linkify text
     *
     * @param string $value
     * @param array  $options
     *    username: linkify username. eg. @username
     *    hashtag : linkify hashtag. eg. #hashtag
     *    url     : linkify url. eg. http://example.com/
     * @return string
     */
    public function linkify($value, $options = array()) {

        $default = array(
            'url' => true,
            'username' => true,
            'hashtag' => true,
        );

        $validChars = '(?:[' . preg_quote('!"$&\'()*+,-.@_:;=~', '!') . '\/0-9a-z]|(?:%[0-9a-f]{2}))';
        $_urlMatch = 'https?://(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,6})' .
                '(?::[1-9][0-9]{0,4})?' . '(?:\/' . $validChars . '*)?' . '(?:\?' . $validChars . '*)?' . '(?:#' . $validChars . '*)?';

        $replaces = array(
            'url' => array('!(^|[\W])(' . $_urlMatch . ')([\W]|$)!iu' => '$1<a href="$2">$2</a>$3'),
            'username' => array('!(^|[^\w/?&;])@(\w+)!iu' => '$1<a href="http://twitter.com/$2">@$2</a>$3'),
            'hashtag' => array('!(^|[^\w/?&;])#(\w+)!iu' => '$1<a href="http://search.twitter.com/search?q=%23$2">#$2</a>$3'),
        );

        $options = am($default, $options);

        foreach ($replaces as $key => $_replace) {
            if ($options[$key]) {
                $value = preg_replace(array_keys($replaces[$key]), array_values($replaces[$key]), $value);
            }
        }

        return $value;
    }

}