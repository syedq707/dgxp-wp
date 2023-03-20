<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQ_Controllers_Post extends SQ_Classes_FrontController
{

    public $saved;

    public function init()
    {

        if (is_rtl()) {
            SQ_Classes_ObjController::getClass('SQ_Classes_DisplayController')->loadMedia('rtl');
        }

        if(SQ_Classes_Helpers_Tools::isBlockEditor()){
	        $this->wpLinkBlockEditor();
        }else{
	        add_action( 'admin_enqueue_scripts', array($this, 'wpLinkClassicEditor'), 99 );
	        if ( SQ_Classes_Helpers_Tools::getValue('action') == 'elementor') {
		        add_action( 'elementor/editor/before_enqueue_scripts', array($this, 'wpLinkClassicEditor'), 99 );
	        }
        }

        //load the draggable script in post edit for the floating SLA
        if (!wp_script_is('jquery-ui-core') || !wp_script_is('jquery-ui-draggable')) {
            wp_enqueue_script("jquery-ui-core");
            wp_enqueue_script("jquery-ui-draggable");
        }

        //Load the Live Assistant
        SQ_Classes_ObjController::getClass('SQ_Models_LiveAssistant')->loadMedia();
        $this->show_view('Blocks/LiveAssistant');

    }

    /**
     * Hook the post save
     */
    public function hookPost()
    {
        if (SQ_Classes_Helpers_Tools::getOption('sq_api') == '')
            return;

        //Hook and save the Snippet and Keywords for Attachment Pages
        add_action('wp_insert_attachment_data', array($this, 'hookAttachmentSave'), 12, 2);

        //if the option to save the images locally is activated
        if (SQ_Classes_Helpers_Tools::getOption('sq_local_images')) {
            add_filter('wp_insert_post_data', array($this, 'checkImage'), 13, 2);
        }

        //Hook the save post action
        if(SQ_Classes_Helpers_Tools::isPluginInstalled('jetpack/jetpack.php')){
            add_action('save_post', array($this, 'hookSavePost'), 21, 2);
        }else{
            add_action('save_post', array($this, 'hookSavePost'), 10, 2);
        }

        //Hook the Move To Trash action
        add_action('wp_trash_post', array(SQ_Classes_ObjController::getClass('SQ_Models_PostsList'), 'hookUpdateStatus'), 10, 1);

        if (SQ_Classes_Helpers_Tools::getOption('sq_auto_sitemap')) {
            add_action('transition_post_status', array(SQ_Classes_ObjController::getClass('SQ_Controllers_Sitemaps'), 'refreshSitemap'), PHP_INT_MAX, 3);
        }

        //Check the compatibility with Woocommerce
        if (SQ_Classes_Helpers_Tools::getOption('sq_jsonld_product_custom') && SQ_Classes_Helpers_Tools::getOption('sq_jsonld_woocommerce')) {
            SQ_Classes_ObjController::getClass('SQ_Models_Compatibility')->checkWooCommerce();
        }

        //add compatibility for backend
        SQ_Classes_ObjController::getClass('SQ_Models_Compatibility')->hookPostEditorBackend();

        //Make sure the URL is local and not changed by other plugins
        add_filter('sq_homeurl', array($this, 'getHomeUrl'));
    }

    /**
     * Get the current Home URL
     *
     * @param  $url
     * @return mixed
     */
    public function getHomeUrl($url)
    {
        if (defined('WP_HOME')) {
            return WP_HOME;
        } else {
            return get_option('home');
        }
    }

    /**
     * Initialize the TinyMCE editor for the current use
     *
     * @return void
     */
    public function hookEditor()
    {
        $this->saved = array();
    }

    /**
     * Check if the image is a remote image and save it locally
     *
     * @param  array $post_data
     * @param  array $postarr
     * @return array
     */
    public function checkImage($post_data, $postarr)
    {

        if (!isset($post_data['post_content']) || !isset($postarr['ID'])) {
            return $post_data;
        }

        if (isset($post_data['post_type']) && $post_data['post_type'] <> '') {
            if(!SQ_Classes_ObjController::getClass('SQ_Models_Post')->isSLAEnable($post_data['post_type'])){
                return $post_data;
            }
        }

        include_once ABSPATH . 'wp-admin/includes/image.php';

        $urls = array();
        if (function_exists('preg_match_all')) {
            @preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', stripslashes($post_data['post_content']), $out);

            if (!empty($out)) {
                if (!is_array($out[1]) || count((array)$out[1]) == 0) {
                    return $post_data;
                }

                if (get_bloginfo('wpurl') <> '') {
                    $domain = parse_url(home_url(), PHP_URL_HOST);

                    foreach ($out[1] as $row) {
                        if (strpos($row, '//') !== false && strpos($row, $domain) === false) {
                            if (!in_array($row, $urls)) {
                                $urls[] = $row;
                            }
                        }
                    }
                }
            }
        }

        if (!is_array($urls) || (is_array($urls) && count((array)$urls) == 0)) {
            return $post_data;
        }

        if (count((array)$urls) > 1) {
            $urls = array_unique($urls);
        }

        $time = microtime(true);

        //get the already downloaded images
        $images = get_post_meta((int)$postarr['ID'], '_sq_image_downloaded');

        foreach ($urls as $url) {

            //Set the title and filename
            $basename = md5(basename($url));
            $keyword = SQ_Classes_Helpers_Tools::getValue('sq_keyword', false);
            if ($keyword) {
                $title = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff ]|i', '', $keyword);
                $basename = preg_replace('|[^a-z0-9-_]|i', '', str_replace(' ', '-', strtolower($keyword)));
            }

            //check the images
            if (!empty($images)) {
                foreach ($images as $key => $local) {
                    $local = json_decode($local, true);
                    if ($local['url'] == md5($url)) {

                        //replace the image in the content
                        $post_data['post_content'] = str_replace($url, $local['file'], $post_data['post_content']);

                        continue 2;
                    }
                }
            }

            //Upload the image on server
            if ($file = $this->model->upload_image($url, $basename)) {
                if (!file_is_valid_image($file['file']))
                    continue;

                $local_file = $file['url'];
                if ($local_file !== false) {

                    //save as downloaded image to avoid duplicates
                    add_post_meta((int)$postarr['ID'], '_sq_image_downloaded', wp_json_encode(array('url' => md5($url), 'file' => $local_file)));

                    //replace the image in the content
                    $post_data['post_content'] = str_replace($url, $local_file, $post_data['post_content']);

                    //add the attachment image
                    $attach_id = wp_insert_attachment(
                        array(
                        'post_mime_type' => $file['type'],
                        'post_title' => $title,
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'guid' => $local_file
                        ), $file['file'], $postarr['ID']
                    );

                    $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                }
            }

            if (microtime(true) - $time >= 10) {
                break;
            }

        }

        return $post_data;
    }

    /**
     * Hook the Attachment save data
     * Don't use it for post save
     *
     * @param  array $post_data
     * @param  array $postarr
     * @return array
     */
    public function hookAttachmentSave($post_data, $postarr)
    {

        if (isset($postarr['ID']) && $post = get_post($postarr['ID'])) {
            //If the post is a new or edited post
            if (wp_is_post_autosave($post->ID) == '' 
                && get_post_status($post->ID) <> 'auto-draft' 
                && get_post_status($post->ID) <> 'inherit'
            ) {

                if ($post_data['post_type'] == 'attachment') {
                    //Save the SEO
                    SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->saveSEO($post->ID);

                    //Send the optimization when attachment page
                    $this->sendSeo($post);
                }
            }
        }

        return $post_data;
    }

    /**
     * Hook after save post to make sure the data is saved
     *
     * @param $post_id
     * @param WP_Post $post Post object.
     */
    public function hookSavePost($post_id, $post)
    {
        //make sure the post is loaded
        if($post_id && !isset($post->ID)){
            $post = get_post($post_id);
        }

        if ($post_id && isset($post->ID) && isset($post->post_type) && $post->post_type <> '') {
            //If the post is a new or edited post
            if (wp_is_post_autosave($post->ID) == '' 
                && get_post_status($post->ID) <> 'auto-draft' 
                && get_post_status($post->ID) <> 'inherit'
            ) {

                //Update the SEO Keywords from Live Assistant and Permalink
                add_filter('sq_seo_before_save', array($this, 'addSeoKeywords'), 11, 1);
                //Update the redirect to old slugs
                add_filter('sq_url_before_save', array($this, 'checkOldSlugs'), 11, 2);

                //Save the SEO
                SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->saveSEO($post->ID);

                //Exclude types for SLA
                if(!SQ_Classes_ObjController::getClass('SQ_Models_Post')->isSLAEnable($post->post_type)){
                    return;
                }

                //Send the optimization when attachment page
                $this->sendSeo($post);
            }
        }

    }

    /**
     * Send the Post to Squirrly API
     *
     * @param $post
     */
    public function sendSeo($post)
    {
        $args = array();

        $seo = SQ_Classes_Helpers_Tools::getValue('sq_seo', '');

        if (is_array($seo) && count((array)$seo) > 0) {
            $args['seo'] = implode(',', $seo);
        }

        $args['keyword'] = SQ_Classes_Helpers_Tools::getValue('sq_keyword', '');
        $args['status'] = $post->post_status;
        $args['permalink'] = get_permalink($post->ID);
        $args['author'] = $post->post_author;
        $args['post_id'] = $post->ID;
        $args['referer'] = 'edit';

        if ($args['permalink']) {
            SQ_Classes_RemoteController::savePost($args);
        }

    }

    /**
     * Called when Post action is triggered
     *
     * @return void
     */
    public function action()
    {
        parent::action();
        switch (SQ_Classes_Helpers_Tools::getValue('action')) {
            /** AJAX CALLS ***/
            case 'sq_ajax_save_post':

                SQ_Classes_Helpers_Tools::setHeader('json');

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                $post_id = (int)SQ_Classes_Helpers_Tools::getValue('post_id');
                $referer = SQ_Classes_Helpers_Tools::getValue('referer', false);

                if ($post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->getCurrentSnippet($post_id)) {

                    if (wp_is_post_autosave($post->ID) == ''
                        && get_post_status($post->ID) <> 'auto-draft'
                        && get_post_status($post->ID) <> 'inherit'
                    ) {

                        //Send the post optimization to Squirrly API
                        $this->sendSeo($post);

                        //save the reference for this post ID
                        if ($referer) update_post_meta($post_id, '_sq_sla', $referer);
                    }

                    $response['data'] = array('data' => esc_html__("Saved", 'squirrly-seo'));
                    echo wp_json_encode($response);
                } else {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("Can't get the post URL", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                }

                exit();
            case 'sq_ajax_get_post':
                SQ_Classes_Helpers_Tools::setHeader('json');

                if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                    exit();
                }

                $post_id = (int)SQ_Classes_Helpers_Tools::getValue('post_id');

                if ($post_id > 0) {
                    if ($post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->getCurrentSnippet($post_id)) {
                        if ($post->post_status <> 'publish') {
                            $post->url = sanitize_title($post->post_title);
                        }
                        echo wp_json_encode($post->toArray());
                    } else {
                        $response['error'] = SQ_Classes_Error::showNotices(esc_html__("Can't get the post URL", 'squirrly-seo'), 'sq_error');
                        echo wp_json_encode($response);
                    }
                } else {
                    $response['error'] = SQ_Classes_Error::showNotices(esc_html__("Invalid request", 'squirrly-seo'), 'sq_error');
                    echo wp_json_encode($response);
                }
                exit();
            case 'sq_ajax_search_blog':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array();
                $args['post_type'] = 'post';
                $args['post_status'] = 'publish';

                parse_str(SQ_Classes_Helpers_Tools::getValue('params'), $args);
                if (isset($args['exclude']))  $args['post__not_in'] = array((int)$args['exclude']);
                if (isset($args['nrb']))  $args['posts_per_page'] = (int)$args['nrb'];

                $responce = array();
                if ($posts = SQ_Classes_ObjController::getClass('SQ_Models_Post')->searchPost($args)) {
                    foreach ($posts as $post) {
                        $responce['results'][] = array('id' => $post->ID,
                            'url' => get_permalink($post->ID),
                            'title' => $post->post_title,
                            'content' => SQ_Classes_Helpers_Sanitize::truncate($post->post_content, 50),
                            'date' => $post->post_date_gmt);
                    }
                }

                echo wp_json_encode($responce);
                exit();
            ////////
            case 'sla_checkin':

	            SQ_Classes_Helpers_Tools::setHeader('json');

				if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $checkin = SQ_Classes_RemoteController::checkin();
                if (is_wp_error($checkin)) {
                    echo wp_json_encode(array('error' => $checkin->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $checkin));
                }

                exit();
            case 'sla_keywords':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'post_id' => SQ_Classes_Helpers_Tools::getValue('post_id'),
                );

                $response = SQ_Classes_RemoteController::getSLAKeywords($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_preview':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'filter' => SQ_Classes_Helpers_Tools::getValue('filter'),
                    'link' => SQ_Classes_Helpers_Tools::getValue('link'),
                );

                $response = SQ_Classes_RemoteController::getSLAPreview($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_tasks':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $response = SQ_Classes_RemoteController::getSLATasks();
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_briefcase_get':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'post_id' => SQ_Classes_Helpers_Tools::getValue('post_id'),
                    'search' => SQ_Classes_Helpers_Tools::getValue('search'),
                    'label' => SQ_Classes_Helpers_Tools::getValue('label'),
                    'return' => SQ_Classes_Helpers_Tools::getValue('return'),
                );

                $response = SQ_Classes_RemoteController::getSLABriefcase($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_briefcase_add':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'post_id' => SQ_Classes_Helpers_Tools::getValue('post_id'),
                    'keyword' => SQ_Classes_Helpers_Tools::getValue('keyword'),
                );

                $response = SQ_Classes_RemoteController::addSLABriefcase($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_briefcase_delete':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'id' => SQ_Classes_Helpers_Tools::getValue('id'),
                );

                $response = SQ_Classes_RemoteController::deleteSLABriefcase($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_briefcase_save':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $args = array(
                    'post_id' => SQ_Classes_Helpers_Tools::getValue('post_id'),
                    'optimizations' => SQ_Classes_Helpers_Tools::getValue('optimizations'),
                );

                $response = SQ_Classes_RemoteController::saveSLABriefcase($args);
                if (is_wp_error($response)) {
                    echo wp_json_encode(array('error' => $response->get_error_message()));
                }else{
                    echo wp_json_encode(array('data' => $response));
                }

                exit();
            case 'sla_customcall':

	            SQ_Classes_Helpers_Tools::setHeader('json');

	            if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
		            $response['error'] = SQ_Classes_Error::showNotices(esc_html__("You do not have permission to perform this action", 'squirrly-seo'), 'sq_error');
		            echo wp_json_encode($response);
		            exit();
	            }

                $url = SQ_Classes_Helpers_Tools::getValue('url');
                parse_str(SQ_Classes_Helpers_Tools::getValue('params'), $args);

                echo SQ_Classes_RemoteController::getCustomCall($url, $args);
                exit();
        }
    }

    /**
     * Save the keywords from briefcase into the META keywords if there are no keywords saved
     *
     * @param  SQ_Models_Domain_Sq $sq
     * @return SQ_Models_Domain_Sq
     */
    public function addSeoKeywords($sq)
    {
        if (empty($sq->keywords)) {
            $keywords = (array)SQ_Classes_Helpers_Tools::getValue('sq_briefcase_keyword', array());

            if (SQ_Classes_Helpers_Tools::getValue('sq_keyword', false)) {
                array_unshift($keywords, SQ_Classes_Helpers_Tools::getValue('sq_keyword'));
            }

            $keywords = array_filter($keywords);
            $keywords = array_unique($keywords);
            $sq->keywords = join(',', $keywords);
        }

        return $sq;
    }

    /**
     * Rewrite the function for pages and other post types
     *
     * @param  string $url
     * @param  string $sq_hash
     * @return string
     */
    public function checkOldSlugs($url, $sq_hash)
    {

        // Don't bother if it hasn't changed.
        $post = SQ_Classes_ObjController::getClass('SQ_Models_Qss')->getSqPost($sq_hash);
        $patterns = (array)SQ_Classes_Helpers_Tools::getOption('patterns');

        if (!isset($post->ID)) {
            return $url;
        }

        if (!empty($patterns) && $permalink = get_permalink($post->ID)) {
            if ($post->ID > 0 && get_post_status($post->ID) === 'publish' && $permalink <> $post->url) {

                //Get the Squirrly SEO Patterns
                foreach ($patterns as $pattern => $type) {
                    if (get_post_type($post->ID) == $pattern) {
                        if (isset($type['do_redirects']) && $type['do_redirects']) {

                            //do_redirects
                            $post_name = basename($post->url);
                            $old_slugs = (array)get_post_meta($post->ID, '_sq_old_slug');

                            // If we haven't added this old slug before, add it now.
                            if (!empty($post_name) && !in_array($post_name, $old_slugs)) {
                                add_post_meta($post->ID, '_sq_old_slug', $post_name);
                            }

                            // If the new slug was used previously, delete it from the list.
                            if (in_array($post->post_name, $old_slugs)) {
                                delete_post_meta($post->ID, '_sq_old_slug', $post->post_name);
                            }

                        }
                    }
                }

            }
        }

        return get_permalink($post->ID);
    }


	/**
	 * Add the nofollow option in the classic editor
	 * @return void
	 */
	public function wpLinkClassicEditor() {

		wp_deregister_script( 'wplink' );
		wp_register_script( 'wplink', _SQ_ASSETS_URL_ . 'js/wplink.min.js', array('jquery', 'wpdialogs'), SQ_VERSION, true );

		wp_localize_script(
			'wplink',
			'wpLinkL10n',
			[
				'title'             => esc_html__( 'Insert/edit link', 'squirrly-seo' ),
				'update'            => esc_html__( 'Update', 'squirrly-seo' ),
				'save'              => esc_html__( 'Add Link', 'squirrly-seo' ),
				'noTitle'           => esc_html__( '(no title)', 'squirrly-seo' ),
				'noMatchesFound'    => esc_html__( 'No matches found.', 'squirrly-seo' ),
				'linkSelected'      => esc_html__( 'Link selected.', 'squirrly-seo' ),
				'linkInserted'      => esc_html__( 'Link inserted.', 'squirrly-seo' ),
				'relCheckbox'       => __( 'Add <code>rel="nofollow"</code>', 'squirrly-seo' ),
				'sponsoredCheckbox' => __( 'Add <code>rel="sponsored"</code>', 'squirrly-seo' ),
				'linkTitle'         => esc_html__( 'Link Title', 'squirrly-seo' ),
			]
		);
	}

	/**
	 * Load the links in the block editor
	 * @return void
	 */
	public function wpLinkBlockEditor() {
		global $wp_version;

		if(version_compare( $wp_version, '6.1' ) >= 0){
			wp_enqueue_script( 'squirrly-editor', _SQ_ASSETS_URL_ . 'js/gutenberg.min.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-data', 'wp-plugins', 'wp-components', 'wp-edit-post', 'wp-api', 'wp-editor', 'wp-hooks', 'lodash' ), SQ_VERSION,false
			);
		}
	}
}
