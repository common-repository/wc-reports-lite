
(function($) { 
"use strict"; 
$(document).ready(function () {
    //Display products btn
    $('.wcrl_customer_products a').on('click',function () {
        $(this).next().toggle();
    });

    // settings page
    $('.wcrl-settings-container ul.tabs li').on('click',function(){
        var tab_id = $(this).attr('data-tab');
        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');

        $(this).addClass('current');
        $("#"+tab_id).addClass('current');
    });

});
})(jQuery);