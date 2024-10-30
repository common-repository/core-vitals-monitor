metrics_fetching = false;
jQuery(document).ready(function() {
    const rand = (new Date()).getSeconds();

    jQuery(document).on('click', '#key_metrics_url_remove_button', function() {
        jQuery(this).closest("tr.key_metrics_row").remove();
    });
    jQuery(document).on('click', '#key_metrics_url_add_button', function() {
        jQuery("#key_metrics_url_table").append(`<tr class="key_metrics_row">
                <td class=""> <input class="key_metrics-in " placeholder="name" type="text" id="key_metrics_name" data-required="true" required=""></td>
                <td class=""> <input class="key_metrics-in " placeholder="https://www.example.com"  type="text" id="key_metrics_url" value="" data-required="true" required=""></td>
                <td class="">
                    <input type="button" id="key_metrics_url_remove_button" class="button button-remove" value="remove" >
                </td>
            </tr>`);
    });

    const save_key_metrics = () => {

        try {
            metrics_fetching = true;
            jQuery("#key_metrics_table").addClass("fetching");
            jQuery("input,button,select").attr("disabled", "true");
            var totalelem = document.getElementById("key_metrics_progressbar");
            totalelem.scrollIntoView({
                block: "center"
            });
            var arr = [];
            form_ins = jQuery("#key_metrics_url_table .key_metrics_row")

            counter = (form_ins.length + 1) * 10;
            counter = counter < 40 ? 40 : counter;
            var progresselem = document.getElementById("fetch_progress");
            progresselem.style.width = 0 + "px";
            var interval = setInterval(function() {
                if (progresselem.clientWidth >= totalelem.clientWidth - 5) {
                    clearInterval(interval);
                    return;
                }
                if (progresselem.clientWidth >= totalelem.clientWidth - 50) {
                    progresselem.style.width = progresselem.offsetWidth + 1 + "px";
                } else {
                    progresselem.style.width = progresselem.offsetWidth + 2 + "px";
                }

            }, counter)

            
            

            // console.table(formdata);

            formdata = {};
            var arr = [];
            form_ins = jQuery("#key_metrics_url_table .key_metrics_row")
            for (var k = 0; k < form_ins.length; k++) {
                var url = jQuery(form_ins[k]).find("#key_metrics_url").val().trim();
                var name = jQuery(form_ins[k]).find("#key_metrics_name").val().trim();
                arr.push([name, url]);
            }
            formdata["lurs"] = arr;

            form_ins = jQuery("#key_metrics_config").find("input,select,textbox");
            arr = [];
            for (var k = 0; k < form_ins.length; k++) {
                name = form_ins[k].name;
                val = form_ins[k].value;
                arr.push([name, val]);
            }
            formdata["gifnoc"] = arr;
            formdata["action"] = "km_92309f2059adc4dba86c5b668026";

            jQuery.ajax({
                type: "post",
                // dataType: "json",
                url: "/wp-admin/admin-ajax.php", //this is wordpress ajax file which is already avaiable in wordpress
                data: formdata,
                success: function(msg){
                    // console.log(msg);
                    // console.log("m");
                    metrics_fetching = false;
                    window.location.reload();
                    jQuery("#key_metrics_table").removeClass("fetching");
                    jQuery("input,button,select").removeAttr("disabled");
                },error: function(error){
                    // console.log(error);
                    // console.log("e");
                    jQuery("#key_metrics_table").removeClass("fetching");
                    jQuery("input,button,select").removeAttr("disabled");
                }
            });

        } catch (error) {
            console.log(error)
            jQuery("#key_metrics_table").removeClass("fetching");
            jQuery("input,button,select").removeAttr("disabled");
        }


    };

    const fetch_key_metrics = () => {

        try {
            metrics_fetching = true;
            jQuery("input,button,select").attr("disabled", "true");
            jQuery("#key_metrics_table").addClass("fetching");
            var totalelem = document.getElementById("key_metrics_progressbar");
            var progresselem = document.getElementById("fetch_progress");
            form_ins = jQuery("#key_metrics_url_table .key_metrics_row")
            counter = (form_ins.length + 1) * 10;
            counter = counter < 40 ? 40 : counter;

            progresselem.style.width = 0 + "px";
            var interval = setInterval(function() {
                if (progresselem.clientWidth >= totalelem.clientWidth - 5) {
                    clearInterval(interval);
                    return;
                }
                if (progresselem.clientWidth >= totalelem.clientWidth - 50) {
                    progresselem.style.width = progresselem.offsetWidth + 1 + "px";
                } else {
                    progresselem.style.width = progresselem.offsetWidth + 2 + "px";
                }

            }, counter)

            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "/wp-admin/admin-ajax.php", //this is wordpress ajax file which is already avaiable in wordpress
                data: {
                    action:'km_dba86c5b66802692309f2059adc4',
                },
                success: function(msg){
                    // console.log(msg);
                    // console.log("m");
                    metrics_fetching = false;
                    window.location.reload();
                    jQuery("#key_metrics_table").removeClass("fetching");
                    jQuery("input,button,select").removeAttr("disabled");
                },error: function(error){
                    // console.log(error);
                    // console.log("e");
                    jQuery("#key_metrics_table").removeClass("fetching");
                    jQuery("input,button,select").removeAttr("disabled");
                }
            });

        } catch (error) {
            jQuery("#key_metrics_table").removeClass("fetching");
            jQuery("input,button,select").removeAttr("disabled");
        }


    };
    
    jQuery("#key_metrics_update_button").click(function() {
        // console.log("fetch...");
        if (!metrics_fetching) {
            fetch_key_metrics();
        }
    });
    jQuery("#key_metrics_save_button").click(function() {

        if (jQuery(this).closest("form")[0].checkValidity()) {
            console.log("save...");
            form_ins = jQuery("input#key_metrics_name");
            var arr = [];
            for (var k = 0; k < form_ins.length; k++) {
                arr[(form_ins[k].value).trim()] = 1;
            }
            if (Object.keys(arr).length == form_ins.length)
                save_key_metrics();
            else
                alert('Ensure all webpages have unique names');

        } else {
            jQuery(this).closest("form")[0].reportValidity()
        }


    });
    try {
        s = new Date(metdate * 1000);
        t = s.toDateString() + ", " + s.toLocaleTimeString();
        jQuery("#km_last_updated").text("Last Updated: " + t);
        s = new Date(confdate * 1000);
        t = s.toDateString() + ", " + s.toLocaleTimeString();
        jQuery("#km_last_updated_url").text("Last Updated: " + t);
    } catch (error) {
    }

});