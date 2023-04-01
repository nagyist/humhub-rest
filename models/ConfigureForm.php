<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\rest\models;

use Yii;
use yii\base\Model;
use humhub\modules\rest\Module;

class ConfigureForm extends Model
{
    public $enableJwtAuth;

    public $enableBasicAuth;

    public $enableBearerAuth;

    public $enableQueryParamAuth;

    public $apiModules;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enableJwtAuth', 'enableBasicAuth', 'enableBearerAuth', 'enableQueryParamAuth'], 'boolean'],
            [['apiModules'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enableJwtAuth' => Yii::t('RestModule.base','Allow JWT Authentication'),
            'enableBasicAuth' => Yii::t('RestModule.base','Allow HTTP Basic Authentication'),
            'enableBearerAuth' => Yii::t('RestModule.base','Allow Bearer Authentication'),
            'enableQueryParamAuth' => Yii::t('RestModule.base','Allow Query Param Bearer Authentication'),
            'apiModules' => Yii::t('RestModule.base', 'Active additional REST API endpoints from the modules'),
        ];
    }

    public function loadSettings()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('rest');

        $settings = $module->settings;

        $this->enableJwtAuth = (boolean)$settings->get('enableJwtAuth');
        $this->enableBasicAuth = (boolean)$settings->get('enableBasicAuth');
        $this->enableBearerAuth = (boolean)$settings->get('enableBearerAuth');
        $this->enableQueryParamAuth = (boolean)$settings->get('enableQueryParamAuth');

        foreach ($module->getModulesWithRestApi() as $apiModule) {
            if ($module->isActiveModule($apiModule->id)) {
                $this->apiModules[] = $apiModule->id;
            }
        }

        return true;
    }

    public function saveSettings()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('rest');

        if (!$this->enableBearerAuth ) {
            $this->enableQueryParamAuth = false;
        }

        $module->settings->set('enableJwtAuth', (boolean)$this->enableJwtAuth);
        $module->settings->set('enableBasicAuth', (boolean)$this->enableBasicAuth);
        $module->settings->set('enableBearerAuth', (boolean)$this->enableBearerAuth);
        $module->settings->set('enableQueryParamAuth', (boolean)$this->enableQueryParamAuth);

        $apiModules = [];
        foreach ($module->getModulesWithRestApi() as $apiModule) {
            $apiModules[$apiModule->id] = is_array($this->apiModules) && in_array($apiModule->id, $this->apiModules);
        }
        $module->settings->setSerialized('apiModules', $apiModules);

        return true;
    }

    public static function getInstance()
    {
        $config = new static;
        $config->loadSettings();

        return $config;
    }

    /**
     * Get options of modules with REST API endpoints
     *
     * @return array
     */
    public function getApiModuleOptions()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('rest');

        $options = [];
        foreach ($module->getModulesWithRestApi() as $apiModule) {
            $options[$apiModule->id] = $apiModule->getName();
        }

        return $options;
    }

}
