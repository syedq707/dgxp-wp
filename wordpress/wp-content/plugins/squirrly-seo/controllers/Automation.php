<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQ_Controllers_Automation extends SQ_Classes_FrontController
{

    public $pages = array();

    function init()
    {
        $tab = preg_replace("/[^a-zA-Z0-9]/", "", SQ_Classes_Helpers_Tools::getValue('tab', 'types'));

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap-reboot');
        if (is_rtl()) {
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('popper');
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap.rtl');
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('rtl');
        } else {
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap');
        }
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap-select');

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('switchery');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('fontawesome');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('global');

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('assistant');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('navbar');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('seosettings');

        if (method_exists($this, $tab)) {
            call_user_func(array($this, $tab));
        }

        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
            wp_enqueue_style('media-views');
        }

        //@ob_flush();
        $this->show_view('Automation/' . esc_attr(ucfirst($tab)));

        //get the modal window for the assistant popup
        echo SQ_Classes_ObjController::getClass('SQ_Models_Assistant')->getModal();
    }

    public function automation()
    {
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('highlight');
        SQ_Classes_ObjController::getClass('SQ_Controllers_Patterns')->init();
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

            ///////////////////////////////////////////SEO SETTINGS AUTOMATION
            case 'sq_seosettings_automation':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                //Save the settings
                if (!empty($_POST)) {
                    SQ_Classes_ObjController::getClass('SQ_Models_Settings')->saveValues($_POST);
                }

                if (SQ_Classes_Helpers_Tools::isAjax()) {
                    SQ_Classes_Helpers_Tools::setHeader('json');

                    $json = array();

                    if (SQ_Classes_Error::isError()) {
                        $json['error'] = SQ_Classes_Error::getError();
                    }else{
                        $json['data'] = SQ_Classes_Error::showNotices(esc_html__("Saved", 'squirrly-seo'), 'sq_success');
                    }

                    echo wp_json_encode($json);
                    exit();
                }

                //show the saved message
                if (!SQ_Classes_Error::isError()) {
                    SQ_Classes_Error::setMessage(esc_html__("Saved", 'squirrly-seo'));
                }

                break;
            case 'sq_automation_addpostype':

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    return;
                }

                //Get the new post type
                $posttype = SQ_Classes_Helpers_Tools::getValue('posttype');
                $filter = array('public' => true, '_builtin' => false);
                $types = get_post_types($filter);
                foreach ($types as $pattern => $type) {
                    if($post_type_obj = get_post_type_object($pattern)) {
                        if($post_type_obj->has_archive) {
                            $types['archive-' . $pattern] = 'archive-' . $pattern;
                        }
                    }
                }

                $filter = array('public' => true,);
                $taxonomies = get_taxonomies($filter);
                foreach ($taxonomies as $pattern => $type) {
                    $types['tax-' . $pattern] = 'tax-' . $pattern;
                }

                //If the post type is in the list of types
                if ($posttype && in_array($posttype, $types)) {
                    $patterns = SQ_Classes_Helpers_Tools::getOption('patterns');
                    //if the post type does not already exists
                    if (!isset($patterns[$posttype])) {
                        //add the custom rights to the new post type
                        $patterns[$posttype] = $patterns['custom'];
                        $patterns[$posttype]['protected'] = 0;
                        //save the options in database
                        SQ_Classes_Helpers_Tools::saveOptions('patterns', $patterns);

                        SQ_Classes_Error::setMessage(esc_html__("Saved", 'squirrly-seo'), 'sq_success');
                        break;
                    }
                }


                //Return error in case the post is not saved
                SQ_Classes_Error::setError(esc_html__("Could not add the post type.", 'squirrly-seo'));
                break;

            /************************ Automation *******************************************************/
            case 'sq_ajax_automation_deletepostype':

                SQ_Classes_Helpers_Tools::setHeader('json');
                $response = array();

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                //Get the new post type
                $posttype = SQ_Classes_Helpers_Tools::getValue('value');

                //If the post type is in the list of types
                if ($posttype && $posttype <> '') {
                    $patterns = SQ_Classes_Helpers_Tools::getOption('patterns');
                    //if the post type exists in the patterns
                    if (isset($patterns[$posttype])) {
                        //add the custom rights to the new post type
                        unset($patterns[$posttype]);

                        //save the options in database
                        SQ_Classes_Helpers_Tools::saveOptions('patterns', $patterns);

                        $response['data'] = SQ_Classes_Error::showNotices(esc_html__("Saved", 'squirrly-seo'), 'sq_success');
                        echo wp_json_encode($response);
                        exit();
                    }
                }


                //Return error in case the post is not saved
                $response['data'] = SQ_Classes_Error::showNotices(esc_html__("Could not add the post type.", 'squirrly-seo'), 'sq_error');
                echo wp_json_encode($response);
                exit();

        }

    }

}
