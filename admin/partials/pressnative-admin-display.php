<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://pressnative.com
 * @since      1.0.0
 *
 * @package    PressNative
 * @subpackage Pressnative/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <?php settings_errors(); ?>

    <h1 class="wp-heading-inline"><?php esc_html_e('PressNative Settings', 'pressnative'); ?></h1>

    <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post">
        <?php settings_fields('pressnative_general'); ?>
        <?php do_settings_sections('pressnative_general'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">PressNative Application ID:</th>
                <td><input type="text" name="pressnative_appid" size="38" value="<?php echo esc_attr(get_option('pressnative_appid')); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">PressNative secret:</th>
                <td><input type="password" name="pressnative_secret" size="65" value="<?php echo esc_attr(get_option('pressnative_secret')); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Appstore ID:</th>
                <td><input type="text" name="pressnative_ios_app" size="65" value="<?php echo esc_attr(get_option('pressnative_ios_app')); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Playstore ID:</th>
                <td><input type="text" name="pressnative_android_app" size="65" value="<?php echo esc_attr(get_option('pressnative_android_app')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

</div>