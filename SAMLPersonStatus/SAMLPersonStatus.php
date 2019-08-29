<?php

/**
 * LimeSurvey SAMLPersonStatus
 *
 * This plugin allows only users with specified status to participate in surveys
 *
 * Author: Panagiotis Karatakis <karatakis@it.auth.gr>
 * Licence: GPL3
 *
 * Sources:
 * https://manual.limesurvey.org/Plugins_-_advanced
 * https://manual.limesurvey.org/Plugin_events
 * https://medium.com/@evently/creating-limesurvey-plugins-adcdf8d7e334
 */

class SAMLPersonStatus extends Limesurvey\PluginManager\PluginBase
{
    protected $storage = 'DbStorage';
    static protected $description = 'This plugin allows only users with specified status to participate in surveys';
    static protected $name = 'SAMLPersonStatus';

    protected $settings = [
        'person_status_mapping' => [
            'type' => 'string',
            'label' => 'SAML Person Status Attribute',
            'default' => 'authPersonStatus'
        ]
    ];

    protected $allowed_statuses = ['whatever', 'active', 'inactive', 'missing'];

    public function init()
    {
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveyPage');
        $this->subscribe('afterSurveyComplete');
    }

    public function beforeSurveySettings()
    {
        $event = $this->event;

        $event->set('surveysettings.' . $this->id, [
            'name' => get_class($this),
            'settings' => [
                'person_status_plugin_enabled' => [
                    'type' => 'checkbox',
                    'label' => 'Enabled',
                    'help' => 'Enable the plugin for this survey',
                    'default' => false,
                    'current' => $this->get('person_status_plugin_enabled', 'Survey', $event->get('survey'), false),
                ],
                'allowed_status' => [
                    'label' => 'Allowed Status',
                    'help' => 'Permit the use of the survey only on the desired person status',
                    'type' => 'select',
                    'options' => [
                        'whatever' => 'Whatever',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'missing' => 'Missing',
                    ],
                    'current' => $this->get('allowed_status', 'Survey', $event->get('survey'), 'whatever'),
                ]
            ]
        ]);
    }

    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value)
        {
            $default = $event->get($name, null, null, isset($this->settings[$name]['default']));
            $this->set($name, $value, 'Survey', $event->get('survey'), $default);
        }
    }

    public function getPersonStatus()
    {
        $AuthSAML = $this->pluginManager->loadPlugin('AuthSAML');

        $ssp = $AuthSAML->get_saml_instance();

        $ssp->requireAuth();

        $attributes = $ssp->getAttributes();

        $personStatusField = $this->get('person_status_mapping', null, null, $this->settings['person_status_mapping']['default']);

        if (isset($attributes[$personStatusField][0])) {
            return $attributes[$personStatusField][0] ? 'active' : 'inactive';
        }
        return 'missing';
    }

    public function beforeSurveyPage()
    {
        $plugin_enabled = $this->get('person_status_plugin_enabled', 'Survey', $this->event->get('surveyId'));
        if ($plugin_enabled) {
            $allowed_person_status = $this->get('allowed_status', 'Survey', $this->event->get('surveyId'));

            $person_status = $this->getPersonStatus();

            if (!$this->checkPersonStatus($person_status, $allowed_person_status)) {
                throw new CHttpException(403, gT("We are sorry but you do not meet the required person status to participate in this survey."));
            }
        }
    }

    public function checkPersonStatus($person_status, $status)
    {
        if (!in_array($status, $this->allowed_statuses)) {
            throw new CHttpException(403, gT("Invalid parameter for person status code $status"));
        }

        if ($status === 'whatever') {
            return true;
        }
        return $status === $person_status;
    }
}