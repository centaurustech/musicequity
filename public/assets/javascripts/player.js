$(document).ready(function () {
    playing = false;
    current = '';

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

});