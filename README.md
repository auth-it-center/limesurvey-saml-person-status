# limesurvey-person-status
Limesurvey plugin that allows only users with specified status to participate in surveys.

## Requirements
* LimeSurvey 3.XX
* [SAML-Plugin](https://github.com/auth-it-center/Limesurvey-SAML-Authentication)

## Installation instructions
1. Copy **SAMLPersonStatus** folder with its content at **limesurvey/plugins** folder
2. Go to **Admin > Configuration > Plugin Manager** or **https:/example.com/index.php/admin/pluginmanager/sa/index**
and **Enable** the plugin

## How to enable plugin for specific survey
1. Go to **Surveys > (Select desired survey) > Simple Plugins** or
**https:/example.com/index.php/admin/survey/sa/rendersidemenulink/surveyid/{survey_id}/subaction/plugins**
2. Open **Settings for plugin AuthSurvey** accordion
3. Click **Enabled** checkbox
4. Open **Settings for plugin SAMLPersonStatus** accordion
5. Click **Enabled** checkbox

## Configuration options

### Global
* **SAML Person Status Attribute** SAML attribute that indicates person status

### Plugin
* **Enabled** If checked then the plugin is enabled for the selected survey
* **Allowed Status** Option that specifies what person status should participant meet in order to participate at the survey

## Images
![Global Plugin settings](images/global_settings.png)
