

<div class="key-metrics-form">
    
    <h3>Metrics Table</h3>
    <div id="key_metrics_table" class="key-metrics-section">
        <input type="button" id="key_metrics_update_button" title="Update Metrics Now" class="kmtx_fl-r button button-info" value="Update Now">
        <p class="description">
            Security Score and Mobile/Desktop Page Performance Score.
        </p>
        <p id="update_cont" class="description green">
            <span class="dashicons-before kmtx dashicons-update c-inh" id="key_metrics_update"></span>
            <span id="update_text">Updating Metrics...</span>
            <strong id="km_last_updated" class="edible">
                Last Updated:
            </strong>   
        </p>

        <div id="key_metrics_progressbar">
            <div id="fetch_progress"></div>
        </div>
        <hr class="table_hr">
        <table class="key-metrics-table">
            <tr class="head" valign="top">
                <th scope="row">URL</th>
                <th scope="row">Security Score</th>
                <th scope="row">Desktop Score</th>
                <th scope="row">Mobile Score</th>
            </tr>

            <?php 

            $all = array_pop($metrics["all_time"]);
            foreach ($sites as $val) {
                $url = $val["url"];
                $name = $val["name"];
                $sec = $all[$name]["security"] ?? '-';
                $dsk = $all[$name]["desktop"] ?? '-';
                $mob = $all[$name]["mobile"] ?? '-';
                $sec_class = $cvm_metrics_class -> key_metrics_get_text_class($sec, 'grade');
                $dsk_class = $cvm_metrics_class -> key_metrics_get_text_class($dsk, 'num');
                $mob_class = $cvm_metrics_class -> key_metrics_get_text_class($mob, 'num');
                $mob = $cvm_metrics_class -> key_metrics_get_text_suffix($mob);
                $dsk = $cvm_metrics_class -> key_metrics_get_text_suffix($dsk);
                $name = esc_html( $name);
                $url = esc_url_raw( $url);
                $mob = esc_html( $mob);
                $sec = esc_html( $sec);
                $dsk = esc_html( $dsk);
                $mob_class = esc_html( $mob_class);
                $sec_class = esc_html( $sec_class);
                $dsk_class = esc_html( $dsk_class);

            ?>
                <tr>
                    <td class="edible <?php  echo  $sec == '-' && $dsk == '-' && $mob == '-' ? 't-strike' : '' ?>"><?php  echo esc_url_raw( $url); ?></td>
                    <td class="edible <?php  esc_attr_e( $sec_class )?>"><?php  echo esc_html( $sec); ?></td>
                    <td class="edible <?php  esc_attr_e( $dsk_class ) ?>"><?php  echo  esc_html( $dsk);?></td>
                    <td class="edible <?php  esc_attr_e( $mob_class ) ?>"><?php  echo esc_html( $mob); ?></td>
                </tr>

            <?php 
            }
            ?>

        </table>
        <p class="description sources kmtx_tac">Sources: &emsp; securityheaders.com &emsp; - &emsp; pagespeed.web.dev</p>

    </div>

</div>


<script>
    const metdate= '<?php  echo esc_html( $metrics["access"]["ambestdate"] ) ?>';
    const corepath= '<?php  echo esc_html( $path ) ?>';
</script>