<?php  

    if ( ! session_id() ) {
        session_start();
    }
    set_time_limit(0);


    function save_key_metrics(){
        $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_list.json');
        $site_list = json_decode($jsonString, true);
    
        // post data must be in array
        if(is_array($_POST["lurs"])){
            $all_sites = [];
            foreach($_POST["lurs"] as $val){
                //each value in the array is sanitized before being saved and used
                $all_sites[] = ["name"=> sanitize_title($val[0]),"url"=> sanitize_url($val[1]) ];
            }
            if(!empty($all_sites)){
                $site_list["all_sites"] = $all_sites;
                $site_list["access"]["ambestdate"] = time();
                $site_list["access"]["serverdate"] = date("Y-m-d h:ia",time());
            }
        }

        // post data must be in array
        if(is_array($_POST["gifnoc"])){
            $config = [];
            foreach($_POST["gifnoc"] as $val){
                //each value in the array is sanitized before being saved and used
                $config[ sanitize_title($val[0]) ] = sanitize_text_field( $val[1] );
            }
            if(!empty($config)){
                $site_list["config"] = $config;
            }
        }

        $interval_before = $site_list["config"]['update_freq'];
        $interval_new = $config['update_freq'];

        if($interval_before != $interval_new){
            $pref_int = ["twicedaily", "daily", "weekly", "fortnightly", "monthly"];
            $pref_arr = [ 43200 , 86400, 604800, 1209600 ,  2592000 ];
            $index = array_search($interval_new,$pref_int);

            if( $index !== false ){
                $new_time = time() + $pref_arr[$index];
                if (wp_next_scheduled('key_metrics_refresh_data')) {
                    wp_clear_scheduled_hook('key_metrics_refresh_data');
                    wp_schedule_event($new_time , ''.$interval_new, 'key_metrics_refresh_data'); // plugin_cron_refresh_cache is a hook
                }

            }

        }

        $newJsonString = json_encode($site_list);
        file_put_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_list.json', $newJsonString);  

        refresh_key_metrics();
    }
    
    function refresh_key_metrics() {
        
        $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_metrics.json');
        $metrics = json_decode($jsonString, true);
        $all = $metrics["all_time"];
        $current = $metrics["all_time"];
        $current = array_pop($current);
        
        $jsonString = file_get_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_list.json');
        $site_list = json_decode($jsonString, true);
        $sites = $site_list["all_sites"]; 
        
        $sel_sec = sanitize_text_field($site_list["config"]["pref_sec"]);
        $sel_dsk = sanitize_text_field($site_list["config"]["pref_dsk"]);
        $sel_mob = sanitize_text_field($site_list["config"]["pref_mob"]);
        $sec_grade = ["A+","A","B","C","D","E","F"];
        $kpmurl = 'https://speedplussecurity.com/api/mailer.php';
        global $cvm_metrics_class;

        if( count($all) >= 30  ){
            $k = array_keys($all)[0];
            unset($all[$k]);
        }

        $multiCurl = array();
        $class = ["mobile","desktop","security"];
        $urls = [];
        foreach($sites as $val){
            $url =  esc_url($val["url"]);
            $name = esc_html($val["name"]);

            foreach($class as $c ){
                $tag = $name."___".$c;
                $multiCurl[$tag] = $cvm_metrics_class -> key_metrics_setup_curl($c,$url);
            }
            $urls[$name] = $url;
        }

        $requests = Requests::request_multiple($multiCurl);

        $response = array();
        $notice = false;
        $notice_type = 0;

        foreach ($requests as $key => $request) {
            $tag = explode("___", $key);
            $k = $tag[0];$c=$tag[1];
            if($c != "security"){
                $res = $request->body;
                $json = json_decode($res);
                $score = $json->lighthouseResult->categories->performance->score;
                $error = $json->error->message;
                if(strpos($error,"ERRORED_DOCUMENT_REQUEST") !== false){
                    $notice = true;
                    $notice_type = 4;    
                    $score = "-";
                }else if(strpos($error,"Something went wrong") !== false){
                    $score = $current[$k][$c] ?? '-';
                }else{
                    $score = $score * 100;
                    if($score == 0){
                        $score = $current[$k][$c] ?? $score;
                    }
                    $stand = $c == "mobile" ? $sel_mob : $sel_dsk;
                      if($score < $stand){
                          $notice = true;
                        $notice_type = $cvm_metrics_class -> key_metrics_setNoticeType($score,"num",$notice_type);
                    } 
                }
                
                $response[$k][$c] = sanitize_text_field($score);
            }else{
                $res = $request->raw;
                $data = [];
                $res = explode(PHP_EOL, $res);
                foreach ($res as $row) {
                    $parts = explode(':', $row);
                    if (count($parts) === 2) {
                        $data[trim($parts[0])] = trim($parts[1]);
                    }
                }
                $sec = $data["x-grade"]??'';
                  if($sec == ''){
                      $sec =	$current[$k][$c] ?? $cvm_metrics_class -> key_metrics_getSecurity($urls[$k]);                
                }
                if($sec == ''){
                    $sec = "-";
                    $notice = true;
                    $notice_type = 4;
                }else{
                    $s = array_search($sec,$sec_grade);
                    if($s > $sel_sec || $sec == "R" || $s == count($sec_grade) - 1 ){
                        $notice = true;
                        $notice_type = $cvm_metrics_class -> key_metrics_setNoticeType($sec,"grade",$notice_type); 
                    }
                }
                $response[$k]["security"] = sanitize_text_field($sec);
            }
            
        }

        $time = time();
        $all[$time] = $response;
  		$metrics["all_time"] = $all;
        $metrics["access"]["ambestdate"] = time();
        $metrics["access"]["serverdate"] = date("M d,Y h:i",time());


        $newJsonString = json_encode($metrics);
        file_put_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_metrics.json', $newJsonString);

        if($notice){
            if($notice_type == 4){
                $site_list["notification"] = array(
                    "message"=> "One or more webpages could not be loaded. Make sure you are testing the correct URL and that the server is properly responding to all requests. Please check the Key Metrics tab",
                    "type"=> $notice_type==1?"warning":"error",
                    "type_a"=> $notice_type
                );
            }
            else if($notice_type > 0){
                $site_list["notification"] = array(
                    "message"=> "One or more webpages have fallen below standards. Please check the Key Metrics tab",
                    "type"=> $notice_type==1?"warning":"error",
                    "type_a"=> $notice_type
                );
            }
        }

        $newJsonString = json_encode($site_list);
        file_put_contents(CORE_VITALS_MONITOR_PLUGIN_DIR.'/res/subs/sites_list.json', $newJsonString); 

        if($notice){
            
            $all = array_pop($metrics["all_time"]);
            $datas = array();
            foreach ($response as $name => $val) {
                $data = array();

                $url = $urls[$name];
                $sec = $val["security"] ?? '-';
                $dsk = $val["desktop"] ?? '-';
                $mob = $val["mobile"] ?? '-';
                $data["url"] = $url;
                $data["sec"] = $sec;
                $data["dsk"] = $dsk;
                $data["name"] = $name;
                $data["mob"] = $mob;
                $datas[] = $data;
            }
            $em = $site_list['config']['distr_email'];
            $ms = $site_list["notification"]['message'];
            $ms = str_replace(" Please check the Key Metrics tab",'',$ms);
            $bkg = $notice_type==1?"#f2fafc":"#fcf2f2";
            // $ms = key_metrics_format($ms,$metrics["access"]["serverdate"],$datas,$bkg);
            $ms =["ms"=>$ms,"tm"=>$metrics["access"]["serverdate"],"dt"=>$datas,"type"=>$notice_type];
            $ms = json_encode($ms);
            $sub = "Scan Notification";
            if(!empty($em) && isset($em)){
               $cvm_metrics_class -> key_metrics_notifier($kpmurl,$em,$ms,$sub);
            }
        }
    }

    