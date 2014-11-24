/**
 * Created by nhiha60591 on 11/20/14.
 */
jQuery(document).ready(function ($) {
    $(".add-new-ctf").click(function(){
        var ctf_html = $(".form-table.custom-filed tr:first-child").html();
        $(".form-table.custom-filed tbody").append('<tr>'+ctf_html+'</tr>')
        return false;
    });
    $("#izw-add-field").click(function(){
        var ht_lenght = $('#form-custom-fields tbody tr').length;
        var ht_class = '';
        if(ht_lenght % 2 == 0 ){
            ht_class = 'alternate';
        }else{
            ht_class = '';
        }
        ht_lenght = ht_lenght +1;
        var html = '<tr class="'+ ht_class +'">\
            <th scope="row" class="check-column">\
            <input id="cb-select-'+ht_lenght+'" type="checkbox" value="1">\
                <div class="locked-indicator"></div>\
            </th>\
            <td>\
            <input type="text" name="field-item[]" />\
            </td>\
            <td>\
\
            </td>\
        </tr>';
        $('#form-custom-fields tbody').append(html);
        return false;
    });
    jQuery("#form-custom-fields button#delete-fields").click(function(){
        var answer = confirm("Delete the selected rows?");
        if (answer) {
            jQuery('#form-custom-fields tbody tr th.check-column input:checked').each(function(i, el){
                jQuery(el).closest('tr').remove();
            });
        }
        return false;
    });
    jQuery("#izw-load-field-same").click(function(){
        jQuery("#upload-same-data").slideToggle();
        return false;
    });
    jQuery("#upload-same-data-button").click(function (){
        var izw_file = document.getElementById("select-same-file").files;
        alert(izw_file[0].tmp_name);
        var data = {
            'action': 'load_same_data',
            'same_file': upload_file
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
            alert('Got this from the server: ' + response);
        });
        return false;
    });
});
