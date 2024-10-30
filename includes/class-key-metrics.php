<?php 


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
if (!defined('CORE_VITALS_MONITOR_PLUGIN_DIR')){
    die;
}

include_once(CORE_VITALS_MONITOR_PLUGIN_DIR . "/includes/tools-render.php");

class CVM_key_metrics {

	protected $plugin_name;

	protected $version;


	public function __construct()
    {
        $this->version = CORE_VITALS_MONITOR_VERSION;

		$this->plugin_name = CORE_VITALS_MONITOR_PLUGIN_NAME;

		//Load dependent files
        $this->load_dependencies();

        //A flag to determine whether plugin had been initialized
        $this->define_admin_hook();
        //Add ajax hook
        $this->load_ajax_hook_for_admin();


	}

    

	public function load_dependencies()
    {
        // include_once(CORE_VITALS_MONITOR_PLUGIN_DIR . "/includes/tools-render.php");
        // include_once(CORE_VITALS_MONITOR_PLUGIN_DIR . "/includes/refresh-metrics.php");
	}


    public function define_admin_hook()
    {
        add_action('admin_menu', array($this, 'key_metrics_menu') );
        add_action('key_metrics_refresh_data', 'refresh_key_metrics');
        //Display admin notices 
        add_action('admin_notices', array($this, 'cvm_key_metrics_notice') );
        add_filter('plugin_action_links_core-vitals-monitor/key-metrics.php', array($this, 'cvm_key_metrics_link_gen') );



        add_action( 'admin_enqueue_scripts', function($hook) {
            $page = sanitize_text_field( $_GET['page'] );
            if ( !in_array($hook, array("tools.php", "options-general.php")) && $page != "key_metrics" )
                return;
            $this -> add_key_metrics_cssjs_files();
        } );


    }

    public function load_ajax_hook_for_admin()
    {
        add_action( 'wp_ajax_km_dba86c5b66802692309f2059adc4', array($this, 'key_metrics_ajax_handler_rkm' ) );
        add_action( 'wp_ajax_km_92309f2059adc4dba86c5b668026', array($this, 'key_metrics_ajax_handler_skm' ) );
    }

    public function key_metrics_ajax_handler_skm()
    {
        try {
            $v = (save_key_metrics());
            wp_send_json_success( $v );
        } catch (\Throwable $th) {wp_send_json_success( false );}
    }

    public function key_metrics_ajax_handler_rkm()
    {
        try {
            $v = (refresh_key_metrics());
            wp_send_json_success( $v );
        } catch (\Throwable $th) {wp_send_json_success( false );}
    }

    public function add_key_metrics_cssjs_files(){
        wp_enqueue_script('key_metrics_script', CORE_VITALS_MONITOR_PLUGIN_URL."/res/cvm_actions.js",CORE_VITALS_MONITOR_VERSION);
        wp_enqueue_style( 'key_metrics_style', CORE_VITALS_MONITOR_PLUGIN_URL."/res/cvm_styles.css",CORE_VITALS_MONITOR_VERSION);
    }

    public function cvm_key_metrics_link_gen($links)
    {
        // Build and escape the URL.
        $url = esc_url(add_query_arg(
            'page',
            'key_metrics#key_metrics_url_settings',
            get_admin_url() . 'tools.php'
        ));
        add_thickbox();
        
        // Create the link.
        $settings_link = "<a href='$url'>" . 'Settings' . '</a>';
        $docs_link = '<a href="https://speedplussecurity.com/core-vitals-monitor?TB_iframe=true&width=600&height=550" class="thickbox">Docs</a>';
        array_push(
            $links,$docs_link,
            $settings_link
        );
        return $links;
    } //end nc_settings_link()

    public function key_metrics_get_text_suffix($val)
    {
        if ($val >= 0 && $val != '-') {
            return $val . '%';
        } else
            return $val;
    }

    public function key_metrics_get_text_class($val, $type)
    {
        $c = '';
        if ($val == "-") {
            $c = "";
        } else if (($type == "num" && ($val >= 90)) || ($type == "grade" && ($val == "A+" || $val == "A")))
            $c = "good";
        else if ($type == "num" && ($val >= 50) || ($type == "grade" && ($val == "C" || $val == "B")))
            $c = "fair";
        else if ($type == "num" && ($val >= 0) || ($type == "grade" && ($val == "D" || $val == "F")))
            $c = "poor";
        return $c;
    }

    public function cvm_key_metrics_notice()
    {
        global $pagenow;
        $admin_pages = ['index.php', 'edit.php', 'plugins.php', 'tools.php', 'settings.php'];
        if (in_array($pagenow, $admin_pages)) {
            $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR . '/res/subs/sites_list.json');
            $sites = json_decode($jsonString, true);
            $notices = $sites["notification"];
            $message = $notices["message"];
            $type = $notices["type"];
            if (isset($message)) {
                $n_url = esc_url(add_query_arg(
                    'page',
                    'key_metrics',
                    get_admin_url() . 'tools.php'
                ));
            ?>
                <div class="notice notice-<?php  echo esc_attr( $type ) ?> is-dismissible">
                    <p>
                        <a style="font-weight: 700;" href='<?php  echo esc_url( $n_url ) ?>'>Key Metrics</a>
                        : <?php  echo esc_html( $message ) ?>
                    </p>
                </div>          
        <?php 
            }
        }
    }
    
    public function key_metrics_menu()
    {
        $page_title = 'Site Key Metrics';
        $menu_title = 'Key Metrics';
        $capability = 'manage_options';
        $menu_slug  = 'key_metrics';
        $function   = 'key_metrics_page';
        $icon_url   = 'dashicons-schedule';
        $position   = 75;
        add_management_page($page_title,                  $menu_title,                   $capability,                   $menu_slug,                   $function,                   $icon_url,                   $position);
    }

    public function key_metrics_setUpQuery($url,$strategy,$cat) {
        
        $api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
        $parameters = [
            "url" => urlencode($url),
            "strategy" => $strategy,//'desktop'
            "category" => $cat,//'PERFORMANCE','SEO','ACCESSIBILITY','BEST_PRACTICES','PWA','CATEGORY_UNSPECIFIED'
        ];
        $query = "$api?key=AIzaSyAmFinSScuu_az-PtninKYCQhXXfeHLnis";
        foreach($parameters as $k => $v){
            $query.="&$k=$v";
        }
        return $query;
    }

    public function key_metrics_getSecurityX($url){
        $rand = ['A','A+','B','C','D','F'];
        return $rand[rand(0,5)];
    }

    public function key_metrics_getSecurity($url){
        $headers = get_headers("https://securityheaders.com/?hide=on&followRedirects=on&q=$url", 1);
        $x = $headers["x-grade"];
        return $x;
    }

    public function key_metrics_setNoticeType($val,$type,$notice_type){
        
        if($type == "num" && ($val >= 50 && $val < 90) || ($type == "grade" && ($val == "C" || $val == "B" )) && $notice_type < 1 )
            $c = 1;
        else if($type == "num" && ($val >= 0 && $val < 50) || ($type == "grade" && ($val == "D" || $val == "F"  || $val == "E" )) && $notice_type < 2)
            $c = 2;
        else
            $c = $notice_type;
        return $c;
    }

    public function key_metrics_notifier($kpmurl,$em,$ms,$sub)
    {   

        $requestData = array();
        $requestData['sub'] = $sub;
        $requestData['ms'] = $ms;
        $requestData['em'] = $em;
        //request to external API and the keys are part of verifications that it came from this plugin 
        $req = Requests::request( $kpmurl.'?k=f8f4bd31cfd905551b98288139e2a969&c=cd88da7b6c397d4d2fef65e304390510', ['Accept' => 'application/json'], $requestData, "POST", array() );

        return $req;
    }

    public function key_metrics_setup_curl($type,$url)
    {   

        if($type != "security"){
            $durl = $this -> key_metrics_setUpQuery($url,$type,"performance");
            $req = 
            [
                'url' => $durl,
                'type' => 'GET',
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'options' => [
                    'timeout' => 180,
                    'connect_timeout' => 20,
                    'verify' => false,
                    'verifyname' => false,
                ],
            ];
            
        }else{
            $durl = "https://securityheaders.com/?hide=on&followRedirects=on&q=$url";
            $req = 
            [
                'url' => $durl,
                'type' => 'HEAD',
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'options' => [
                    'verify' => false,
                    'verifyname' => false,
                ],
            ];
        }
        return $req;
    }


}




