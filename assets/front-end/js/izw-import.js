jQuery(document).ready(function($){
    $('.nectar-social:not(".woo") .twitter-share:not(.inactive)').click(function(e) {
        e.stopPropagation();
        var hashtag = $(".program-hastag").text();
        windowLocation = window.location.href.replace(window.location.hash, '');
        if($(".section-title h1").length > 0) {
            var $pageTitle = encodeURIComponent($(".section-title h1").text());
        } else {
            var $pageTitle = encodeURIComponent($(document).find("title").text());
        }
        window.open( 'http://twitter.com/intent/tweet?text='+$pageTitle +' '+windowLocation + ' ' + hashtag.trim(), "twitterWindow", "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
        return false;
    });
    $('.nectar-social.woo .twitter-share').click(function(e) {
        e.stopPropagation();
        var hashtag = $(".program-hastag").text();
        windowLocation = window.location.href.replace(window.location.hash, '');
        window.open( 'http://twitter.com/intent/tweet?text='+$("h1.product_title").text() +' '+windowLocation + ' ' + hashtag.trim(), "twitterWindow", "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
        return false;
    });
});