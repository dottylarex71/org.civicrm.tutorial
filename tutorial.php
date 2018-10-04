<?php

require_once 'tutorial.civix.php';
use CRM_Tutorial_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tutorial_civicrm_config(&$config) {
  _tutorial_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tutorial_civicrm_xmlMenu(&$files) {
  _tutorial_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tutorial_civicrm_install() {
  _tutorial_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function tutorial_civicrm_postInstall() {
  _tutorial_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tutorial_civicrm_uninstall() {
  _tutorial_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tutorial_civicrm_enable() {
  _tutorial_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tutorial_civicrm_disable() {
  _tutorial_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tutorial_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _tutorial_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tutorial_civicrm_managed(&$entities) {
  _tutorial_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tutorial_civicrm_caseTypes(&$caseTypes) {
  _tutorial_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function tutorial_civicrm_angularModules(&$angularModules) {
  _tutorial_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tutorial_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _tutorial_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function tutorial_civicrm_entityTypes(&$entityTypes) {
  _tutorial_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_buildForm().
 */
function tutorial_civicrm_buildForm($formName, &$form) {
  _civitutorial_load(implode('/', $form->urlPath));
}

/**
 * Implements hook_civicrm_pageRun().
 */
function tutorial_civicrm_pageRun(&$page) {
  _civitutorial_load(implode('/', $page->urlPath));
}

/**
 * @return array|mixed
 */
function _civitutorial_get_files() {
  $files = Civi::cache('community_messages')->get('tutorials');
  if ($files === NULL) {
    $files = $paths = [];
    $directories = array_unique(explode(PATH_SEPARATOR, get_include_path()));
    // Files in this directory override others, as this is where user-configured files go.
    $directories[] = Civi::paths()->getPath('[civicrm.files]/.');
    foreach ($directories as $directory) {
      $dir = \CRM_Utils_File::addTrailingSlash($directory) . 'crm-tutorials';
      if (is_dir($dir)) {
        foreach (glob("$dir/*.json") as $file) {
          $matches = [];
          preg_match('/([-a-z_A-Z0-9]*).json/', $file, $matches);
          $id = $matches[1];
          $paths[$id] = $file;
          $files[$id] = json_decode(file_get_contents($file), TRUE);
          $files[$id]['id'] = $id;
        }
      }
    }
    $files = array_combine($paths, $files);
    Civi::cache('community_messages')->set('tutorials', $files, (60 * 60 * 24 * 30));
  }
  return $files;
}

/**
 * @param $currentPath
 * @param $tutorialPath
 * @return bool
 */
function _civitutorial_match_url($currentPath, $tutorialPath) {
  $url = parse_url($tutorialPath);
  return ($currentPath == $url['path']);
}

/**
 * @param $tutorial
 * @param int $cid
 * @return bool
 * @throws \CiviCRM_API3_Exception
 */
function _civitutorial_match_group($tutorial, $cid = NULL) {
  if (empty($tutorial['groups'])) {
    return TRUE;
  }
  $contact = civicrm_api3('Contact', 'getsingle', [
    'id' => $cid ?: 'user_contact_id',
    'return' => ["group"],
  ]);
  if (empty($contact['groups'])) {
    return FALSE;
  }
  $groups = civicrm_api3('Group', 'get', [
    'return' => ["name"],
    'id' => ['IN' => explode(',', $contact['groups'])],
  ]);
  $groups = array_column($groups['values'], 'name');

  return !!array_intersect($groups, $tutorial['groups']);
}

/**
 * @param $urlPath
 */
function _civitutorial_load($urlPath) {
  // Because this hook gets called twice sometimes
  static $ranAlready = FALSE;
  $cid = CRM_Core_Session::getLoggedInContactID();
  if ($cid && !$ranAlready && !CRM_Core_Resources::isAjaxMode() && CRM_Core_Permission::check('access CiviCRM')) {
    $ranAlready = TRUE;
    $resources = CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.tutorial', 'vendor/hopscotch/css/hopscotch.min.css')
      ->addScriptFile('org.civicrm.tutorial', 'vendor/hopscotch/js/hopscotch.min.js', 0, 'html-header')
      ->addScriptFile('org.civicrm.tutorial', 'js/tutorial.js');
    if (CRM_Core_Permission::check('administer CiviCRM')) {
      $resources
        ->addScriptFile('org.civicrm.tutorial', 'js/tutorial-admin.js')
        ->addScriptFile('civicrm', 'js/jquery/jquery.crmIconPicker.js')
        ->addVars('tutorialAdmin', [
          'path' => $resources->getUrl('org.civicrm.tutorial')
        ]);
      // Add strings from the html files for i18n.
      $strings = new CRM_Core_Resources_Strings(Civi::cache('js_strings'));
      foreach (glob(__DIR__ . '/html/*.html') as $file) {
        $resources->addString($strings->get('org.civicrm.tutorial', $file, 'text/html'), 'org.civicrm.tutorial');
      }
    }
    $tutorials = _civitutorial_get_files();
    foreach ($tutorials as $path => $tutorial) {
      if (_civitutorial_match_url($urlPath, $tutorial['url']) && _civitutorial_match_group($tutorial)) {
        // Check if user has viewed this tutorial already
        /** @var Civi\Core\SettingsBag $settings */
        $settings = Civi::service('settings_manager')->getBagByContact(NULL, $cid);
        $views = (array) $settings->get('tutorials');
        $tutorial['viewed'] = !empty($views[$tutorial['id']]);
        $resources->addVars('tutorial', $tutorial);
        // Mark this tutorial as viewed
        if (empty($views[$tutorial['id']])) {
          $views[$tutorial['id']] = date('Y-m-d H:i:s');
          $settings->set('tutorials', $views);
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function tutorial_civicrm_navigationMenu(&$menu) {
  _tutorial_civix_insert_navigation_menu($menu, 'Support', [
    'label' => ts('Take a tour of this screen'),
    'url' => '#tutorial-start',
    'name' => 'tutorial',
    'permission' => 'access CiviCRM',
    'title' => ts('Get an interactive walkthrough of this screen'),
    'icon' => 'crm-i fa-play',
  ]);
  _tutorial_civix_insert_navigation_menu($menu, 'Support', [
    'label' => ts('Create tour of this screen'),
    'url' => '#tutorial-admin',
    'name' => 'tutorial_admin',
    'permission' => 'administer CiviCRM',
    'title' => ts('Create an interactive walkthrough for your users'),
    'icon' => 'crm-i fa-map',
  ]);
}
