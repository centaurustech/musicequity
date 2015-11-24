$(document).ready(function () {

    /*
     -- Page background via Backstretch.js
     */

    var page = 1;
    $.backstretch("assets/images/background.jpg");

    $('#more-do').on('click', function(e) {
        e.preventDefault();
        $('.less-text').hide();
        $('.more-text').slideDown('500');
    })

    $('#less-do').on('click', function(e) {
        e.preventDefault();
        $('.more-text').slideUp('1000');
        $('.less-text').show();

    })

    $('.scroll-next').click(function () {
        page++;
        var url      = window.location.href;
        var $btn = $(this);
        $btn.attr('disabled', 'disabled');
        $btn.html('Loading...');
        $.get(url + "?page=" + page,function(data) {
            var posts = $(data).find('.itemWall');

            $('#scroll-block').before(posts);

            if(total_pages == page) {
                $btn.attr('disabled', 'disabled');
                $btn.html('Load More');
            }
            else {
                $btn.prop("disabled", false);
                $btn.html('Load More');
            }
        });

    });

    playing = false;
    current = '';
    $('#upload-btn').click(function() {
        $('#upload').modal('show');
    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.danger').prop('href', $(e.relatedTarget).data('href'));
    });


    $('.buy').on('click', function (e) {
        var id = $(this).attr('data-id');

        $.post('add-to-cart/' + id, function (data) {
            $('#cd-cart-trigger').click();
        });

        e.preventDefault();
    });


    $('.buy-bundle').on('click', function (e) {
        var id = $(this).attr('data-id');

        $.post('add-bundle-to-cart/' + id, function (data) {
            $('#cd-cart-trigger').click();
        });

        e.preventDefault();
    });


    $('.selector').on('click', function (e) {
        var target = $(this).data('target');

        if(target == 'picture') {
            $('.profile-picture').show();
            $('.promo').hide();
        }

        if(target == 'video') {
            $('.profile-picture').hide();
            $('.promo').show();
        }
        console.log('picture');
        e.preventDefault();

    });

    $('body').on('click', '.cd-item-remove', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');

        $.post('remove-from-cart/' + id, function (data) {
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
        });
        $(this).parent().hide(300).remove();
    });

    $('body').on('click', '.bundle-item-remove', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');

        $.post('remove-bundle-from-cart/' + id, function (data) {
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
        });
        $(this).parent().hide(300).remove();
    });

    $('#approval-btn').click(function() {
        $('#approval').modal('show');
    });


    $('.new-event').click(function () {

        $('#newevent').modal('show');

    });
    state = false;

    $('.like').on("click", function (e) {
        var song = $(this).attr('data-song');
        var block = $(this);
        $.post('like/' + song, function(data){
            if(data.like) {
                block.find('span').removeClass('glyphicon-heart-empty');
                block.find('span').addClass('glyphicon-heart');
                var number = parseInt(block.parent().find('span.like-wrapper').html());
                number++;
                block.parent().find('span.like-wrapper').html(number);
            }
            else {
                block.find('span').addClass('glyphicon-heart-empty');
                block.find('span').removeClass('glyphicon-heart');
                var number = parseInt(block.parent().find('span.like-wrapper').html());
                number--;
                block.parent().find('span.like-wrapper').html(number);
            }

        });

        e.preventDefault();
    })

    $('body').click(function (event) {
        if( !$(event.target).is('li.dropdown-item') &&  !$(event.target).is('img.bars') ) {
            $('.dropdown').css('visibility', 'hidden');
            state = false;
        }
    })

    $('.social-link').on("click", function (e)
    {
        var url = $(this).attr("href");
        var windowName = "Share";
        var left = (screen.width/2)-(580/2);
        var top = (screen.height/2)-(400/2);
        var windowParams = "width=580,height=400 top="+top+", left="+left;

        window.open(url, windowName, windowParams);

        e.preventDefault();
    });

    $('#dropdown-tg').on("click", function (e) {
        if(state) {
            $('.dropdown').css('visibility', 'hidden');
        }
        else {
            $('.dropdown').css('visibility', 'visible');
        }
        state = !state;
        e.preventDefault();
    });

    $('#ReportForm').on("submit", function(e) {
        var d = $(this).serialize();
        $.post('report', d, function (data) {
            if(!data.success) {
                $('#reason').removeClass('error');
                $('#explanation').removeClass('error');
                $.each(data, function(key, value)  {
                    $('#' + key).addClass('error');
                })
            }
            else {
                $('#report').modal('hide');

                $('#ReportForm')[0].reset();
                toastr.success('Your report was submitted for review.','Success!')
            }
        })
        e.preventDefault();
    });

    $('#report-btn').on("click", function (event) {
        $('#report').modal('show');
        event.preventDefault();
    })

    $('[data-toggle="tooltip"]').tooltip({html:true})

    $('#AddEventForm').on('submit', function(e) {

        var data = $(this).serialize();
        $.post('event/new', data, function(data) {
            if(!data.success) {
                $('#name').removeClass('error');
                $('#date').removeClass('error');
                $('#location').removeClass('error');
                $.each(data, function(key, value)  {
                    console.log('#' + key, value);
                    $('label.error-input').remove();
                    $('#' + key).addClass('error');
                    $('#' + key).after('<label class="error-input">'+ value +'</label>')
                })
            }
            else {
                $('#newevent').modal('hide');
                $('#AddEventForm')[0].reset();
                location.reload();
            }
        })

        e.preventDefault();
    })


    $('.datepicker').datepicker({ dateFormat: 'dd/mm/yy' });

    function drawProgress(canvas, start, position, duration) {
        diff = (position - start * 1000) / 1000;
        var blockwidth = (diff * 295) / (duration / 1000)
        canvas.setLayer('progress', {
            width:blockwidth,
            x:0
        }).drawLayers();
    }

    function hideProgress(canvas) {
        canvas.setLayer('progress', {
            width:0
        }).drawLayers();
    }

    function changePosition(id, position) {
        var song = soundManager.getSoundById(id);
        song.setPosition(position);
    }


    $('#current_title').marquee({
        //speed in milliseconds of the marquee
        duration: 7000,
        //gap in pixels between the tickers
        gap: 50,
        //time in milliseconds before the marquee will start animating
        delayBeforeStart: 0,
        //'left' or 'right'
        direction: 'left',
        //true or false - should the marquee be duplicated to show an effect of continues flow
        duplicated: true
    });

    $('.volume').click(function () {
        $('.slider-container').toggle();
    });


    $('.slider').slider({
        orientation: 'vertical',
        value: 80,
        max: 100,
        tooltip: 'hide',
        handle: 'square'
    }).on('slide', function (e) {
        volume = 100 - e.value;
        soundManager.setVolume(current,volume);
    });

    function PlaySong(song, elem, canvas, title)
    {

        if( !playing )
        {
            $('.player-art').html('<img src="' + elem.attr('data-picture') + '" />');
            $('.btn-start').find('span').removeClass('glyphicon-play')
                .addClass('glyphicon-pause');
            $('#current_title').html(title).marquee({
                //speed in milliseconds of the marquee
                duration: 7000,
                //gap in pixels between the tickers
                gap: 50,
                //time in milliseconds before the marquee will start animating
                delayBeforeStart: 0,
                //'left' or 'right'
                direction: 'left',
                //true or false - should the marquee be duplicated to show an effect of continues flow
                duplicated: true
            });
            $('.fixed-player').slideDown(1000);
            elem.find('span').removeClass('glyphicon-play');
            elem.find('span').addClass('glyphicon-pause');


            song.play(
                {

                    whileplaying: function () {
                        drawProgress($(canvas), 0, this.position, this.duration)
                        var position = this.position;
                        var oldvolume = this.volume;
                        if(position > 25000) {
                            var rest = 30000 - position;
                            var volume;
                            if(rest != 0) {
                                volume = rest/5000*oldvolume;
                            }
                            else {
                                volume = 0;
                            }
                            this.setVolume(volume);
                        }
                    },
                    onfinish: function() {
                        playing = false;
                        $('.play').find('span').removeClass('glyphicon-pause');
                        $('.play').find('span').addClass('glyphicon-play');
                        $('.btn-start').find('span').removeClass('glyphicon-pause')
                            .addClass('glyphicon-play');
                        hideProgress($(canvas));
                        $('.fixed-player').slideUp(1000);
                        this.setVolume(20);
                    },
                    onload: function () {
                        // Post information about hit
                        $.post('addhit/' + elem.attr('data-id'), function (data) {
                            if(data.success)
                            {
                                var block_id = '#song-hits-' + elem.attr('data-id')
                                var hits = $(block_id).html();
                                var chits = parseInt(hits);
                                $(block_id).html(chits + 1);

                            }
                        });
                    }
                }
            );
            playing = true;
            current = song.id;
        }
        else
        {
            name = 'song-' + elem.attr('data-id');
            soundManager.pauseAll();
            $('.play').find('span').removeClass('glyphicon-pause');
            $('.play').find('span').addClass('glyphicon-play');
            $('.btn-start').find('span').removeClass('glyphicon-pause')
                .addClass('glyphicon-play');
            playing = false;
            if(name != current) {
                current = name;
                PlaySong(soundManager.getSoundById(name), elem, canvas, title);
            }
            else {
                current = '';
            }

        }
    }

    $('.play-item').click(function() {

        var target = $(this).data('bundle-target');
        var main = $(this).data('bundle');
        $('.' + main).hide();
        console.log($('#' + target));
        $('#' + target).show();

    });


    $('#album-btn').click(function () {
        $('#album').modal('show');
    });

    $('#faq-btn').click(function () {
        $('#faq').modal('show');
    });

    $('#transactions-btn').click(function () {
        $('#transactions').modal('show');
    });

    $('#approval').one('shown.bs.modal', function () {
        $('#data-table').dataTable({
            "searching": false
        });
    })

    $('#transactions').on('shown.bs.modal', function () {
        $('#transactions-table').dataTable({
            "searching": false
        });
    })



    /*$.validator.addMethod("needsSelection", function(value, element) {
     return $(element).multiselect("getChecked").length > 0;
     });

     $.validator.messages.needsSelection = 'You need to select at least 1 song.';
     */
    $("#create_album").validate({
        debug: true,
        submitHandler: function(form) {
            var queryString =  $(form).serialize();
            console.log(queryString);
            var btn = $(form).find('button[type="submit"]');
            btn.attr('disabled', true)
            $.post( "/create/album", queryString , function( data ) {
                if(data.success) {
                    location.reload();
                }
                else {
                    btn.attr('disabled', false);
                    console.log(data);
                    /*$.each(data, function (i, v) {
                     $('#' + i).addClass('error-input');
                     $('#' + i).after('<label class="error-input">'+ v +'</label>')
                     })*/
                }
            });
        },
        errorClass: "error-input",
        rules: {
            name: {
                required: true
            },
            price: {
                required: true,
                min: 1.10
            },
            'songs[]': {
                required: true,
                minlength:2,
                maxlength:10
            }
        },
        messages: {
            'songs[]': {
                minlength: 'You need at least 2 songs for album.',
                maxlength: 'You are only allowed to select 20 songs'
            }
        },
        ignore: ':hidden:not("#songs")'
    });

    $('.play').click(function ()
    {


        // getting sound id
        var id = $(this).attr('data-id');
        var title = $(this).attr('data-title');



        // Drawing canvas
        var box = $(this).parent().find('div.song-progress');
        var canvas = box.find('canvas');

        if ($(canvas).length == 0) {
            box.empty();
            box.append('<canvas class="canvas" width="295" height="55" id="canvas-song-' + id + '"></canvas>');
            var canvas = box.find('canvas');
            canvas.drawImage({
                layer:true,
                source: 'thumbnail/' + id,
                x: 147.5, y: 27.5,
                click: function(layer) {
                    position = layer._eventX;
                    s = (30 * position) / 295

                    changePosition('song-'+ id, s*1000);
                }
            }).drawRect(
                {
                    layer : true,
                    fromCenter: false,
                    name: 'progress',
                    compositing:'lighter',
                    fillStyle: '#004D66',
                    intangible: true,
                    x: 0, y: 0,
                    width: 0, height: 55
                }).drawLayers();
        }

        // Handling sound

        var name = 'song-' + id
        var song = soundManager.getSoundById(name);
        if(!song)
        {
            soundManager.createSound({
                id: name,
                url: '/song/' + id,
                volume:20
            });
        }

        song = soundManager.getSoundById(name);


        PlaySong(song, $(this), canvas, title);

    });



    Dropzone.options.uploadForm = {
        paramName: "song", // The name that will be used to transfer the file
        maxFilesize: 50, // MB
        acceptedFiles: ".aiff,.wav,.flac,.alac,.ogg,.mp2,.mp3,.aac,.amr,.wma,.m4a",
        addRemoveLinks: true,
        uploadMultiple : false,
        maxFiles: 1,
        init: function() {
            this.on("success", function(file)
            {
                document.location.href = "/upload";
            });
        },
        accept: function(file, done) {
            if( $('#agree').is(':checked') ) {
                if( $('#agree2').is(':checked') ) {
                    done();
                }
                else {
                    alert('You must confirm that you are the owner of the track.')
                }
            }
            else {
                alert('You must agree with our terms and conditions before uploading')
            }
        }
    };

});
