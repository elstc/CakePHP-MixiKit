<?php

App::import('Core', 'ConnectionManager');
App::import('Datasource', 'MixiKit.mixiSource');

/**
 * MixiKit mixi Behavior
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
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://elasticconsultants.com
 * @package    mixi_kit
 * @subpackage mixi_kit.models.behaviors
 * @since      MixiKit 1.0
 * @modifiedby nojimage <nojima at elasticconsultants.com>
 */
class MixiBehavior extends ModelBehavior {

    /**
     *
     * @var MixiGraphApiSource
     */
    public $DataSource;
    public $default = array(
        'datasource' => 'mixi',
        'fields' => array(
            'oauth_access_token' => 'oauth_access_token',
            'oauth_refresh_token' => 'oauth_refresh_token',
            'oauth_scope' => 'oauth_scope',
            'oauth_expires' => 'oauth_expires',
        ),
    );

    /**
     *
     * @param AppModel $model
     * @param array    $config
     */
    public function setup($model, $config = array()) {

        $this->settings[$model->alias] = Set::merge($this->default, $config);

        $this->getMixiSource($model);
    }

    /**
     * get DataSource Object
     *
     * @param AppModel $model
     * @return mixiSource
     */
    public function getMixiSource($model) {

        $ds = ConnectionManager::getDataSource($this->settings[$model->alias]['datasource']);

        if (get_class($ds) == 'MixiGraphApiSource' || is_subclass_of($ds, 'MixiGraphApiSource')) {

            $this->DataSource = $ds;
        }

        return $this->DataSource;
    }

    /**
     * set DataSource Object
     *
     * @param AppModel $model
     * @param string $datasource
     */
    public function setMixiSource($model, $datasource) {

        if (empty($datasource)
                || (!in_array($datasource, array_keys(get_class_vars('DATABASE_CONFIG'))) && !in_array($datasource, ConnectionManager::sourceList()))) {

            return;
        }

        $this->settings[$model->alias]['datasource'] = $datasource;

        $this->getMixiSource($model);
    }

    /**
     * get OAuth Access Token
     *
     * @param AppModel $model
     * @param string   $accessCode
     * @return array|false
     */
    public function mixiAccessToken($model, $accessCode = null) {

        if (empty($accessCode)) {

            return false;
        }

        $token = $this->DataSource->getAccessToken($accessCode);

        return $token;
    }

    /**
     * set OAuth Access Token
     *
     * @param AppModel $model
     * @param mixed $token
     * @return true|false
     */
    public function mixiSetToken($model, $token = null) {

        if (empty($token)) {

            // -- get from Model->data
            if (empty($model->data[$model->alias])) {

                return false;
            }

            $data = $model->data[$model->alias];

            if (empty($data[$this->settings[$model->alias]['fields']['oauth_token']])) {

                return false;
            }

            $token = $data[$this->settings[$model->alias]['fields']['oauth_token']];
        } else if (is_array($token)) {

            if (!empty($token[$model->alias])) {

                $token = $token[$model->alias];
            }

            if (!empty($token[$this->settings[$model->alias]['fields']['oauth_token']])) {

                // -- get from array
                $token = $token[$this->settings[$model->alias]['fields']['oauth_token']];
            }
        }

        return $this->DataSource->setToken($token, $secret);
    }

    /**
     * set OAuth Access Token By Id
     *
     * @param AppModel $model
     * @param mixed    $id
     * @return true|false
     */
    public function mixiSetTokenById($model, $id = null) {

        if (is_null($id)) {

            $id = $model->id;
        }

        $data = $model->read($this->settings[$model->alias]['fields'], $id);

        if (empty($data[$model->alias])) {

            return false;
        }

        return $this->mixiSetToken($model, $data[$model->alias]);
    }

    /**
     * set OAuth Access Token By Id
     *
     * @param AppModel $model
     * @param mixed    $id
     * @return true|false
     */
    public function mixiSaveToken($model, $id = null) {

        if (is_null($id)) {

            $id = $model->id;
        }

        $data = array($model->alias => array());
        $data[$model->alias][$this->settings[$model->alias]['fields']['oauth_token']] = $this->DataSource->oauth_token;

        return $model->save($data);
    }

    /**
     * create save data
     *
     * @param  AppModel $model
     * @param  array    $token
     * @param  array    $userProfile
     * @return array
     */
    public function createSaveDataByToken($model, $token, $userProfile = array()) {

        if (empty($userProfile)) {
            $this->DataSource->setToken($token['access_token']);
            $userProfile = $this->DataSource->getMyProfile();
        }

        $data = array(
            $model->alias => array(
                'id' => $userProfile['id'],
                'username' => $userProfile['displayName'],
                'password' => Security::hash($token['access_token']),
                $this->settings[$model->alias]['fields']['oauth_access_token'] => $token['access_token'],
                $this->settings[$model->alias]['fields']['oauth_refresh_token'] => $token['refresh_token'],
                $this->settings[$model->alias]['fields']['oauth_expires'] => $token['expires'],
                $this->settings[$model->alias]['fields']['oauth_scope'] => $token['scope'],
            ),
        );

        return $data;
    }

}