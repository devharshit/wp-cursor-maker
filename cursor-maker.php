<?php
/**
 * Plugin Name: WP Cursor Maker
 * Description: Customize your site's cursor with an image and display it on selected pages.
 * Version: 1.0
 * Author: harshitmshingala
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CursorMaker {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'wp_head', [ $this, 'output_custom_cursor_style' ] );
    }

    public function add_settings_page() {
        add_options_page(
            'Cursor Maker Settings',
            'Cursor Maker',
            'manage_options',
            'cursor-maker',
            [ $this, 'settings_page_html' ]
        );
    }

    public function register_settings() {
        register_setting( 'cursor_maker_settings', 'cursor_maker_image' );
        register_setting( 'cursor_maker_settings', 'cursor_maker_pages' );
        register_setting( 'cursor_maker_settings', 'cursor_maker_scope' );
    }

    public function settings_page_html() {
        $image = esc_url( get_option( 'cursor_maker_image' ) );
        $scope = get_option( 'cursor_maker_scope', 'selected' );
        $selected_pages = (array) get_option( 'cursor_maker_pages', [] );
        $pages = get_pages();
        ?>
        <div class="wrap">
            <h1>Cursor Maker Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'cursor_maker_settings' ); ?>
                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">Upload Cursor Image</th>
                        <td>
                            <input type="text" id="cursor_maker_image" name="cursor_maker_image" value="<?php echo $image; ?>" style="width: 60%;" />
                            <input type="button" class="button" id="cursor_maker_upload" value="Upload Image" />
                            <p class="description">Recommended: Transparent PNG, .cur or .gif. Size: ~32x32px</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Where to Apply Cursor?</th>
                        <td>
                            <label><input type="radio" name="cursor_maker_scope" value="entire" <?php checked( $scope, 'entire' ); ?> /> Entire Site</label><br>
                            <label><input type="radio" name="cursor_maker_scope" value="selected" <?php checked( $scope, 'selected' ); ?> /> Selected Pages Only</label>
                        </td>
                    </tr>

                    <tr valign="top" id="cursor_maker_page_select" style="<?php echo ($scope === 'selected') ? '' : 'display:none;'; ?>">
                        <th scope="row">Select Pages</th>
                        <td>
                            <select name="cursor_maker_pages[]" multiple style="width: 60%; height: 200px;">
                                <?php foreach ( $pages as $page ) : ?>
                                    <option value="<?php echo $page->ID; ?>" <?php selected( in_array( $page->ID, $selected_pages ) ); ?>>
                                        <?php echo esc_html( $page->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple pages.</p>
                        </td>
                    </tr>

                </table>

                <?php submit_button(); ?>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($){
            $('#cursor_maker_upload').click(function(e) {
                e.preventDefault();
                var image = wp.media({ title: 'Upload Cursor Image', multiple: false }).open()
                .on('select', function(){
                    var uploaded_image = image.state().get('selection').first().toJSON();
                    $('#cursor_maker_image').val(uploaded_image.url);
                });
            });

            $('input[name="cursor_maker_scope"]').change(function() {
                if ($(this).val() === 'selected') {
                    $('#cursor_maker_page_select').show();
                } else {
                    $('#cursor_maker_page_select').hide();
                }
            });
        });
        </script>
        <?php
    }

    public function output_custom_cursor_style() {
        if ( is_admin() ) return;

        $cursor_image = esc_url( get_option( 'cursor_maker_image' ) );
        $scope = get_option( 'cursor_maker_scope', 'selected' );
        $selected_pages = (array) get_option( 'cursor_maker_pages', [] );

        if ( ! $cursor_image ) return;

        if ( $scope === 'entire' || ( $scope === 'selected' && is_page( $selected_pages ) ) ) {
            echo "<style>body, * { cursor: url('$cursor_image'), auto !important; }</style>";
        }
    }
}

new CursorMaker();