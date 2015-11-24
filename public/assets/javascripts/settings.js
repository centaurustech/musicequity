$(document).ready(function () {


    /*
     -- Page background via Backstretch.js
     */

    $.backstretch("assets/images/background.jpg");

   $('#upload-picture').click(function () {
       $('#picture').click();
   });


    /*
    -- Checking for "dirty" form
     */

    $('form').areYouSure();

    /*
    -- Tooltips initialization
     */


    $('[data-toggle="tooltip"]').tooltip({html:true})


    function ReadData(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {

                var image = $('<img id="crop"/>').attr('src', e.target.result);

                if(image[0].naturalHeight < 210 || image[0].naturalWidth < 210) {
                    alert('Image is too small, should be at least 210x210 px')
                }
                else {
                    $('#image-crop').modal('show');
                    $('.image-container').empty();
                    $('.image-container').append(image);
                    $("#crop").cropper({
                        aspectRatio: 210 / 210,
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

    $('.delete-link').click(function () {
        var id = $(this).attr('data-delete');
        var url = '/link/delete/' + id;

        $.post(url, function (data) {
            if(data.success) {
                $('#link' + id).remove();
            }
        })

    });

    $('#AddLink').click(function() {
        var block = $('#LinksWrap');
        var link = $('#links');

        if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(link.val())) {
            $('#form-group-links').removeClass('has-error');
            $('.help-block').addClass('hide');
            $('.help-block').hide();
            block.append('<input type="hidden" name="web[]" value="'+ link.val() +'">');
            block.append('<div class="link"><a href="'+ link.val() +'">'+ link.val() +'</a></a></div>');
            link.val("");
        } else {
            $('#form-group-links').addClass('has-error');
            $('.help-block').removeClass('hide');
        }
    });

    $('#do-crop').click(function () {
        var image = $('#crop').cropper("getDataURL");
        var selection = $('#crop').cropper("getData");
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        btn.html('Loading...');
        $.ajax({
            type: "POST",
            url: "/postimage",
            enctype: 'multipart/form-data',
            data: {
                file: image,
                selection: selection
            },
            success: function (data) {
                if(data.success) {
                    url = '/profile/picture/' + data.id + '/large';
                    $('#crop').cropper("destroy")
                    $('.profilepic').empty();
                    var newimage = $('<img />').attr('src', url);
                    $('#picture_id').val(data.id);
                    $('.profilepic').append(newimage);
                    $('#image-crop').modal('hide');
                    btn.attr('disabled', false);
                    btn.html('Crop');
                }
            }
        });
    });

    $('.close').click(function () {
        //$(this).parent().parent().parent().hide();
    });


    $("#picture").change(function(){
        ReadData(this);
    });

});

