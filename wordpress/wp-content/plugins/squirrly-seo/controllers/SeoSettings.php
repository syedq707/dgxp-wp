<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQ_Controllers_SeoSettings extends SQ_Classes_FrontController
{

    public $pages = array();

    function init()
    {
        $tab = preg_replace("/[^a-zA-Z0-9]/", "", SQ_Classes_Helpers_Tools::getValue('tab', 'tweaks'));

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('seosettings');

        if (method_exists($this, $tab)) {
            call_user_func(array($this, $tab));
        }

        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
            wp_enqueue_style('media-views');
        }

        //@ob_flush();
        $this->show_view('SeoSettings/' . esc_attr(ucfirst($tab)));

        //get the modal window for the assistant popup
        echo SQ_Classes_ObjController::getClass('SQ_Models_Assistant')->getModal();
    }

    public function gotoImport()
    {
        $_GET['tab'] = 'backup';
        return $this->init();
    }

    public function automation()
    {
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('highlight');
        SQ_Classes_ObjController::getClass('SQ_Controllers_Patterns')->init();
    }

    public function metas()
    {
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('highlight');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('snippet');
    }

    public function links()
    {
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('highlight');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('snippet');
    }


    public function backup()
    {
        add_filter('sq_themes', array(SQ_Classes_ObjController::getClass('SQ_Models_ImportExport'), 'getAvailableThemes'), 10, 1);
        add_filter('sq_importList', array(SQ_Classes_ObjController::getClass('SQ_Models_ImportExport'), 'importList'));
    }


    public function hookFooter()
    {
        if (!SQ_Classes_Helpers_Tools::getOption('sq_seoexpert')) {
            echo "<script>jQuery('.sq_advanced').hide();</script>";
        } else {
            echo "<script>jQuery('.sq_advanced').show();</script>";
        }
    }

    /**
     * Called when action is triggered
     *
     * @return void
     */
    public function action()
    {
        parent::action();

        switch (SQ_Classes_Helpers_Tools::getValue('action')) {

            case 'sq_seosettings_save':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                //Save the settings
                if (!empty($_POST)) {
                    SQ_Classes_ObjController::getClass('SQ_Models_Settings')->saveSettings();
                }

                //save the options in database
                SQ_Classes_Helpers_Tools::saveOptions();

                //show the saved message
                if (!SQ_Classes_Error::isError()) {
                    //reset the report time
                    SQ_Classes_Helpers_Tools::saveOptions('seoreport_time', false);

                    SQ_Classes_Error::setMessage(esc_html__("Saved", 'squirrly-seo'));
                }

                break;

            case 'sq_seosettings_ga_revoke':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                //remove connection with Google Analytics
                $response = SQ_Classes_RemoteController::revokeGaConnection();
                if (!is_wp_error($response)) {
                    SQ_Classes_RemoteController::checkin();
                    SQ_Classes_Error::setError(esc_html__("Google Analytics account is disconnected.", 'squirrly-seo') . " <br /> ", 'success');
                } else {
                    SQ_Classes_Error::setError(esc_html__("Error! Could not disconnect the account.", 'squirrly-seo') . " <br /> ");
                }
                break;
            case 'sq_seosettings_gsc_revoke':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                //remove connection with Google Search Console
                $response = SQ_Classes_RemoteController::revokeGscConnection();
                if (!is_wp_error($response)) {
                    SQ_Classes_RemoteController::checkin();
                    SQ_Classes_Error::setError(esc_html__("Google Search Console account is disconnected.", 'squirrly-seo') . " <br /> ", 'success');
                } else {
                    SQ_Classes_Error::setError(esc_html__("Error! Could not disconnect the account.", 'squirrly-seo') . " <br /> ");
                }
                break;
            case 'sq_seosettings_ga_check':
            case 'sq_seosettings_gsc_check':
            case 'sq_seosettings_clear_cache':

		        if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
			        return;
		        }

                //Refresh the checkin on login
                delete_transient('sq_checkin');
                SQ_Classes_RemoteController::checkin();

                break;
            case 'sq_seosettings_ga_save':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                $property_id = SQ_Classes_Helpers_Tools::getValue('property_id');

                if ($property_id) {
                    $args = array();
                    $args['property_id'] = $property_id;
                    SQ_Classes_RemoteController::saveGAProperties($args);
                }

                SQ_Classes_Error::setMessage(esc_html__("Saved", 'squirrly-seo'));
                break;
            case 'sq_seosettings_backupsettings':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    SQ_Classes_Helpers_Tools::setHeader('json');
                    echo wp_json_encode($response);
                    exit();
                }

                SQ_Classes_Helpers_Tools::setHeader('text');
                header("Content-Disposition: attachment; filename=squirrly-settings-" . gmdate('Y-m-d') . ".txt");

                if (function_exists('base64_encode')) {
                    echo base64_encode(wp_json_encode(SQ_Classes_Helpers_Tools::$options));
                } else {
                    echo wp_json_encode(SQ_Classes_Helpers_Tools::$options);
                }

                exit();
            case 'sq_seosettings_restoresettings':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                if (!empty($_FILES['sq_options']) && $_FILES['sq_options']['tmp_name'] <> '') {
                    $fp = fopen($_FILES['sq_options']['tmp_name'], 'rb');
                    $options = '';
                    while (($line = fgets($fp)) !== false) {
                        $options .= $line;
                    }
                    try {
                        if (function_exists('base64_encode') && base64_decode($options) <> '') {
                            $options = @base64_decode($options);
                        }
                        $options = json_decode($options, true);
                        if (is_array($options) && isset($options['sq_api'])) {
                            $options['sq_api'] = SQ_Classes_Helpers_Tools::getOption('sq_api');
                            $options['sq_seojourney'] = SQ_Classes_Helpers_Tools::getOption('sq_seojourney');
                            $options['menu'] = SQ_Classes_Helpers_Tools::getOption('menu');
                            $options['sq_auto_devkit'] = SQ_Classes_Helpers_Tools::getOption('sq_auto_devkit');
                            $options['sq_devkit_logo'] = SQ_Classes_Helpers_Tools::getOption('sq_devkit_logo');
                            $options['sq_devkit_name'] = SQ_Classes_Helpers_Tools::getOption('sq_devkit_name');
                            $options['sq_devkit_menu_name'] = SQ_Classes_Helpers_Tools::getOption('sq_devkit_menu_name');
                            $options['sq_devkit_audit_success'] = SQ_Classes_Helpers_Tools::getOption('sq_devkit_audit_success');
                            $options['sq_devkit_audit_fail'] = SQ_Classes_Helpers_Tools::getOption('sq_devkit_audit_fail');
                            SQ_Classes_Helpers_Tools::$options = $options;
                            SQ_Classes_Helpers_Tools::saveOptions();

                            //Check if there is an old backup from Squirrly
                            SQ_Classes_Helpers_Tools::getOptions();

                            //reset the report time
                            SQ_Classes_Helpers_Tools::saveOptions('seoreport_time', false);

                            SQ_Classes_Error::setError(esc_html__("Great! The backup is restored.", 'squirrly-seo') . " <br /> ", 'success');
                        } else {
                            SQ_Classes_Error::setError(esc_html__("Error! The backup is not valid.", 'squirrly-seo') . " <br /> ");
                        }
                    } catch (Exception $e) {
                        SQ_Classes_Error::setError(esc_html__("Error! The backup is not valid.", 'squirrly-seo') . " <br /> ");
                    }
                } else {
                    SQ_Classes_Error::setError(esc_html__("Error! You have to enter a previously saved backup file.", 'squirrly-seo') . " <br /> ");
                }
                break;
            case 'sq_seosettings_backupseo':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-Disposition: attachment; filename=squirrly-seo-" . gmdate('Y-m-d') . ".sql");

                if (function_exists('base64_encode')) {
                    echo base64_encode(SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->createTableBackup());
                } else {
                    echo SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->createTableBackup();
                }
                exit();
            case 'sq_seosettings_restoreseo':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                if (!empty($_FILES['sq_sql']) && $_FILES['sq_sql']['tmp_name'] <> '') {
                    $fp = fopen($_FILES['sq_sql']['tmp_name'], 'rb');
                    $sql_file = '';
                    while (($line = fgets($fp)) !== false) {
                        $sql_file .= $line;
                    }

                    if (function_exists('base64_encode')) {
                        $sql_file = @base64_decode($sql_file);
                    }

                    if ($sql_file <> '' && strpos($sql_file, 'INSERT INTO') !== false) {
                        try {

                            $queries = explode("INSERT INTO", $sql_file);
                            SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->executeSql($queries);
                            SQ_Classes_Error::setError(esc_html__("Great! The SEO backup is restored.", 'squirrly-seo') . " <br /> ", 'success');

                        } catch (Exception $e) {
                            SQ_Classes_Error::setError(esc_html__("Error! The backup is not valid.", 'squirrly-seo') . " <br /> ");
                        }
                    } else {
                        SQ_Classes_Error::setError(esc_html__("Error! The backup is not valid.", 'squirrly-seo') . " <br /> ");
                    }
                } else {
                    SQ_Classes_Error::setError(esc_html__("Error! You have to enter a previously saved backup file.", 'squirrly-seo') . " <br /> ");
                }
                break;
            case 'sq_seosettings_importall':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
		            return;
	            }

                $platform = SQ_Classes_Helpers_Tools::getValue('sq_import_platform', '');
                if ($platform <> '') {
                    try {
                        SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->importDBSettings($platform);
                        $seo = SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->importDBSeo($platform);
                        if (!empty($seo)) {
                            //Check if the Squirrly Table Exists
                            SQ_Classes_ObjController::getClass('SQ_Models_Qss')->checkTableExists();

                            foreach ($seo as $sq_hash => $metas) {
                                SQ_Classes_ObjController::getClass('SQ_Models_Qss')->saveSqSEO(
                                    (isset($metas['url']) ? $metas['url'] : ''),
                                    $sq_hash,
                                    maybe_serialize(
                                        array(
                                        'ID' => (isset($metas['post_id']) ? (int)$metas['post_id'] : 0),
                                        'post_type' => (isset($metas['post_type']) ? $metas['post_type'] : ''),
                                        'term_id' => (isset($metas['term_id']) ? (int)$metas['term_id'] : 0),
                                        'taxonomy' => (isset($metas['taxonomy']) ? $metas['taxonomy'] : ''),
                                        )
                                    ),
                                    maybe_serialize($metas),
                                    gmdate('Y-m-d H:i:s')
                                );
                            }
                        }

                        SQ_Classes_Error::setMessage(sprintf(esc_html__("Success! The import from %s was completed successfully and your SEO is safe!", 'squirrly-seo'), SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->getName($platform)));
                    } catch (Exception $e) {
                        SQ_Classes_Error::setMessage(esc_html__("Error! An error occured while import. Please try again.", 'squirrly-seo'));
                    }
                }
                break;
            case 'sq_seosettings_importsettings':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                $platform = SQ_Classes_Helpers_Tools::getValue('sq_import_platform', '');
                if ($platform <> '') {
                    if (SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->importDBSettings($platform)) {
                        SQ_Classes_Error::setMessage(esc_html__("All the Plugin settings were imported successfuly!", 'squirrly-seo'));
                    } else {
                        SQ_Classes_Error::setMessage(esc_html__("No settings found for this plugin/theme.", 'squirrly-seo'));
                    }
                }
                break;
            case 'sq_seosettings_importseo':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                $platform = SQ_Classes_Helpers_Tools::getValue('sq_import_platform', '');
                $overwrite = SQ_Classes_Helpers_Tools::getValue('sq_import_overwrite');

                if ($platform <> '') {
                    $seo = SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->importDBSeo($platform);

                    if (!empty($seo)) {
                        foreach ($seo as $sq_hash => $metas) {
                            $sq = SQ_Classes_ObjController::getClass('SQ_Models_Qss')->getSqSeo($sq_hash);

                            if ($overwrite || !($sq->title && $sq->description)) {

                                SQ_Classes_ObjController::getClass('SQ_Models_Qss')->saveSqSEO(
                                    (isset($metas['url']) ? $metas['url'] : ''),
                                    $sq_hash,
                                    maybe_serialize(
                                        array(
                                        'ID' => (isset($metas['post_id']) ? (int)$metas['post_id'] : 0),
                                        'post_type' => (isset($metas['post_type']) ? $metas['post_type'] : ''),
                                        'term_id' => (isset($metas['term_id']) ? (int)$metas['term_id'] : 0),
                                        'taxonomy' => (isset($metas['taxonomy']) ? $metas['taxonomy'] : ''),
                                        )
                                    ),
                                    maybe_serialize($metas),
                                    gmdate('Y-m-d H:i:s')
                                );

                            }
                        }
                    }

                    SQ_Classes_Error::setMessage(sprintf(esc_html__("Success! The import from %s was completed successfully and your SEO is safe!", 'squirrly-seo'), SQ_Classes_ObjController::getClass('SQ_Models_ImportExport')->getName($platform)));
                }
                break;
            case 'sq_rollback':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
		            return;
	            }

                SQ_Classes_Helpers_Tools::setHeader('html');
                $plugin_slug = basename(_SQ_PLUGIN_NAME_, '.php');

                $rollback = SQ_Classes_ObjController::getClass('SQ_Models_Rollback');

                $rollback->set_plugin(
                    array(
                    'version' => SQ_STABLE_VERSION,
                    'plugin_name' => _SQ_ROOT_DIR_,
                    'plugin_slug' => $plugin_slug,
                    'package_url' => sprintf('https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, SQ_STABLE_VERSION),
                    )
                );

                $rollback->run();

                wp_die(
                    '', esc_html__("Rollback to Previous Version", 'squirrly-seo'), [
                        'response' => 200,
                    ]
                );
            case 'sq_reinstall':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
		            return;
	            }

                SQ_Classes_Helpers_Tools::setHeader('html');
                $plugin_slug = basename(_SQ_PLUGIN_NAME_, '.php');


                $rollback = SQ_Classes_ObjController::getClass('SQ_Models_Rollback');

                $rollback->set_plugin(
                    array(
                    'version' => SQ_VERSION,
                    'plugin_name' => _SQ_ROOT_DIR_,
                    'plugin_slug' => $plugin_slug,
                    'package_url' => sprintf('https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, SQ_VERSION),
                    )
                );

                $rollback->run();

                wp_die(
                    '', esc_html__("Reinstall Current Version", 'squirrly-seo'), [
                        'response' => 200,
                    ]
                );
            case 'sq_alerts_close':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
		            return;
	            }

	            //remove the specified alert from showing again
	            if ($alert = SQ_Classes_Helpers_Tools::getValue('alert')) {
	                if (in_array($alert, array('sq_alert_overview', 'sq_alert_journey'))) {
	                    SQ_Classes_Helpers_Tools::saveOptions($alert, false);
	                }
	            }
	            break;
            /**************************** Ajax *******************************************************/
            case 'sq_ajax_seosettings_save':
                SQ_Classes_Helpers_Tools::setHeader('json');

                $response = array();
                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                $name = SQ_Classes_Helpers_Tools::getValue('input');
                $value = SQ_Classes_Helpers_Tools::getValue('value');

                if (isset(SQ_Classes_Helpers_Tools::$options[$name])) {
                    SQ_Classes_Helpers_Tools::saveOptions($name, $value);
                    $response['data'] = SQ_Classes_Error::showNotices(esc_html__("Saved", 'squirrly-seo'), 'sq_success');
                } else {
                    $response['data'] = SQ_Classes_Error::showNotices(esc_html__("Could not save the changes", 'squirrly-seo'), 'sq_error');

                }

                echo wp_json_encode($response);
                exit();
            case 'sq_ajax_sla_sticky':

                SQ_Classes_Helpers_Tools::setHeader('json');

                $response = array();
                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                SQ_Classes_Helpers_Tools::saveUserMeta('sq_auto_sticky', (int)SQ_Classes_Helpers_Tools::getValue('sq_auto_sticky'));
                echo wp_json_encode(array());
                exit();
            case 'sq_ajax_gsc_code':

                SQ_Classes_Helpers_Tools::setHeader('json');

                $response = array();
                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                //remove connection with Google Analytics
                $code = SQ_Classes_RemoteController::getGSCToken();

                if (!is_wp_error($code) && $code) {
                    $response['code'] = SQ_Classes_Helpers_Sanitize::checkGoogleWTCode($code);
                } else {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("Error! Could not get the code. Connect to Google Search Console and validate the connection.", 'squirrly-seo'), 'sq_error');
                }

                echo wp_json_encode($response);
                exit();
            case 'sq_ajax_ga_code':

                SQ_Classes_Helpers_Tools::setHeader('json');

                $response = array();
                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                //remove connection with Google Analytics
                $code = SQ_Classes_RemoteController::getGAToken();
                if (!is_wp_error($code) && $code) {
                    $response['code'] = $code;
                } else {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("Error! Could not get the tracking code. Connect to Google Analytics and get the website tracking code from Admin area.", 'squirrly-seo'), 'sq_error');
                }
                echo wp_json_encode($response);
                exit();
            case 'sq_ajax_connection_check':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            $response = array();
	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
	                $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
	                echo wp_json_encode($response);
	                exit();
	            }

	            //delete local checking cache
	            delete_transient('sq_checkin');
	            //check the connection again
	            SQ_Classes_RemoteController::checkin();

	            echo wp_json_encode(array());
	            exit();

        }

    }

}
