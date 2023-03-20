<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQ_Controllers_Api extends SQ_Classes_FrontController
{

    /**
     * 
     *
     * @var string token local key 
     */
    private $token;
    /**
     * REST namespace.
     *
     * @var string
     */
    private $namespace = 'squirrly';

    public function __construct() {
        parent::__construct();


    }

    /**
     * Return Squirrly API URL
     */
    public function getApiUrl(){
        return trailingslashit(get_rest_url()) . $this->namespace . '/';
    }

    /**
     * Initialize the TinyMCE editor for the current use
     *
     * @return void
     */
    public function hookInit()
    {

        if (SQ_Classes_Helpers_Tools::getOption('sq_api') == '')
            return;

        if (!SQ_Classes_Helpers_Tools::getOption('sq_cloud_connect'))
            return;

        $this->token = SQ_Classes_Helpers_Tools::getOption('sq_cloud_token');

        //Change the rest api if needed
        add_action('rest_api_init', array($this, 'sqApiInit'));
    }


    function sqApiInit()
    {
        if (function_exists('register_rest_route')) {

            register_rest_route(
                $this->namespace, '/indexnow/', array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'indexUrl'),
                    'permission_callback' => '__return_true'
                )
            );

            register_rest_route(
                $this->namespace, '/save/', array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'savePost'),
                    'permission_callback' => '__return_true'
                )
            );

            register_rest_route(
                $this->namespace, '/get/', array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'getData'),
                    'permission_callback' => '__return_true'
                )
            );

            register_rest_route(
                $this->namespace, '/test/', array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'testConnection'),
                    'permission_callback' => '__return_true'
                )
            );


            // load deprecate API for compatibility
            $this->deprecateRest();
        }
    }

    /*
     * Deprecate since version 12.1.10
     */
    public function deprecateRest(){
        register_rest_route(
            'save', '/squirrly/', array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'savePost'),
                'permission_callback' => '__return_true'
            )
        );

        register_rest_route(
            'test', '/squirrly/', array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'testConnection'),
                'permission_callback' => '__return_true'
            )
        );

        register_rest_route(
            'get', '/squirrly/', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'getData'),
                'permission_callback' => '__return_true'
            )
        );
    }

    /**
     * Indexnow the URLs
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function indexUrl(WP_REST_Request $request){
        $urls = $request->get_param( 'urls' );

        if ( empty( $urls ) ) {
            return new WP_Error( 'empty_urls', __( 'No URLs found. Please add URLs to index.', 'squirrly-seo' ) );
        }

        $urls = array_values( array_filter( array_map( 'trim', explode( "\n", $urls ) ) ) );
        $urls = array_values( array_unique( array_filter( $urls, 'wp_http_validate_url' ) ) );

        if ( ! $urls ) {
            return new WP_Error( 'invalid_urls', __( 'Invalid URLs provided. Please add valid URLs.', 'squirrly-seo' ) );
        }

        $result = SQ_Classes_ObjController::getClass('SQ_Models_Indexnow')->submitUrl( $urls );
        if ( ! $result ) {
            return new WP_Error( 'submit_failed', __( 'Failed to submit the URLs. Please try again.', 'squirrly-seo' ) );
        }

        $urls_number = count( $urls );
        return new WP_REST_Response(
            [
                'success' => true,
                'message' => sprintf(
                    _n(
                        'Successfully submitted %s URL.',
                        'Successfully submitted %s URLs.',
                        $urls_number,
                        'squirrly-seo'
                    ),
                    $urls_number
                ),
            ]
        );
    }

    /**
     * Test the connection
     *
     * @param WP_REST_Request $request Full details about the request.
     */
    public function testConnection(WP_REST_Request $request)
    {
        SQ_Classes_Helpers_Tools::setHeader('json');

        //get the token from API
        $token = $request->get_param('token');
        if ($token <> '') {
            $token = sanitize_text_field($token);
        }

        if (!$this->token || $this->token <> $token) {
            exit(wp_json_encode(array('connected' => false, 'error' => esc_html__("Invalid Token. Please try again", 'squirrly-seo'))));
        }

        echo wp_json_encode(array('connected' => true, 'error' => false));
        exit();
    }

    /**
     * Save the Post
     *
     * @param WP_REST_Request $request Full details about the request.
     */
    public function savePost(WP_REST_Request $request)
    {
        SQ_Classes_Helpers_Tools::setHeader('json');

        //get the token from API
        $token = $request->get_param('token');
        if ($token <> '') {
            $token = sanitize_text_field($token);
        }

        if (!$this->token || $this->token <> $token) {
            exit(wp_json_encode(array('error' => esc_html__("Connection expired. Please try again", 'squirrly-seo'))));
        }

        $post = $request->get_param('post');
        if ($post = json_decode($post)) {
            if (isset($post->ID) && $post->ID > 0) {
                $post = new WP_Post($post);
                $post->ID = 0;
                if (isset($post->post_author)) {
                    if (is_email($post->post_author)) {
                        if ($user = get_user_by('email', $post->post_author)) {
                            $post->post_author = $user->ID;
                        } else {
                            exit(wp_json_encode(array('error' => esc_html__("Author not found", 'squirrly-seo'))));
                        }
                    } else {
                        exit(wp_json_encode(array('error' => esc_html__("Author not found", 'squirrly-seo'))));
                    }
                } else {
                    exit(wp_json_encode(array('error' => esc_html__("Author not found", 'squirrly-seo'))));
                }

                $post_ID = wp_insert_post($post->to_array());
                if (is_wp_error($post_ID)) {
                    echo wp_json_encode(array('error' => $post_ID->get_error_message()));
                } else {
                    echo wp_json_encode(array('saved' => true, 'post_ID' => $post_ID, 'permalink' => get_permalink($post_ID)));
                }
                exit();
            }
        }
        echo wp_json_encode(array('error' => true));
        exit();
    }

    /**
     * Get data for the Focus Page Audit
     *
     * @param \WP_REST_Request $request
     */
    public function getData(WP_REST_Request $request)
    {

        global $wpdb;
        $response = array();
        SQ_Classes_Helpers_Tools::setHeader('json');

        //get the token from API
        $token = $request->get_param('token');
        if ($token <> '') {
            $token = sanitize_text_field($token);
        }

        if (!$this->token || $this->token <> $token) {
            exit(wp_json_encode(array('error' => esc_html__("Connection expired. Please try again.", 'squirrly-seo'))));
        }

        $select = $request->get_param('select');


        switch ($select) {
        case 'innerlinks':
            $url = $request->get_param('url');
            if ($url == '') {
                exit(wp_json_encode(array('error' => esc_html__("Wrong Params", 'squirrly-seo'))));
            }

            //get post inner links
            $total_posts = 0;
            $inner_links = array();
            if ($row = $wpdb->get_row($wpdb->prepare("SELECT COUNT(`ID`) as count FROM `$wpdb->posts` WHERE `post_status` = %s", 'publish'))) {
                $total_posts = $row->count;
            }

            if ($rows = $wpdb->get_results($wpdb->prepare("SELECT `ID` FROM `$wpdb->posts` WHERE (`post_content` LIKE '%%%s%' OR `post_content` LIKE '%%%s%' OR `post_content` LIKE '%%%s%') AND `post_status` = %s", $url, str_replace('/','\/',$url), urlencode($url), 'publish'), OBJECT)) {
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->setPostByID($row->ID);
                        if ($post->sq->nositemap || !$post->sq->do_sitemap) {
                            continue;
                        }
                        $inner_links[] = $post->url;
                    }
                }
            }

            if ($rows = $wpdb->get_results($wpdb->prepare("SELECT `post_id` FROM `$wpdb->postmeta` WHERE `meta_value` LIKE '%%%s%'", $url), OBJECT)) {
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->setPostByID($row->post_id);
                        if ($post->sq->nositemap || !$post->sq->do_sitemap || $post->post_status <> 'publish') {
                            continue;
                        }
                        $inner_links[] = $post->url;
                    }
                }
            }

            $response = array('url' => $url, 'total_posts' => $total_posts, 'inner_links' => $inner_links);
            break;
        case 'post':
            $url = $request->get_param('url');
            if ($url == '') {
                exit(wp_json_encode(array('error' => esc_html__("Wrong Params", 'squirrly-seo'))));
            }
            //get Squirrly SEO post metas
            if ($post = SQ_Classes_ObjController::getClass('SQ_Models_Snippet')->setPostByURL($url)) {
                $response = $post->toArray();
            }

            break;
        case 'squirrly':
            //Get Squirrly settings
            if ($options = SQ_Classes_Helpers_Tools::getOptions()) {
                $response = (array)$options;
            }

            break;
        }
        echo wp_json_encode($response);

        exit();

    }
}
