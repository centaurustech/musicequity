$(document).ready(function () {
    play = false;
    start = 0;
    startpx = 0;
    onepixel = 0;

    /*
     -- Page background via Backstretch.js
     */

    $.backstretch("assets/images/background.jpg");


    var check_assembly = function () {

        var result = null;
        var url = "check_assembly";
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            async: false,
            success: function(data) {
                console.log(data);
                if(!data.done) {
                    if(data.failed) {
                        $('#processing').hide();
                        $('.fail').show();
                    }
                    else{
                        setTimeout(function(){check_assembly();}, 3000);
                    }

                }
                else {

                    $('#processing').hide();
                    onepixel = data.duration/295;
                    $('#canvas-song-' + song).drawImage({
                        layer:true,
                        source: 'thumbnail/' + song,
                        x: 147.5, y: 27.5,
                        click: function(layer) {
                            position = layer._eventX;
                            s = (data.duration * position) / 295

                            changePosition('song-'+ song, s*1000);
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
                    $('#canvas-song-' + song).moveLayer('slider', 9000);
                    $('.preview').show();
                    $('#NewSongForm').show();

                }
            }
        });
        return result;
    }
    check_assembly();

    function hideProgress(canvas) {
        canvas.setLayer('progress', {
            width:0
        }).drawLayers();
    }

    function drawProgress(canvas, start, position, duration) {

        diff = (position - start * 1000) / 1000;
        var blockwidth = (diff * 295) / (duration / 1000)

        canvas.setLayer('progress', {
            width:blockwidth,
            x:startpx
        }).drawLayers();


    }


    function changePosition(id, position) {
        var song = soundManager.getSoundById(id);
        song.setPosition(position);
    }

    function setPlay(elem) {
        elem.removeClass('glyphicon-play');
        elem.addClass('glyphicon-pause');
        play = true;
    }

    function setPause(elem) {
        elem.removeClass('glyphicon-pause');
        elem.addClass('glyphicon-play');
        play = false;
    }

    function playSong(song, end) {
        if(play == false) {
            song.play({
                whileplaying: function () {
                    drawProgress($('.canvas'), start, this.position, this.duration)
                    if(this.position >= (start*1000) + (end *1000) ) {
                        this.stop();
                    }
                },
                onstop: function () {
                    hideProgress($('.canvas'))
                    this.position = start;
                    setPause($('.play').find('span'));
                },
                whileloading: function() {
                    current = $('.play').find('span').attr('class');
                    $('.play').find('span').addClass('glyphicon-refresh');
                }
            });
            setPlay($('.play').find('span'));

        }
        else {
            song.pause();
            setPause($('.play').find('span'));
        }
    }

    $('.play').click(function() {
        var id = $(this).attr('data-id');
        var position = $(this).attr('data-start');
        var end = $(this).attr('data-end');
        position = position * 1000;
        var song = soundManager.getSoundById(id);
        playSong(song, 30);
    });

    $("#price").maskMoney({
        prefix: 'AUD$ ',
        thousands: ',',
        decimals: '.'
    });


    $('#genre').tagsInput({
        'height':'68px',
        'width':'100%',
        'interactive':true,
        'defaultText':'add genre'
    });

    $('select').selectpicker();

    $('#close_crop').click(function () {
        $('#image-crop').modal('hide');
        $('#crop').cropper("destroy")
    });

    jQuery.validator.addMethod("songprice", function(value, element) {
        return this.optional(element) || (parseFloat(value) > 1.24);
    }, "Minimum price is $1.25");

    $("#NewSongForm").validate({
        debug: true,
        submitHandler: function(form) {
            form.submit();
        },
        errorClass: "error-input",
        rules: {
            title: {
                required: true
            },
            price: {
                required: true,
                songprice: true
            },
            description: {
                required: true
            },
            art: "required"
        }
    });



    $('#do-crop').click(function () {
        var base = $('#crop').cropper("getDataURL");
        var field = $('<input name="art_cropped" type="hidden" />').attr('value', base);
        var image = $('<img />').attr('src', base);
        $('#NewSongForm').append(field);
        $('#image-crop').modal('hide');
        $('#crop').cropper("destroy")
        $('.song-art-container ').empty();
        $('.song-art-container ').append(image);

    });


    function ReadData(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var image = $('<img id="crop"/>').attr('src', e.target.result);
                if(image[0].naturalHeight <= 120 || image[0].naturalWidth <= 120) {
                    $('.song-art-container ').empty();
                    var image = $('<img />').attr('src', e.target.result);
                    $('.song-art-container ').append(image);
                    var base = e.target.result;
                    var field = $('<input name="art_cropped" type="hidden" />').attr('value', base);
                    $('#NewSongForm').append(field);
                }
                else {
                    $('#image-crop').modal('show');
                    $('.image-container').empty();
                    $('.image-container').append(image);
                    $("#crop").cropper({
                        aspectRatio: 120 / 120,
                        autoCropArea: 0.6, // Center 60%
                        multiple: false,
                        dragCrop: false,
                        dashed: false,
                        movable: true,
                        resizable: false,
                        built: function () {
                            $(this).cropper("zoom", 0.5);
                        }
                    });
                }


            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#art").change(function(){
        ReadData(this);
    });

    $('#choose').click(function() {
       $('#art').click();
    });

});