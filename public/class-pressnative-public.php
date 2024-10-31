<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://pressnative.com
 * @since      1.0.0
 *
 * @package    PressNative
 * @subpackage Pressnative/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    PressNative
 * @subpackage Pressnative/public
 * @author     Abdullah Diaa <abdullah@pressnative.com>
 */
class Pressnative_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/***** Plugin specific Endpoints *******/

	/**
	 * Recursive sanitation for an array
	 * 
	 * @param $array
	 *
	 * @return mixed
	 */
	private function recursive_sanitize_text_field($array)
	{
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				$value = $this->recursive_sanitize_text_field($value);
			} else {
				if (is_int($value)) {
					$value = intval($value);
				} else {
					$value = sanitize_text_field($value);
				}
			}
		}

		return $array;
	}
	/**
	 *  GET /directory > get directory
	 *  Endpoint to view data of the the website in order to show in the native application
	 * 
	 *  @since    1.0.4
	 */

	public function rest_pressnative_get_directory_list()
	{
		register_rest_route('pressnative/v1', 'directory', array(
			'methods'  => 'GET',
			'callback' => array($this, 'rest_pressnative_get_directory_list_request'),
			'args' => array(
				'option' => array(
					'validate_callback' => function ($param, $request, $key) {
						return sanitize_text_field($param);
					}
				)
			)
		));
	}

	/**
	 *  GET /directory/page > extract page HTML
	 *  Endpoint to view data of the the website in order to show in the native application
	 * 
	 *  @since    1.0.4
	 */

	public function rest_pressnative_get_directory_page()
	{
		register_rest_route('pressnative/v1', 'directory/page', array(
			'methods'  => 'GET',
			'callback' => array($this, 'rest_pressnative_get_directory_page_request'),
			'args' => array(
				'option' => array(
					'validate_callback' => function ($param, $request, $key) {
						return sanitize_text_field($param);
					}
				)
			)
		));
	}

	/**
	 *  POST /upload > Upload JSON and images for directory support
	 *  Endpoint to upload json data for directory support
	 * 
	 *  @since    1.0.5
	 */

	public function rest_pressnative_upload_cached_data()
	{
		register_rest_route('pressnative/v1', 'upload', array(
			'methods'  => 'POST',
			'callback' => array($this, 'rest_pressnative_upload_cached_data_request')
		));
	}

	 /**
	 *  POST /flush_upload > Flush all data in pressnative directory
	 *  Endpoint to Flush all data in pressnative directory
	 * 
	 *  @since    1.0.6
	 */

	public function rest_pressnative_flush_upload_cached_data()
	{
		register_rest_route('pressnative/v1', 'flush_upload', array(
			'methods'  => 'POST',
			'callback' => array($this, 'rest_pressnative_flush_upload_cached_data_request')
		));
	}

	/**
	 *  Check if curl plugin is installed in PHP
	 * 
	 *  @since    1.0.4
	 */
	private function _is_curl_installed()
	{
		if (in_array('curl', get_loaded_extensions())) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Check if xml plugin is installed in PHP
	 * 
	 *  @since    1.0.4
	 */
	private function _is_xml_installed()
	{
		if (in_array('xml', get_loaded_extensions())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  GET /options > get plugin configuration
	 *  Endpoint to view data of the the website in order to show in the native application
	 * 
	 *  @since    1.0.0
	 */

	public function rest_pressnative_get_options()
	{
		register_rest_route('pressnative/v1', 'options', array(
			'methods'  => 'GET',
			'callback' => array($this, 'rest_pressnative_get_options_request'),
			'args' => array(
				'option' => array(
					'validate_callback' => function ($param, $request, $key) {
						return sanitize_text_field($param);
					}
				)
			)
		));
	}

	/**
	 *  GET /options > get plugin configuration
	 *  Endpoint to view data of the the website in order to show in the native application
	 * 
	 *  @since    1.0.7
	 */

	public function rest_pressnative_get_manifest()
	{
		register_rest_route('pressnative/v1', 'manifest', array(
			'methods'  => 'GET',
			'callback' => array($this, 'rest_pressnative_get_manifest_request'),
			'args' => array(
				'option' => array(
					'validate_callback' => function ($param, $request, $key) {
						return sanitize_text_field($param);
					}
				)
			)
		));
	}

	/**
	 *  
	 *  Add support for text/plain and text/html for json
	 * 
	 *  @since    1.0.6
	 */
	public static function pressnative_add_multiple_mime_types( $check, $file, $filename ) {
		$allowed_mimes = [
            [ 'json' => 'text/plain' ],
            [ 'json' =>  'text/html' ],
        ];
        if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
            foreach ( $allowed_mimes as $mime ) {
                remove_filter( 'wp_check_filetype_and_ext', [ $this, 'pressnative_add_multiple_mime_types' ], 99 );
                $mime_filter = function($mimes) use ($mime) {
                    return array_merge($mimes, $mime);
                };

                add_filter('upload_mimes', $mime_filter, 99);
                $check = wp_check_filetype_and_ext( $file, $filename, $mime );
                remove_filter('upload_mimes', $mime_filter, 99);
                add_filter( 'wp_check_filetype_and_ext', [ $this, 'pressnative_add_multiple_mime_types' ], 99, 3 );
                if ( ! empty( $check['ext'] ) || ! empty( $check['type'] ) ) {
                    return $check;
                }
            }
        }

        return $check;
    }

	/**
	 * Override the default upload path.
	 * 
	 * @param   array   $dir
	 * @return  array
	 * @since    1.0.6
	 */
	public function wpse_pressnative_upload_dir( $dir ) {
		return array(
			'path'   => $dir['basedir'] . '/pressnative',
			'url'    => $dir['baseurl'] . '/pressnative',
			'subdir' => '/pressnative',
		) + $dir;
	}

		/**
	 *  POST /upload > Upload JSON and images for directory support
	 *  Endpoint to upload json data for directory support
	 * 
	 *  @since    1.0.6
	 */

	public function rest_pressnative_flush_upload_cached_data_request(WP_REST_Request $request)
	{

		$appID = esc_attr(get_option('pressnative_appid'));
		$secret = esc_attr(get_option('pressnative_secret'));

		$submitted_app_id = sanitize_text_field($request->get_header("appID"));
		$submitted_app_secret = sanitize_text_field($request->get_header("secret"));

		if (!$appID || !$secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if (!$submitted_app_id || !$submitted_app_secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_id != $appID) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_secret != $secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		add_filter( 'upload_dir', array($this, 'wpse_pressnative_upload_dir' ));
		$upload_dir = wp_upload_dir();
		$dir_url = $upload_dir['path'];

		foreach(glob($dir_url. '/*.json') as $v){
			unlink($v);
		}
		remove_filter( 'upload_dir', array($this, 'wpse_pressnative_upload_dir' ));

		$response = new WP_REST_Response(["success" => true]);
		$response->set_status(200);
		return $response;
	}


	/**
	 *  POST /upload > Upload JSON and images for directory support
	 *  Endpoint to upload json data for directory support
	 * 
	 *  @since    1.0.5
	 */

	public function rest_pressnative_upload_cached_data_request(WP_REST_Request $request)
	{

		$appID = esc_attr(get_option('pressnative_appid'));
		$secret = esc_attr(get_option('pressnative_secret'));

		$submitted_app_id = sanitize_text_field($request->get_header("appID"));
		$submitted_app_secret = sanitize_text_field($request->get_header("secret"));

		if (!$appID || !$secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if (!$submitted_app_id || !$submitted_app_secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_id != $appID) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_secret != $secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}
        add_filter( 'wp_check_filetype_and_ext', array($this, 'pressnative_add_multiple_mime_types'), 99, 3 );

		// Prepare array for output
		$output = array();
		$files = $request->get_file_params();

		// These files need to be included as dependencies when on the front end.
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		// Process images
		if (!empty($files)) {

			foreach ($files as $key => $file) {
				$upload_overrides = array(
					'test_form' => false
				);
				add_filter( 'upload_dir', array($this, 'wpse_pressnative_upload_dir' ));
				$upload_dir = wp_upload_dir();
				$file_url = $upload_dir['path'] . "/". $file['name'];
				if(file_exists($file_url) && !is_dir($file_url)){
					unlink($file_url);
				}
				$movefile = wp_handle_upload($file,$upload_overrides);
				if ($movefile && ! isset( $movefile['error'] )) {
					// Success
					$output['status'] = 'success';
					$output['url'] = $movefile['url'];
					$output['message'] = 'File uploaded.';
				} else {
					$output['status'] = 'error';
					$output['message'] = $movefile['error'];
					return $output;
				}
			}
		}
		remove_filter('upload_dir', array($this, 'wpse_pressnative_upload_dir' ));

		return $output;
	}


	public function rest_pressnative_get_directory_page_request(WP_REST_Request $request)
	{

		if (!$this->_is_curl_installed()) {
			wp_send_json_error(["error" => "libcurl is not installed, cURL is required for directory feature"]);
			return;
		}

		if (!$this->_is_xml_installed()) {
			wp_send_json_error(["error" => "php-xml is not installed, xml is required for directory feature"]);
			return;
		}

		$url = esc_attr($request['url']);
		if ($url == "" || $url == null) {
			wp_send_json_error(["error" => "url is missing"]);
			return;
		}

		$title_xpath = $request['title_path'];
		$text_xpath = $request['text_path'];


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, urldecode($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$content = curl_exec($ch);
		curl_close($ch);

		$data = [
			'title' => '',
			'content' => ''
		];

		$dom = new DOMDocument;
		$dom->strictErrorChecking = false;
		@$dom->loadHTML($content);
		$xPath = new DOMXPath($dom);

		$nodes = $xPath->query($title_xpath);
		$data['title'] = $dom->saveHTML($nodes->item(0));

		$nodes2 = $xPath->query($text_xpath);
		$data['content'] = $dom->saveHTML($nodes2->item(0));

		$response = new WP_REST_Response($data);
		$response->set_status(200);

		return $response;
	}

	public function rest_pressnative_get_directory_list_request(WP_REST_Request $request)
	{

		if (!$this->_is_curl_installed()) {
			wp_send_json_error(["error" => "libcurl is not installed, cURL is required for directory feature"]);
			return;
		}

		if (!$this->_is_xml_installed()) {
			wp_send_json_error(["error" => "php-xml is not installed, xml is required for directory feature"]);
			return;
		}

		$url = esc_attr($request['url']);
		if ($url == "" || $url == null) {
			wp_send_json_error(["error" => "url is missing"]);
			return;
		}

		$top_xpath = $request['path'];
		$search_xpath = $request['spath'];
		$nested_xpath = $request['npath'];


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, urldecode($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$content = curl_exec($ch);
		curl_close($ch);

		$dom = new DOMDocument;
		$dom->strictErrorChecking = false;
		@$dom->loadHTML($content);
		$xPath = new DOMXPath($dom);
		$directory_items = [];
		$nodes = $xPath->query($top_xpath);
		$i = 0;
		foreach ($nodes as $node) {
			foreach ($xPath->query($search_xpath, $node) as $child) {
				$i++;
				$directory_items[$i] = [
					'title'	=> $child->nodeValue
				];
			}
		}
		$i = 0;
		foreach ($nodes as $node) {
			foreach ($xPath->query($nested_xpath, $node) as $child) {
				$i++;
				$directory_items[$i]['url'] = $child->nodeValue;
			}
		}

		$data = [];
		foreach ($directory_items as $item) {
			$data[] = $item;
		}


		$response = new WP_REST_Response($data);
		$response->set_status(200);

		return $response;
	}
	

	public function rest_pressnative_get_manifest_request(WP_REST_Request $request)
	{
		$short_name = get_bloginfo( 'name' );
		if ( strlen( $short_name ) > 12 ) {
			$short_name = substr( $short_name, 0, 9 ) . '...';
		}

		$related_apps = [];
		$prefer_related_applications = false;

		if(get_option('pressnative_ios_app') !== null){
			if(get_option('pressnative_ios_app') !== ""){
				$prefer_related_applications = true;
				$related_apps[] = [
					"platform" => "itunes",
					"id" => get_option('pressnative_ios_app')];
			}
		}

		if(get_option('pressnative_android_app') !== null){
			if(get_option('pressnative_android_app') !== ""){
				$prefer_related_applications = true;
				$related_apps[] = [
					"platform" => "play",
					"id" => get_option('pressnative_android_app')];
			}
		}
		

		$manifest = [
			"display" =>  "fullscreen",
			'start_url' => get_site_url(),
			'name'	=> get_bloginfo( 'name' ),
			'short_name' => $short_name,
			'dir'	=> is_rtl() ? 'rtl' : 'ltr',
			'prefer_related_applications' => $prefer_related_applications,
			"related_applications" => $related_apps,
			'description'	=> get_bloginfo( 'description' )
		];

		$sizes = [ 144, 192, 512 ];
		if ( has_site_icon()){
			foreach ( $sizes as $size ) {
				$icon = get_site_icon_url( $size );
				if($icon){
					$manifest['icons'][] = [
						'src'   => get_site_icon_url( $size ),
						'sizes' => "{$size}x{$size}",
						'type'  => 'image/png',
					];
				}
			}
		}

		$language = get_bloginfo( 'language' );
		if ( $language ) {
			$manifest['lang'] = $language;
		}

		$response = new WP_REST_Response($manifest);
		$response->set_status(200);

		return $response;
	}
	public function rest_pressnative_get_options_request(WP_REST_Request $request)
	{

		//sanitize all categories fields either text or int
		$cats_top = $this->recursive_sanitize_text_field(get_option("pressnative_cats_top", $this->get_cats(8)));
		$cats_push = $this->recursive_sanitize_text_field(get_option("pressnative_cats_push", $this->get_cats(3)));
		$cats_sidebar = $this->recursive_sanitize_text_field(get_option("pressnative_cats_sidebar", $this->get_cats(10)));
		$settings = $this->recursive_sanitize_text_field(get_option("pressnative_settings", []));
		$socialmedia = $this->recursive_sanitize_text_field(get_option("pressnative_socialmedia", []));

		//Escape HTML/URLs, bools and integers
		//options of the plugin for the native application
		$options = [
			"title" => esc_attr(get_option("pressnative_title", get_bloginfo('name'))),
			"privacy" =>  esc_url_raw(get_option("pressnative_privacy", "")),
			"terms" =>  esc_url_raw(get_option("pressnative_terms", "")),
			//settings list
			"settings" =>  $settings,
			//Categories in side bar
			"cats_sidebar" => $cats_sidebar,
			//Categories in top bar in home page
			"cats_top" =>  $cats_top,
			//Categories in Push tab
			"cats_push" => $cats_push,
			//Emphasis on first article 
			"top_article" => boolval(get_option("pressnative_top_article", "true") === 'true' ? true : false),
			"show_author" => boolval(get_option("pressnative_show_author", "true") === 'true' ? true : false),
			"show_reading_time" => boolval(get_option("pressnative_show_reading_time", "true") === 'true' ? true : false),
			"show_likes" => boolval(get_option("pressnative_show_likes", "true") === 'true' ? true : false),
			"premium" => boolval(get_option("pressnative_premium", "false") === 'true' ? true : false),
			"show_audio" => boolval(get_option("pressnative_show_audio", "false") === 'true' ? true : false),
			"show_onboarding" => boolval(get_option("pressnative_show_onboarding", "true") === 'true' ? true : false),
			"premium_category_id"  => intval(get_option("pressnative_premium_category_id")),
			//social media
			"socialmedia" => $socialmedia
		];

		$response = new WP_REST_Response($options);
		$response->set_status(200);

		return $response;
	}

	/**
	 *  /update	> Update plugin data
	 *  Endpoint to update fields of the website
	 * 
	 *  @since    1.0.0
	 */

	public function rest_pressnative_update_options()
	{
		register_rest_route('pressnative/v1', 'update', array(
			'methods'  => 'POST',
			'callback' => array($this, 'rest_pressnative_update_options_request')
		));
	}

	public function rest_pressnative_update_options_request(WP_REST_Request $request)
	{
		$appID = esc_attr(get_option('pressnative_appid'));
		$secret = esc_attr(get_option('pressnative_secret'));

		$submitted_app_id = sanitize_text_field($request->get_header("appID"));
		$submitted_app_secret = sanitize_text_field($request->get_header("secret"));

		if (!$appID || !$secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if (!$submitted_app_id || !$submitted_app_secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_id != $appID) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_secret != $secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		$parameters = $request->get_json_params();

		if (is_bool($parameters['top_article'])) {
			update_option('pressnative_top_article', boolval($parameters['top_article']) ? "true" : "false");
		}

		if (is_bool($parameters['show_reading_time'])) {
			update_option('pressnative_show_reading_time', boolval($parameters['show_reading_time']) ? "true" : "false");
		}


		if (is_bool($parameters['show_author'])) {
			update_option('pressnative_show_author', boolval($parameters['show_author']) ? "true" : "false");
		}

		if (is_bool($parameters['show_likes'])) {
			update_option('pressnative_show_likes', boolval($parameters['show_likes']) ? "true" : "false");
		}

		if (is_bool($parameters['premium'])) {
			update_option('pressnative_premium', boolval($parameters['premium']) ? "true" : "false");
		}

		if (is_bool($parameters['show_audio'])) {
			update_option('pressnative_show_audio', boolval($parameters['show_audio']) ? "true" : "false");
		}

		if (is_bool($parameters['show_onboarding'])) {
			update_option('pressnative_show_onboarding', boolval($parameters['show_onboarding']) ? "true" : "false");
		}

		if (is_string($parameters['title'])) {
			update_option('pressnative_title', sanitize_text_field($parameters['title']));
		}

		if (is_string($parameters['privacy'])) {
			update_option('pressnative_privacy', esc_url_raw($parameters['privacy']));
		}

		if (is_string($parameters['ios_app'])) {
			update_option('pressnative_ios_app', sanitize_text_field($parameters['ios_app']));
		}

		if (is_string($parameters['android_app'])) {
			update_option('pressnative_android_app', sanitize_text_field($parameters['android_app']));
		}

		if (is_string($parameters['terms'])) {
			update_option('pressnative_terms', esc_url_raw($parameters['terms']));
		}

		if (is_int($parameters['premium_category_id'])) {
			update_option('pressnative_premium_category_id', intval($parameters['premium_category_id']));
		}

		if ($parameters['settings']) {
			update_option('pressnative_settings', $this->recursive_sanitize_text_field($parameters['settings']));
		}

		if ($parameters['socialmedia']) {
			update_option('pressnative_socialmedia', $this->recursive_sanitize_text_field($parameters['socialmedia']));
		}

		if ($parameters['cats_push']) {
			update_option('pressnative_cats_push', $this->recursive_sanitize_text_field($parameters['cats_push']));
		}

		if ($parameters['cats_sidebar']) {
			update_option('pressnative_cats_sidebar', $this->recursive_sanitize_text_field($parameters['cats_sidebar']));
		}

		if ($parameters['cats_top']) {
			update_option('pressnative_cats_top', $this->recursive_sanitize_text_field($parameters['cats_top']));
		}

		if (is_string($parameters['push_notifications_appid'])) {
			update_option('pressnative_push_appid', sanitize_text_field($parameters['push_notifications_appid']));
		}

		if (is_string($parameters['push_notifications_secret'])) {
			update_option('pressnative_push_secret', sanitize_text_field($parameters['push_notifications_secret']));
		}

		$response = new WP_REST_Response(["success" => true]);
		$response->set_status(200);

		return $response;
	}

	/**
	 *  /check_credentails > check appID and app secret
	 *  Endpoint to authenticate requests to update fields using APPID and APPSECRET
	 * 
	 *  @since    1.0.0
	 */

	public function rest_pressnative_check_credentials()
	{
		register_rest_route('pressnative/v1', 'check', array(
			'methods'  => 'POST',
			'callback' => array($this, 'rest_pressnative_check_credentials_request')
		));
	}

	public function rest_pressnative_check_credentials_request(WP_REST_Request $request)
	{
		$appID = esc_attr(get_option('pressnative_appid'));
		$secret = esc_attr(get_option('pressnative_secret'));

		$submitted_app_id = sanitize_text_field($request->get_header("appID"));
		$submitted_app_secret = sanitize_text_field($request->get_header("secret"));


		if (!$submitted_app_id || !$submitted_app_secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if (!$submitted_app_id || !$submitted_app_secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_id != $appID) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}

		if ($submitted_app_secret != $secret) {
			$response = new WP_REST_Response(["success" => false]);
			$response->set_status(200);

			return $response;
		}


		$response = new WP_REST_Response(["success" => true]);
		$response->set_status(200);

		return $response;
	}


	/**
	 *  Get a certain number of top categories with count and not empty 
	 * 
	 *  @since    1.0.0
	 */

	private function get_cats($count)
	{
		$cats = get_categories([
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $count,
			'hide_empty' => 1,
		]);

		$categories = [];

		foreach ($cats as $category) {
			array_push($categories, [
				'id' => $category->term_id,
				'name' => $category->name
			]);
		}
		return $categories;
	}


	/**
	 * Register likes and dislikes of posts using admin-ajax
	 *
	 * @since    1.0.0
	 */
	public function pressnative_likes_dislikes()
	{
		$json = array();

		$post_type = sanitize_text_field($_POST['type']);
		$post_id_posted = intval($_POST['postId']);

		if (!is_int($post_id_posted)) {
			$json['message'] = 'postId must be an integer value';
			wp_send_json_error($json);
		}

		if (!in_array($post_type, ["increment", "decrement", "likes"])) {
			$json['message'] = 'post_type must be either icrement or decrement or likes';
			wp_send_json_error($json);
		}


		if (isset($post_id_posted) && isset($post_type)) {
			$post_id = $post_id_posted;
			$updateType = $post_type;
			$post_like_posts = get_post_meta($post_id, 'pressnative_post_likes_counter', true);
			$total_likes = 0;

			if ($updateType == "increment") { //increment in post like
				if ($post_like_posts == "") {
					add_post_meta($post_id, 'pressnative_post_likes_counter', '1');
					$total_likes = 1;
				} else {
					$post_like_posts = $post_like_posts + 1;
					$total_likes = $post_like_posts;
					update_post_meta($post_id, 'pressnative_post_likes_counter', $post_like_posts);
				}
			} elseif ($updateType == "decrement") { //increment in post dislike
				if ($post_like_posts == "") {
					add_post_meta($post_id, 'pressnative_post_likes_counter', '0');
				} else {
					if ($post_like_posts != '0') {
						$post_like_posts = $post_like_posts - 1;
						$total_likes = $post_like_posts;
						update_post_meta($post_id, 'pressnative_post_likes_counter', $post_like_posts);
					}
				}
			} elseif ($updateType == "likes") {
				if ($post_like_posts == "") {
					$total_likes = 0;
				} else {
					$total_likes = $post_like_posts;
				}
			}

			$json['message'] = 'Success!';
			$json['likes'] = $total_likes;
			wp_send_json_success($json);
		} else {
			$json['message'] = 'postId and updateType must be set';
			wp_send_json_error($json);
		}
	}



	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pressnative_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pressnative_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pressnative-public.css', array(), $this->version, 'all');
	}


	/**
	 * Register post metas
	 *
	 * @since    1.0.7
	 */
	public function pressnative_apps_meta_tags()
	{
		if(get_option('pressnative_ios_app') !== null){
			if(get_option('pressnative_ios_app') !== ""){
				?>
				<meta name="apple-itunes-app" content="app-id=<?php echo get_option('pressnative_ios_app') ?>" />
				<link rel="manifest" href="/wp-json/pressnative/v1/manifest">
				<?php
			}
		}

	}

	/**
	 * Register post metas
	 *
	 * @since    1.0.0
	 */
	public function pressnative_register_post_metas()
	{
		$meta_args = array(
			'type'         => 'string',
			'description'  => 'PressNative likes counter for the mobile app',
			'single'       => true,
			'show_in_rest' => true,
		);
		register_post_meta('post', 'pressnative_post_likes_counter', $meta_args);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pressnative_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pressnative_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pressnative-public.js', array('jquery'), $this->version, false);
	}
}
