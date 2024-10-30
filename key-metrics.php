<?php 

/**
 * @link              https://www.speedplussecurity.com 
 * @since             1.0
 * @package           Key Metrics
 *
 * @wordpress-plugin
 * Plugin Name:       Core Vitals Monitor 
 * Description:       Tests performance metrics (security and performance) on- a periodic schedule 
 * Version:           1.0
 * Author:            Idris Adesina
 * Author URI:        https://www.speedplussecurity.com   
 * Text Domain:       key_metrics
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * WordPress Available:  yes
 */


define( 'CORE_VITALS_MONITOR_VERSION', '1.0.7' );
define('CORE_VITALS_MONITOR_PLUGIN_NAME',plugin_basename(__FILE__));
define('CORE_VITALS_MONITOR_PLUGIN_URL',plugins_url('',__FILE__));
define('CORE_VITALS_MONITOR_PLUGIN_DIR',dirname(__FILE__));
$cvm_metrics_class;

// $_SESSION["key_metrics_session"] = "alive";

register_activation_hook(__FILE__, 'cvm_key_metrics_activate_plugin');
register_deactivation_hook(__FILE__, 'cvm_key_metrics_deactivate_plugin');


if (!function_exists("cvm_key_metrics_activate_plugin")) {
    function cvm_key_metrics_activate_plugin()
    { // runs on plugin activation
        if (!wp_next_scheduled('key_metrics_refresh_data')) {
            $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR. '/res/subs/sites_list.json');
            $sites = json_decode($jsonString, true);
            
            $interval = sanitize_text_field( $sites["config"]['update_freq'] );
            $pref_int = ["twicedaily", "daily", "weekly", "fortnightly", "monthly"];
            $interval = array_search($interval,$pref_int) == false ? 'weekly' : $interval;


            if($sites['platform'] == ''){
                $sites['platform'] = sanitize_title($_SERVER['SERVER_NAME']);
                $sites["access"]["ambestdate"] = time();
                $sites["access"]["serverdate"] = date("Y-m-d h:ia",time());
                
                $home_url = sanitize_url( site_url() );
                $def = ["name" => "home","url" => $home_url];
                array_unshift( $sites["all_sites"] , $def);
                $newJsonString = json_encode($sites);
                file_put_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_list.json', $newJsonString);
            }
            add_filter( 'cron_schedules', 'cvm_key_metrics_d_custom_cron_schedule' );
            wp_schedule_event(time(), ''.$interval, 'key_metrics_refresh_data'); // plugin_cron_refresh_cache is a hook
        }
    };

}

if (!function_exists("cvm_key_metrics_d_custom_cron_schedule")) {

    function cvm_key_metrics_d_custom_cron_schedule( $schedules ) {
        $schedules['weekly'] = array(
            'interval' => 7 * 24 * 60 * 60, 
            'display'  => __( 'Once Weekly' ),
        );
        $schedules['fortnightly'] = array(
            'interval' => 2 * 7 * 24 * 60 * 60, 
            'display'  => __( 'Every Two Weeks' ),);
        $schedules['monthly'] = array(
            'interval' => 30 * 24 * 60 * 60, 
            'display'  => __( 'Monthly' ),
        );
        return $schedules;
    }
    
}

if (!function_exists("cvm_key_metrics_deactivate_plugin")) {

    function cvm_key_metrics_deactivate_plugin()
    { // runs on plugin deactivation
        if (wp_next_scheduled('key_metrics_refresh_data')) {
            wp_clear_scheduled_hook('key_metrics_refresh_data');
        }
    }

}


require __DIR__ . '/includes/class-key-metrics.php';

if (!function_exists("run_cvm_key_metrics")) {

    function run_cvm_key_metrics()
    {
        global $cvm_metrics_class;
        $cvm_metrics_class = new CVM_key_metrics();

        $GLOBALS['key_metrics_plugin'] = $cvm_metrics_class;
    }

}
run_cvm_key_metrics();

include_once(__DIR__ . "/includes/refresh-metrics.php");


?>