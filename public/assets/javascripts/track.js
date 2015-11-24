$(document).ready(function () {

    /*
     -- Page background via Backstretch.js
     */

    $.backstretch("assets/images/background.jpg");

    $('#comment-form').on('submit', function(e) {
        e.preventDefault();
        var comment = $(this).find('input[type=text]').val();

        $.post('comment', $( "#comment-form" ).serialize(), function(data) {
           if(data.success) {

               var code = '<div class="comment-box"> <div class="comment-art pull-left"> <img src="/profile/picture/'+ picture +'/medium" alt=""/> </div> <div class="comment-body pull-left"> <div class="comment-title"> ' + name + ' <span class="small">a moment ago</span> </div> <div class="comment-text"> ' + comment + '</div> </div> <div class="clearfix"></div> </div>';

               $('#lastdiv').prepend(code);


           }
        });
        $(this).find('input[type=text]').val('');

    })

});