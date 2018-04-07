(function ($) {
    $(document).ready(function () {
        $('.add-fav').click(function () {
            // Gets imdb move id from the button id (lol, seriously?)
            var id = $(this).attr('id').replace('fav-','');

            // Visual feedback
            $(this).html('Added!');
            $(this).prop("disabled",true);

            $.post(
                    PT_Ajax.ajaxurl,
                    {
                        // wp ajax action
                        action: 'ajax-bravoAddFav',

                        // vars
                        imdbID: id,

                        // send the nonce along with the request
                        nextNonce: PT_Ajax.nextNonce
                    },
                    function (response) {
                        console.log(response);
                    }
            );
            return false;
        });

    });
    })(jQuery);