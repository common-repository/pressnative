<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pressnative.com
 * @since      1.0.0
 *
 * @package    PressNative
 * @subpackage Pressnative/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PressNative
 * @subpackage Pressnative/admin
 * @author     Abdullah Diaa <abdullah@pressnative.com>
 */
class Pressnative_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pressnative-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pressnative-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Adds menu page of pressnative
	 *
	 * @since    1.0.0
	 */

	public function add_pressnative_to_adminbar($wp_admin_bar)
	{

		add_menu_page(
			esc_html__('PressNative Settings', 'pressnative'),
			esc_html__('PressNative', 'pressnative'),
			'manage_options',
			$this->plugin_name,
			array($this, 'load_admin_page_content'), // Calls function to require the partial
			'dashicons-smartphone',
			990000
		);
	}

	/**
	 * Add checkbox to send notifications checked by default
	 *
	 * @since    1.0.4
	 */

	public function pressnative_article_push_checkbox()
	{
		global $post;
		/* check if this is a post, if not then we won't add the custom field */
		/* change this post type to any type you want to add the custom field to */
		if (get_post_type($post) != 'post') return false;
		/* get the value corrent value of the custom field */
?>
		<div class="misc-pub-section">
			<label><input type="checkbox" name="pressnative_send_notification" id="pressnative_send_notification" checked /> Notify mobile users</label>
		</div>
<?php
	}

	/**
	 * Add checkbox to send notifications checked by default
	 *
	 * @since    1.0.4
	 */
	public function pressnative_cd_meta_box_add()
	{

		$appID =  get_option("pressnative_push_appid");
		$appSecret =  get_option("pressnative_push_secret");
		if ($appID  && $appSecret) {
			add_meta_box('pb-box', 'Push Notification', array($this, 'pressnative_article_push_checkbox'), 'post', 'side', 'high');
		}
	}

	public function decode_entities($string)
	{
		$HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
		if (defined('ENT_HTML401')) {
			$HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
		}
		return html_entity_decode(str_replace("&apos;", "'", $string), $HTML_ENTITY_DECODE_FLAGS, 'UTF-8');
	}


	/**
	 * Broadcast push notification to all users
	 *
	 * @since    1.0.4
	 */
	function pressnative_send_article_notification($new_status, $old_status, $post)
	{

		if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ('publish' !== $new_status || 'publish' !== $old_status)
			return;

		/* store the value in the database */
		$appID =  get_option("pressnative_push_appid");
		$appSecret =  get_option("pressnative_push_secret");
		if (!$appID  || !$appSecret) {
			return;
		}

		$postid = $post->ID;
		if (empty($postid)) {
			return;
		}

		$title = $this->decode_entities(get_the_title($postid));

		if (isset($_POST['pressnative_send_notification']) && !get_transient("pressnative_send_push_after_publish_delay")) {

			set_transient('pressnative_send_push_after_publish_delay', true, 20);

			$posttype = get_post_type($post);

			if (in_array($posttype, array('post'))) {
				$permalink = get_permalink($postid);

				$args = array(
					"post_id" => $postid
				);

				if (has_post_thumbnail($postid)) {
					$image = wp_get_attachment_image_src(get_post_thumbnail_id($postid), 'pb_notification', true);
					if ($image[0] != '') {
						$args['mutableContent'] = true;
						$args['attachment-url'] = $image[0];
						$args['bigPicture'] = $image[0];
					}
				}

				$msg_data = array(
					'message' =>
					array(
						0 =>
						array(
							'language' => 'en',
							"title" => get_bloginfo('name'),
							'body' => $title
						),
					),
					'payload' => $args,
					'platforms' => [0, 1],
					'source' => 'wp_api',
				);

				if ($title != "" && $permalink != "") {
					wp_remote_post("https://api.pushbots.com/3/push/campaign", array(
						'method'      => 'POST',
						'blocking'    => true,
						'timeout'     => 20, 
						'httpversion' => '1.0',
    					'redirection' => 5,
						'headers'     => array(
							"x-pushbots-appid" => $appID,
							"x-pushbots-secret" =>  $appSecret,
							"Content-Type" => "application/json"
						),
						'body'        => json_encode($msg_data)
					));
				}
			}
		}
	}

	/**
	 * Register option settings of the plugin
	 *
	 * @since    1.0.0
	 */

	public function pressnative_register_settings()
	{

		$subset_option = [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
			'default'           => ''
		];
		register_setting('pressnative_general', 'pressnative_appid', $subset_option);
		register_setting('pressnative_general', 'pressnative_secret', $subset_option);
		register_setting( 'pressnative_general', 'pressnative_ios_app', $subset_option );
		register_setting( 'pressnative_general', 'pressnative_android_app', $subset_option );
		
	}

	// Load the plugin admin page partial.
	public function load_admin_page_content()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		require_once plugin_dir_path(__FILE__) .  "partials/$this->plugin_name-admin-display.php";
	}
}
