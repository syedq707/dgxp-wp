<?php
/*
 * Copyright (c) 2012-2023, Squirrly.
 * The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

 * Plugin Name: Squirrly SEO (Peaks)
 * Plugin URI: https://wordpress.org/plugins/squirrly-seo/
 * Description: A.I.-based Private SEO Consultant. In a Plugin. Powered by Machine Learning and Cloud Services. Over 300 functionalities for SEO available when you need them.
 * Author: Squirrly
 * Author URI: https://plugin.squirrly.co
 * Version: 12.1.21
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: squirrly-seo
 * Domain Path: /languages
 */

if (!defined('SQ_VERSION')) {
    /* SET THE CURRENT VERSION ABOVE AND BELOW */
    define('SQ_VERSION', '12.1.21');
    //The last stable version
    define('SQ_STABLE_VERSION', '12.1.20');
    // Call config files
    try {
        include_once dirname(__FILE__) . '/config/config.php';
        include_once dirname(__FILE__) . '/debug/index.php';

        /* important to check the PHP version */
        // inport main classes
        include_once _SQ_CLASSES_DIR_ . 'ObjController.php';

        // Load helpers
        SQ_Classes_ObjController::getClass('SQ_Classes_Helpers_Tools');
        SQ_Classes_ObjController::getClass('SQ_Classes_Helpers_Sanitize');
        // Load the Front and Block controller
        SQ_Classes_ObjController::getClass('SQ_Classes_FrontController');
        SQ_Classes_ObjController::getClass('SQ_Classes_BlockController');

        // Upgrade Squirrly call.
        register_activation_hook(__FILE__, array(SQ_Classes_ObjController::getClass('SQ_Classes_Helpers_Tools'), 'sq_activate'));
        register_deactivation_hook(__FILE__, array(SQ_Classes_ObjController::getClass('SQ_Classes_Helpers_Tools'), 'sq_deactivate'));

        if (SQ_Classes_Helpers_Tools::isBackedAdmin()) {
            SQ_Classes_ObjController::getClass('SQ_Classes_FrontController')->runAdmin();
        } else {
            SQ_Classes_ObjController::getClass('SQ_Classes_FrontController')->runFrontend();
        }


    } catch (Exception $e) {
    }
}