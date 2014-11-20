/**
 * Created by nhiha60591 on 11/20/14.
 */
jQuery(document).ready(function ($) {
    $(".add-new-ctf").click(function(){
        var ctf_html = $(".form-table.custom-filed tr:first-child").html();
        $(".form-table.custom-filed tbody").append('<tr>'+ctf_html+'</tr>')
        return false;
    });
});
