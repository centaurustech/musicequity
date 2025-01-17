jQuery(document).ready(function($){
    //if you change this breakpoint in the style.css file (or _layout.scss if you use SASS), don't forget to update this value as well
    var $L = 1200,
        $menu_navigation = $('#main-nav'),
        $cart_trigger = $('#cd-cart-trigger'),
        $hamburger_icon = $('#cd-hamburger-menu'),
        $lateral_cart = $('#cd-cart'),
        $shadow_layer = $('#cd-shadow-layer');

    //open lateral menu on mobile
    $hamburger_icon.on('click', function(event){
        event.preventDefault();
        //close cart panel (if it's open)

        $lateral_cart.removeClass('speed-in');
        toggle_panel_visibility($menu_navigation, $shadow_layer, $('body'));
    });

    //open cart
    $cart_trigger.on('click', function(event){
        event.preventDefault();
        //close lateral menu (if it's open)
        var ul = $('.cd-cart-items');
        var total = 0;
        ul.empty();
        $.get('cart/view', function (data) {
            $.each(data, function(i, data) {
                var li = $('<li />');
                li.attr('id', 'cart_line' + data.id);
                var html = '<span class="cd-qty">1x</span> '+ data.title +' <div class="cd-price">$'+ parseFloat(data.price).toFixed(2) +'</div> <a href="#" data-id="'+ data.id +'" class="cd-item-remove cd-img-replace"></a>';
                li.html(html);
                ul.append(li);
                total += Number(data.price);
            });
            $.get('cart/view-bundle', function (data) {
                $.each(data, function(i, data) {
                    var li = $('<li />');
                    li.attr('id', 'cart_line' + data.id);
                    var html = '<span class="cd-qty">1x</span> '+ data.title +' <div class="cd-price">$'+ parseFloat(data.price).toFixed(2) +'</div> <a href="#" data-id="'+ data.id +'" class="bundle-item-remove cd-img-replace"></a>';
                    li.html(html);
                    ul.append(li);
                    total += Number(data.price);
                });
                $('.cd-cart-total').find('span').html('$AUD '+ parseFloat(total).toFixed(2));
            })
        });
     toggle_panel_visibility($lateral_cart, $shadow_layer, $('body'));
    });

    //close lateral cart or lateral menu
    $shadow_layer.on('click', function(){
        $shadow_layer.removeClass('is-visible');
        // firefox transitions break when parent overflow is changed, so we need to wait for the end of the trasition to give the body an overflow hidden
        if( $lateral_cart.hasClass('speed-in') ) {
            $lateral_cart.removeClass('speed-in').on('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
                $('body').removeClass('overflow-hidden');
            });
            $menu_navigation.removeClass('speed-in');
        } else {
            $menu_navigation.removeClass('speed-in').on('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
                $('body').removeClass('overflow-hidden');
            });
            $lateral_cart.removeClass('speed-in');
        }
    });

});

function toggle_panel_visibility ($lateral_panel, $background_layer, $body) {
    if( $lateral_panel.hasClass('speed-in') ) {
        // firefox transitions break when parent overflow is changed, so we need to wait for the end of the trasition to give the body an overflow hidden
        $lateral_panel.removeClass('speed-in').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
            $body.removeClass('overflow-hidden');
        });
        $background_layer.removeClass('is-visible');

    } else {
        $lateral_panel.addClass('speed-in').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
            $body.addClass('overflow-hidden');
        });
        $background_layer.addClass('is-visible');
    }
}

function move_navigation( $navigation, $MQ) {
    if ( $(window).width() >= $MQ ) {
        $navigation.detach();
        $navigation.appendTo('header');
    } else {
        $navigation.detach();
        $navigation.insertAfter('header');
    }
}