<form class="key-metrics-form" action="javascript:myFunction();">
    
    <!-- manage webpages -->
    <div class="key-metrics-cont">

        <h3>Manage Webpages</h3>
        <div id="key_metrics_url_settings" class="key-metrics-section">
            <p class="description">
                Add/Edit webpages to be scanned.
            </p>
            <p id="update_cont" class="description green">
                <strong id="km_last_updated_url" class="edible">
                    Last Updated:
                </strong>
            </p>

            <div id="key_metrics_progressbar">
                <div id="fetch_progress"></div>
            </div>
            <hr class="table_hr">
            <table class="key-metrics-table" id="key_metrics_url_table">
                <tr class="head key_metrics_settings" valign="top">
                    <!-- <th scope="row"></th> -->
                    <th scope="row" class="bold6">Name</th>
                    <th scope="row" class="bold6">URL</th>
                    <th scope="row"></th>
                </tr>

                <?php 
                $d = 0;
                foreach ($sites as $val) {
                    $url = $val["url"];
                    $name = $val["name"];
                ?>
                    <tr class="key_metrics_row">
                        <td class=""> <input class="key_metrics-in " placeholder="name" type="text" id="key_metrics_name" value="<?php  echo esc_html( $name) ?>" data-required="true" required=""></td>
                        <td class=""> <input class="key_metrics-in " placeholder="https://www.example.com" type="text" id="key_metrics_url" value="<?php  echo esc_url_raw($url) ?>" data-required="true" required=""></td>
                        <td class="">
                            <input type="button" id="key_metrics_url_remove_button" class="button button-remove" value="remove">
                        </td>
                    </tr>

                <?php 
                }
                ?>

            </table>
            <div class="key_metrics_save_changes">
                <input type="button" id="key_metrics_url_add_button" class="button button-info" value="Add URL">
            </div>

        </div>

    </div>

    <!-- more settings -->
    <div class="key-metrics-cont mt-3">

        <h3>Metrics Configuration</h3>
        <div id="key_metrics_config" class="key-metrics-section">

            <div style="margin-bottom: 20px;">
                <p id="update_cont" class="description green">
                    <strong id="" class="edible">
                        Metrics Standards
                    </strong>
                </p>
                <p class="description">
                    Please set security and speed score that triggers notification.
                </p>

                <div id="key_metrics_progressbar">
                    <div id="fetch_progress"></div>
                </div>
                <hr class="table_hr">

                <div style="display: flex;flex-wrap: wrap;">

                    <div class="input-norm col-md-3 mb-4" style="display: flex;flex-direction: column;flex: 1;padding: 10px;min-width: 150px;max-width: 300px; ">
                        <span class="input-norm-addon" style="margin-bottom: 5px;">Security Score</span>
                        <select class="form-control" id="pref_sec" name="pref_sec" data-required="true" required="">
                            <option value="" disabled="">Select</option>
                            <?php 

                            foreach ($sec_grade as $p => $grad) {
                                $grad = esc_html( $grad);
                                $p = esc_html( $p);
                                $txt = $grad != "F" ? "Less than " : "";
                                $sel = $sel_sec == $p ? " selected='' " : '';

                            ?>
                                <option <?php esc_attr_e($sel)?> value='<?php echo esc_html($p)?>'><?php echo esc_html($txt.' '.$grad)?></option>;
                            <?php 
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-norm col-md-3 mb-4" style="display: flex;flex-direction: column;flex: 1;padding: 10px;min-width: 150px;max-width: 300px; ">
                        <span class="input-norm-addon" style="margin-bottom: 5px;">Desktop Score</span>
                        <select class="form-control" id="pref_dsk" name="pref_dsk" data-required="true" required="">
                            <option value="" disabled="">Select</option>
                            <?php 

                            for ($i = 100; $i > 0; $i--) {
                                $txt = $i != 0 ? "Less than" : "";
                                $sel = $sel_dsk == $i ? " selected='' " : '';

                            ?>
                                <option <?php esc_attr_e($sel)?> value='<?php echo esc_html($i)?>'><?php echo esc_html($txt.' '.$i)?></option>;
                            <?php 
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-norm col-md-3 mb-4" style="display: flex;flex-direction: column;flex: 1;padding: 10px;min-width: 150px;max-width: 300px; ">
                        <span class="input-norm-addon" style="margin-bottom: 5px;">Mobile Score</span>
                        <select class="form-control" id="pref_mob" name="pref_mob" data-required="true" required="">
                            <option value="" disabled="">Select</option>
                            <?php 

                            for ($i = 100; $i > 0; $i--) {
                                $txt = $i != 0 ? "Less than" : "";
                                $sel = $sel_mob == $i ? " selected='' " : '';

                            ?>
                                <option <?php esc_attr_e($sel)?> value='<?php echo esc_html($i)?>'> <?php echo esc_html($txt.' '.$i)?></option>;
                            <?php 
                            }
                            ?>
                        </select>
                    </div>

                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <p id="update_cont" class="description green">
                    <strong id="" class="edible">Email Notification</strong>
                </p>
                <p class="description">
                    Please enter email address to receive notifications when a page falls below standard
                </p>
                <hr class="table_hr">

                <div style="display: flex;flex-wrap: wrap;">

                    <div class="input-norm col-md-3 mb-4" style="display: flex;flex-direction: column;flex: 1;padding: 10px;min-width: 150px;">
                        <span class="input-norm-addon" style="margin-bottom: 5px;">Email Address</span>
                        <input class="key_metrics-in " placeholder="" type="email" id="key_metrics_email" name="distr_email" value="<?php  echo esc_textarea($metrics_email) ?>">
                    </div>

                </div>

            </div>

            <div style="margin-bottom: 20px;">
                <!--56 skm 92309f2059adc4 -->
                <!--56 rkm dba86c5b668026 -->
                <p id="update_cont" class="description green">
                    <strong id="" class="edible">Scan Schedule</strong>
                </p>
                <p class="description">
                    Please choose the preferred scan interval
                </p>

                <hr class="table_hr">

                <div style="display: flex;flex-wrap: wrap;">

                    <div class="input-norm col-md-3 mb-4" style="display: flex;flex-direction: column;flex: 1;padding: 10px;min-width: 150px;/* max-width: 300px; */">
                        <span class="input-norm-addon" style="margin-bottom: 5px;">Scan Interval</span>
                        <select class="form-control" disabled="" id="pref_interval" name="update_freq">
                            <option value="" disabled="">Select</option>
                            <?php 
                            $pref_int = ["twicedaily", "daily", "weekly", "fortnightly", "monthly"];
                            foreach ($pref_int as $p => $grad) {
                                $sel = $update_freq == $grad ? " selected='' " : '';
                                $grad = esc_html( $grad);
                            ?>
                                <option  <?php esc_attr_e($sel)?>  value='<?php echo esc_html($grad)?>'><?php echo esc_html($grad)?></option>;
                            <?php 
                            }
                            ?>
                        </select>
                    </div>

                </div>

            </div>

        </div>



    </div>

    <div class="key_metrics_save_changes">
        <input type="submit" id="key_metrics_save_button" class="button button-primary" value="Save Changes">
    </div>

</form>

<script>
    const confdate= '<?php  echo esc_html( $site_list["access"]["ambestdate"] ) ?>';
</script>