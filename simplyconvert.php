<?php
/**
 * Plugin Name: SimplyConvert
 * Description: Easy install for SimplyConvert widgets
 * Author: SimplyConvert
 * Version: 1.1
 * Author URI: https://simplyconvert.com
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 *
 * SimplyConvert is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * SimplyConvert is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimplyConvert. If not, see <http://www.gnu.org/licenses/>.
 */

// initial setup tasks
function simplyconvert_activate() {
    add_option('simplyconvert_hash', '');
}
register_activation_hook(__FILE__, "simplyconvert_activate");

// function simplyconvert_deactivate() {

// }
// register_deactivation_hook(__FILE__, "simplyconvert_deactivate");

// cleanup plugin
function simplyconvert_uninstall() {
    delete_option('simplyconvert_hash');
}
register_uninstall_hook(__FILE__, "simplyconvert_uninstall");

// register custom options
function simplyconvert_settings_init() {
    // register a setting to contain the company hash from simplyconvert
    register_setting('simplyconvert_options', 'simplyconvert_hash');

    // register a new section in the simplyconvert page
    add_settings_section(
        'simplyconvert_section_setup',
        'Widget Setup',
        'simplyconvert_section_setup_callback',
        'simplyconvert_options'
    );

    // register a new field in the simplyconvert setup section in the simplyconvert page
    add_settings_field(
        'simplyconvert_hash_field',
        'Company ID',
        'simplyconvert_hash_field_callback',
        'simplyconvert_options',
        'simplyconvert_section_setup',
        array(
            'label_for' => 'simplyconvert_hash',
            'class' => '',
        )
    );
}
// register simplyconvert_settings_init to the admin_init action hook
add_action('admin_init', 'simplyconvert_settings_init');

function simplyconvert_section_setup_callback($args) {
    ?>
    <p id="<?php echo esc_attr($args['id']);?>"><?php esc_html_e("", 'simplyconvert_options');?></p>
    <?php
}

function simplyconvert_hash_field_callback($args) {
    // get the value of the setting
    $simplyconvert_hash = get_option('simplyconvert_hash');
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']);?>" name="<?php echo esc_attr($args['label_for']);?>" value="<?php echo $simplyconvert_hash;?>">
    <p class="description">
        <?php esc_html_e("Insert your company's unique ID", "simplyconvert_options");?>
    </p>
    <p class="description">
        <a href="https://dashboard.simplyconvert.com/app/index.php?template=account"><?php esc_html_e("View company ID in SimplyConvert dashboard", "simplyconvert_options");?></a>
    </p>
    <?php
}

function simplyconvert_options_page() {
    add_management_page(
        'SimplyConvert',
        'SimplyConvert',
        'manage_options',
        'simplyconvert',
        'simplyconvert_options_page_html'
    );
}
add_action('admin_menu', 'simplyconvert_options_page');

// html for settings page
function simplyconvert_options_page_html() {
    // check user capabilities
    if ( ! current_user_can('manage_options'))
        return;
    
    // add error/update messages
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of updated
        add_settings_error('simplyconvert_messages', 'simplyconvert_message', 'Settings Saved', 'updated');
    }
    settings_errors('simplyconvert_messages');

    ?>    
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title());?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for simplyconvert_options setting
            settings_fields('simplyconvert_options');
            // output setting sections and their fields
            do_settings_sections('simplyconvert_options');
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function simplyconvert_insert_embed_code() {
    $simplyconvert_hash = get_option('simplyconvert_hash');
    if ($simplyconvert_hash == '')
        return;

    wp_enqueue_script('simplyconvert_embed', plugins_url('simplyconvert_embed.js', __FILE__) );
    wp_add_inline_script('simplyconvert_embed', "var simplyconvert_hash='" . $simplyconvert_hash . "';", 'before');
}
add_action('wp_enqueue_scripts', 'simplyconvert_insert_embed_code');

function simplyconvert_embed_shortcode($atts=[], $content=null) {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

    $sc_atts = shortcode_atts(
        array(
            'id'=>'',
            'style'=>'height:100%;width:100%;min-height:500px;padding:15px;',
        ), $atts
    );

    if ($sc_atts['id'] == '')
        return $content;

    $content = '<!-- SimplyConvert Embedded Chat--> <div style="'. $sc_atts['style'] .'"><div class="_scembeddedchat" data-scembed="'. $sc_atts['id'] .'" ></div></div>';

    return $content;
}
add_shortcode('simplyconvert_embed', 'simplyconvert_embed_shortcode');