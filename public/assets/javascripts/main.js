map = false;
$(document).ready(function () {

    /*
        -- Page background via Backstretch.js
     */

    $.backstretch("/assets/images/background.jpg");

    /*
        -- Select country based on visitor location
     */


    if(search_location != '') {
        $('#country-list option[value="'+ search_location +'"]').attr('selected', 'selected');
    }
    else {
        $('#country-list option[value="'+ user_location +'"]').attr('selected', 'selected');
    }



    /*
        -- Modals events
     */


    // normal modals
    $('.open-modal').on("click",function (e) {
        var modal_id = '#' + $(this).attr('data-modal'),
            modal = $(modal_id);
        modal.on('show.bs.modal', centerModal);
        modal.modal('show');
        e.preventDefault();
    });

    // map modal
    $('#map').on("click", function (e) {
        var height =  (($(window).height()/100)*80);
        $('#map-modal').find('.modal-dialog').css({'width': '80%'});
        $('#map-modal').find('.modal-body').css({'height': height, 'width':'100%'});
        $('#map-modal').on('show.bs.modal', centerModal);
        $('#map-modal').modal('show');
        $('#map-modal').on('shown.bs.modal', function () {
            if(!map) {
                $.getScript('https://maps.googleapis.com/maps/api/js?v=3.exp&' +
                'callback=asyncMap', function () {});
                map = true;
            }
            else {
                google.maps.event.trigger(map, 'resize');
            }

        })

        e.preventDefault();
    })

    /*
        -- Forms validation
     */


    // Artist form sign up validation
    $("#ArtistRegistrationForm").validate({
        debug: true,
        submitHandler: function(form) {
            var queryString =  $(form).serialize();
            var btn = $(form).find('button[type="submit"]');
            btn.attr('disabled', true)
            $.post( "/users", queryString , function( data ) {
                if(data.success) {
                    $(form).remove();
                    $('#ArtistConfirmation').show();
                }
                else {
                    btn.attr('disabled', false);
                    console.log(data);
                    $.each(data, function (i, v) {
                        $('#' + i).addClass('error-input');
                        $('#' + i).after('<label class="error-input">'+ v +'</label>')
                    })
                }
            });
        },
        errorClass: "error-input",
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            repassword: {
                equalTo: "#password"
            },
            agree: "required"
        }
    });

    // Artist form sign up validation
    $("#CustomerRegistrationForm").validate({
        debug: true,
        submitHandler: function(form) {
            var queryString =  $(form).serialize();
            var btn = $(form).find('button[type="submit"]');
            btn.attr('disabled', true)
            $.post( "/users", queryString , function( data ) {
                if(data.success) {
                    $(form).remove();
                    $('#CustomerConfirmation').show();
                }
                else {
                    btn.attr('disabled', false);
                    console.log(data);
                    $.each(data, function (i, v) {
                        $(form).find('input[name='+ i +']').addClass('error-input');
                        $(form).find('input[name='+ i +']').after('<label class="error-input">'+ v +'</label>')
                    })
                }
            });
        },
        errorClass: "error-input",
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            repassword: {
                equalTo: "#customer_password"
            },
            agree: "required"
        }
    });

    // Charity form sign up validation

    $("#CharityRegistrationForm").validate({
        debug: true,
        submitHandler: function(form) {
            var queryString =  $(form).serialize();
            var btn = $(form).find('button[type="submit"]');
            btn.attr('disabled', true)
            $.post( "/users", queryString , function( data ) {
                if(data.success) {
                    $(form).remove();
                    $('#CharityConfirmation').show();
                }
                else {
                    btn.attr('disabled', false);
                    console.log(data);
                    $.each(data, function (i, v) {
                        $(form).find('input[name='+ i +']').addClass('error-input');
                        $(form).find('input[name='+ i +']').after('<label class="error-input">'+ v +'</label>')
                    })
                }
            });
        },
        errorClass: "error-input",
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            repassword: {
                equalTo: "#charity_password"
            },
            agree: "required"
        }
    });

    /* Auth form */

    $('#SignInForm').submit(function ( event ) {

        $req = $.post('/signin', $(this).serialize());
        $req.success(function (data) {
            if(data.success)
            {
                window.location.replace(data.url);
            }
            else
            {
                $('#SignInError').html('Incorrect username and/or password');
                $('#SignInError').fadeIn('500');
            }
        });
        event.preventDefault();

    });

    $('#country-list').selectpicker({
        style: 'btn-info',
        size: 8
    });

    /*
        -- misc functions
     */

    function centerModal() {
        $(this).css('display', 'block');
        var $dialog = $(this).find(".modal-dialog");
        var offset = ($(window).height() - $dialog.height()) / 3;
        // Center modal vertically in window
        $dialog.css("margin-top", offset);
    }

    window.asyncMap = function () {
        var mapOptions = {
            zoom: 8,
            center: new google.maps.LatLng(-34.397, 150.644)
        };


        var map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);
        google.maps.event.trigger(map, 'resize');
        var bounds = new google.maps.LatLngBounds();
        console.log(coordinates);
        $.each(coordinates, function (index, cords) {
            var pin_coordinates = new google.maps.LatLng(cords.lat,cords.lng);
            if(cords.color == 'blue') {

                // blue marker for hubs

                var marker = new google.maps.Marker({
                    position: pin_coordinates,
                    title: cords.name,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                });
            }
            else {
                var marker = new google.maps.Marker({
                    position: pin_coordinates,
                    title: cords.name,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                });
            }


            var infowindow = new google.maps.InfoWindow({
                content: '<a href="' + cords.url + '">'+ cords.name +'</a>'
            });
            if(cords.color == 'blue') {

            }
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.open(map,marker);
            });
            marker.setMap(map);
            bounds.extend(marker.position);
            map.fitBounds(bounds);
        })

    }
});