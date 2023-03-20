<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQ_Controllers_FocusPages extends SQ_Classes_FrontController
{

    /**
     * 
     *
     * @var object Checkin process with Squirrly Cloud 
     */
    public $checkin;
    /**
     * 
     *
     * @var array list of tasks labels 
     */
    public $labels = array();
    /**
     * 
     *
     * @var array found pages in DB 
     */
    public $pages = array();
    /**
     * 
     *
     * @var array of focus pages from API 
     */
    public $focuspages = array();

    /** @var Used in the view for defining the Focus Page row */
    public $focuspage;
    public $post;

    /**
     * Initiate the class if called from menu
     *
     * @return mixed|void
     */
    function init()
    {

        if (SQ_Classes_Helpers_Tools::getOption('sq_api') == '') {
            $this->show_view('Errors/Connect');
            return;
        }

        //Checkin to API V2
        $this->checkin = SQ_Classes_RemoteController::checkin();

        if (is_wp_error($this->checkin)) {
            if ($this->checkin->get_error_message() == 'no_data') {
                $this->show_view('Errors/Error');
                return;
            } elseif ($this->checkin->get_error_message() == 'maintenance') {
                $this->show_view('Errors/Maintenance');
                return;
            }
        }

        $tab = preg_replace("/[^a-zA-Z0-9]/", "", SQ_Classes_Helpers_Tools::getValue('tab', 'pagelist'));

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap-reboot');
        if (is_rtl()) {
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('popper');
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap.rtl');
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('rtl');
        } else {
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('bootstrap');
        }
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('switchery');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('fontawesome');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('global');

        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('assistant');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('navbar');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('seosettings');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('chart');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('knob');

        if (method_exists($this, $tab)) {
            call_user_func(array($this, $tab));
        }
        $this->show_view('FocusPages/' . esc_attr(ucfirst($tab)));

        //get the modal window for the assistant popup
        echo SQ_Classes_ObjController::getClass('SQ_Models_Assistant')->getModal();
    }

    /**
     * Load for Add Focus Page menu tab
     */
    public function addpage()
    {
        $search = (string)SQ_Classes_Helpers_Tools::getValue('skeyword', '');
        $this->pages = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->getPages($search);

        //get also the focus pages
        $this->focuspages = SQ_Classes_RemoteController::getFocusPages();

        if (!empty($this->focuspages)) {
            foreach ($this->focuspages as &$focuspage) {
                $focuspage = SQ_Classes_ObjController::getDomain('SQ_Models_Domain_FocusPage', $focuspage);
            }
        }
    }

    /**
     * Called for List of the Focus Pages
     */
    public function pagelist()
    {
        //Set the Labels and Categories
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('focuspages');
        SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('labels');

        //Set the focus pages and labels
        $this->setFocusPages();
    }

    /**
     * Set the Focus Pages and Labels
     */
    public function setFocusPages()
    {
        $labels = SQ_Classes_Helpers_Tools::getValue('slabel', array());
        $days_back = (int)SQ_Classes_Helpers_Tools::getValue('days_back', 90);
        $sid = SQ_Classes_Helpers_Tools::getValue('sid');

        SQ_Classes_ObjController::getClass('SQ_Models_FocusPages')->init();
        $this->checkin = SQ_Classes_RemoteController::checkin();

        if ($focuspages = SQ_Classes_RemoteController::getFocusPages()) {

            if (is_wp_error($focuspages)) {
                SQ_Classes_Error::setError('Could not load the Focus Pages.');
            } else {

                //Get the audits for the focus pages
                $audits = SQ_Classes_RemoteController::getFocusAudits(array('post_id' => $sid, 'days_back' => $days_back));

                if (!empty($focuspages)) {
                    foreach ($focuspages as $focuspage) {

                        //Add the audit data if exists
                        if (!is_wp_error($audits)) {
                            if (isset($focuspage->user_post_id) && !empty($audits)) {
                                foreach ($audits as $audit) {
                                    if ($focuspage->user_post_id == $audit->user_post_id) {
                                        if (isset($audit->audit)) $audit->audit = json_decode($audit->audit); //set the audit data
                                        if (isset($audit->stats)) $audit->stats = json_decode($audit->stats); //set the stats and progress data
                                        $focuspage = (object)array_merge((array)$focuspage, (array)$audit);
                                        break;
                                    }
                                }
                            }
                        }

                        /** @var SQ_Models_Domain_FocusPage $focuspage */
                        $focuspage = SQ_Classes_ObjController::getDomain('SQ_Models_Domain_FocusPage', $focuspage);

                        //set the connection info with GSC and GA
                        $focuspage->audit->sq_analytics_gsc_connected = (isset($this->checkin->connection_gsc) ? $this->checkin->connection_gsc : 0);
                        $focuspage->audit->sq_analytics_google_connected = (isset($this->checkin->connection_ga) ? $this->checkin->connection_ga : 0);
                        $focuspage->audit->sq_subscription_serpcheck = (isset($this->checkin->subscription_serpcheck) ? $this->checkin->subscription_serpcheck : 0);

                        //SQ_Debug::dump($focuspage, $focuspage->audit);

                        //If there is a local page, then show focus
                        if ($focuspage->getWppost()) {
                            //if post_id is set, show only that focus page
                            if ($sid && $focuspage->id <> $sid) {
                                continue;
                            }

                            $this->focuspages[] = SQ_Classes_ObjController::getClass('SQ_Models_FocusPages')->parseFocusPage($focuspage, $labels)->getFocusPage();

                        } elseif ($focuspage->user_post_id) {
                            SQ_Classes_Error::setError(esc_html__("Focus Page does not exist or was deleted from your website.", 'squirrly-seo'));
                            SQ_Classes_RemoteController::deleteFocusPage(array('user_post_id' => $focuspage->user_post_id));
                        }
                    }
                }
            }
        }

        //Remove the blank focus pages
        $this->focuspages = array_filter($this->focuspages);

        //Get the labels for view use
        if (!empty($labels) || count((array)$this->focuspages) > 1) {
            $this->labels = SQ_Classes_ObjController::getClass('SQ_Models_FocusPages')->getLabels();
        }
    }

    /**
     * Load the Google Chart
     *
     * @return string
     */
    public function loadScripts()
    {
        echo '<script>
               function drawScoreChart(id, values, reverse) {
                    var data = google.visualization.arrayToDataTable(values);

                    var options = {

                      title : "",
                      chartArea:{width:"85%",height:"80%"},
                      enableInteractivity: "true",
                      tooltip: {trigger: "auto"},
                      vAxis: {
                          direction: ((reverse) ? -1 : 1),
                          title: "",
                          viewWindowMode:"explicit",
                          viewWindow: {
                              max:100,
                              min:0
                          }},
                      hAxis: {
                          title: "",
                          baselineColor: "transparent",
                          gridlineColor: "transparent",
                          textPosition: "none"
                      } ,
                      seriesType: "bars",
                      series: {2: {type: "line"}},
                      legend: {position: "bottom"},
                      colors:["#6200EE"]
                    };

                    var chart = new google.visualization.ComboChart(document.getElementById(id));
                    chart.draw(data, options);
                    return chart;
                }
                function drawRankingChart(id, values, reverse) {
                    var data = google.visualization.arrayToDataTable(values);

                    var options = {

                        curveType: "function",
                        title: "",
                        chartArea:{width:"100%",height:"100%"},
                        enableInteractivity: "true",
                        tooltip: {trigger: "auto"},
                        pointSize: "2",
                        colors: ["#6200EE"],
                        hAxis: {
                          baselineColor: "transparent",
                           gridlineColor: "transparent",
                           textPosition: "none"
                        } ,
                        vAxis:{
                          direction: ((reverse) ? -1 : 1),
                          baselineColor: "transparent",
                          gridlineColor: "transparent",
                          textPosition: "none"
                        }
                    };

                    var chart = new google.visualization.LineChart(document.getElementById(id));
                    chart.draw(data, options);
                    return chart;
                }
                function drawTrafficChart(id, values, reverse) {
                     var data = google.visualization.arrayToDataTable(values);

                    var options = {

                      title : "",
                      chartArea:{width:"85%",height:"80%"},
                      enableInteractivity: "true",
                      tooltip: {trigger: "auto"},
                      vAxis: {
                          direction: ((reverse) ? -1 : 1),
                          title: "",
                          viewWindowMode:"explicit"
                      },
                      hAxis: {
                          title: "",
                          baselineColor: "transparent",
                          gridlineColor: "transparent",
                          textPosition: "none"
                      } ,
                      seriesType: "bars",
                      series: {2: {type: "line"}},
                      legend: {position: "bottom"},
                      colors:["#6200EE"]
                    };

                    var chart = new google.visualization.ComboChart(document.getElementById(id));
                    chart.draw(data, options);
                    return chart;
                }
          </script>';
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
	        case 'sq_focuspages_inspecturl':

				SQ_Classes_Helpers_Tools::setHeader('json');
		        $json = array();

		        if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
			        $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
			        echo wp_json_encode($response);
			        exit();
		        }

	            $post_id = (int)SQ_Classes_Helpers_Tools::getValue('post_id', 0);

	            //Set the focus pages and labels
	            $args = array();
	            $args['post_id'] = $post_id;
	            if ($json['html'] = SQ_Classes_RemoteController::getInspectURL($args)) {

	                //Support for international languages
	                if (function_exists('iconv') && SQ_Classes_Helpers_Tools::getOption('sq_non_utf8_support')) {
	                    if (strpos(get_bloginfo("language"), 'en') === false) {
	                        $json['html'] = iconv('UTF-8', 'UTF-8//IGNORE', $json['html']);
	                    }
	                }
	            }

	            if (SQ_Classes_Error::isError()) {
	                $json['error'] = SQ_Classes_Error::getError();
	            }

	            echo wp_json_encode($json);
	            exit();
	        case 'sq_focuspages_getpage':

	            SQ_Classes_Helpers_Tools::setHeader('json');
		        $json = array();

		        if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
			        $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
			        echo wp_json_encode($response);
			        exit();
		        }

	            //Set the focus pages and labels
	            $this->setFocusPages();

	            $json['html'] = $this->get_view('FocusPages/FocusPages');

	            //Support for international languages
	            if (function_exists('iconv') && SQ_Classes_Helpers_Tools::getOption('sq_non_utf8_support')) {
	                if (strpos(get_bloginfo("language"), 'en') === false) {
	                    $json['html'] = iconv('UTF-8', 'UTF-8//IGNORE', $json['html']);
	                }
	            }

	            if (SQ_Classes_Error::isError()) {
	                $json['error'] = SQ_Classes_Error::getError();
	            }

	            echo wp_json_encode($json);
	            exit();
	        case 'sq_focuspages_addnew':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
	                return;
	            }

	            $term_id = (int)SQ_Classes_Helpers_Tools::getValue('term_id', 0);
	            $taxonomy = SQ_Classes_Helpers_Tools::getValue('taxonomy', '');
	            $post_type = SQ_Classes_Helpers_Tools::getValue('type', '');

	            if ($post_id = (int)SQ_Classes_Helpers_Tools::getValue('post_id', 0)) {
	                if ($post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->getCurrentSnippet($post_id, $term_id, $taxonomy, $post_type)) {
	                    //Save the post data in DB with the hash
	                    SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->savePost($post);

	                    if ($post->post_status == 'publish' && $post->ID == $post_id) {
	                        //send the post to API
	                        $args = array();
	                        $args['post_id'] = $post->ID;
	                        $args['hash'] = $post->hash;
	                        $args['permalink'] = $post->url;
	                        if ($focuspage = SQ_Classes_RemoteController::addFocusPage($args)) {
	                            if (!is_wp_error($focuspage)) {
	                                SQ_Classes_Error::setError(esc_html__("Focus page is added. The audit may take a while so please be patient.", 'squirrly-seo') . " <br /> ", 'success');
	                                if (isset($focuspage->user_post_id)) {
	                                    set_transient('sq_auditpage_' . $focuspage->user_post_id, time());

	                                    SQ_Classes_Helpers_Tools::saveOptions('seoreport_time', false);
	                                }
	                            } elseif ($focuspage->get_error_message() == 'limit_exceed') {
	                                SQ_Classes_Error::setError(esc_html__("You reached the maximum number of focus pages for all your websites.", 'squirrly-seo') . " <br /> ");
	                            }
	                        } else {
	                            SQ_Classes_Error::setError(esc_html__("Error! Could not add the focus page.", 'squirrly-seo') . " <br /> ");
	                        }
	                    } else {
	                        SQ_Classes_Error::setError(esc_html__("Error! This focus page is not public.", 'squirrly-seo') . " <br /> ");
	                    }

	                } else {
	                    SQ_Classes_Error::setError(sprintf(esc_html__("Error! Could not find the focus page %d in your website.", 'squirrly-seo'), $post_id) . " <br /> ");
	                }
	            }
	            break;
	        case 'sq_focuspages_update':

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
	                return;
	            }

	            $post_id = (int)SQ_Classes_Helpers_Tools::getValue('post_id', 0);
	            $term_id = (int)SQ_Classes_Helpers_Tools::getValue('term_id', 0);
	            $taxonomy = SQ_Classes_Helpers_Tools::getValue('taxonomy', '');
	            $post_type = SQ_Classes_Helpers_Tools::getValue('type', '');
	            if ($id = (int)SQ_Classes_Helpers_Tools::getValue('id', 0)) {
	                if ($post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->getCurrentSnippet($post_id, $term_id, $taxonomy, $post_type)) {

	                    //Save the post data in DB with the hash
	                    SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->savePost($post);

	                    //send the post to API
	                    $args = array();
	                    $args['post_id'] = $id;
	                    $args['hash'] = $post->hash;
	                    $args['permalink'] = $post->url;
	                    if ($focuspage = SQ_Classes_RemoteController::updateFocusPage($args)) {

	                        if (!is_wp_error($focuspage)) {
	                            SQ_Classes_Error::setError(esc_html__("Focus page sent for recheck. It may take a while so please be patient.", 'squirrly-seo') . " <br /> ", 'success');
	                            set_transient('sq_auditpage_' . $id, time());
	                        } elseif ($focuspage->get_error_message() == 'too_many_attempts') {
	                            SQ_Classes_Error::setError(esc_html__("You've made too many requests, please wait a few minutes.", 'squirrly-seo') . " <br /> ");
	                        }

	                    } else {
	                        SQ_Classes_Error::setError(esc_html__("You've made too many requests, please wait a few minutes.", 'squirrly-seo') . " <br /> ");
	                        set_transient('sq_auditpage_' . $id, time());
	                    }

	                } else {
	                    SQ_Classes_Error::setError(sprintf(esc_html__("Error! Could not find the focus page %d in your website.", 'squirrly-seo'), $post_id) . " <br /> ");
	                }
	            }
	            break;
	        case 'sq_focuspages_delete':

            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
                return;
            }

            if ($post_id = SQ_Classes_Helpers_Tools::getValue('id')) {
                SQ_Classes_RemoteController::deleteFocusPage(array('user_post_id' => $post_id));
                SQ_Classes_Error::setError(esc_html__("The focus page is deleted", 'squirrly-seo') . " <br /> ", 'success');
            } else {
                SQ_Classes_Error::setError(esc_html__("Invalid params!", 'squirrly-seo') . " <br /> ");
            }

            break;
        }

    }
}
