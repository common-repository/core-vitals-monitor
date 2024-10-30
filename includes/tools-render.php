<?php 
    try {
        if (!function_exists("key_metrics_page")) 
        {
            
            function key_metrics_page()
            {

                global $cvm_metrics_class;
                // $file_path = str_replace('\\', '/',  CORE_VITALS_MONITOR_PLUGIN_DIR);
                // $file_path = str_replace(  sanitize_text_field($_SERVER['DOCUMENT_ROOT']) , '', $file_path);
                // $path = 'https://' . sanitize_text_field( $_SERVER['HTTP_HOST'] ). $file_path;

                $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR . '/res/subs/sites_list.json');
                $site_list = json_decode($jsonString, true);
                $sites = $site_list["all_sites"];
                $sel_sec = sanitize_text_field($site_list["config"]["pref_sec"]);
                $sel_dsk = sanitize_text_field($site_list["config"]["pref_dsk"]);
                $sel_mob = sanitize_text_field($site_list["config"]["pref_mob"]);
                $metrics_email = sanitize_text_field($site_list["config"]["distr_email"]);
                $update_freq = sanitize_text_field($site_list["config"]["update_freq"]);
                $sec_grade = ["A+", "A", "B", "C", "D", "E", "F"];
    
                $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR. '/res/subs/sites_metrics.json');
                $metrics = json_decode($jsonString, true);

                include_once(__DIR__ . "/header.php");
                include_once(__DIR__ . "/display.php");
                include_once(__DIR__ . "/config.php");

    
                if (isset($site_list["notification"]["message"])) {
                    $site_list["notification"] = array();
                    $newJsonString = json_encode($site_list);
                    file_put_contents(CORE_VITALS_MONITOR_PLUGIN_DIR . '/res/subs/sites_list.json', $newJsonString);
                }
    
                if (!session_id()) {
                    session_start();
                }
    
                $_SESSION["kmtrxcvmsessrec"] = "0933aab32f47bd3c0482c69b0b316d47ed264f50c552255f10da34c09b7a28e8";
    
            }
        }
        
    } catch (\Throwable $th) {
        
    }


    