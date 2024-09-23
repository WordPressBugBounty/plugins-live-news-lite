jQuery(document).ready(function($) {

    'use strict';

    //Handle changes of the news ticker filter
    $('#daext-filter-form select').on('change', function() {

        'use strict';

        $('#daext-filter-form').submit();

    });

    //do not set the default values if we are editing an existing sliding news
    if ( $( "#update-id" ).length ){return;}

    daextlnl_update_default_colors();

    $('#ticker-id').change(function(){

        'use strict';

        daextlnl_update_default_colors();

    });

    /*
     * Update the default 'Text Color', 'Text Color Hover' and 'Background Color' based on the values available on the
     * related ticker
     */
    function daextlnl_update_default_colors(){

        'use strict';

        //When the menu doesn't have the #ticker-id field available return
        if(!$('#ticker-id').length){return;}

        let ticker_id = parseInt($('#ticker-id').val(), 10);

        //prepare input for the ajax request
        let data = {
            "action": "update_default_colors",
            "security": daextlnl_nonce,
            "ticker_id": ticker_id
        };

        //ajax
        $.post(daextlnl_ajax_url, data, function(result_json) {

            'use strict';

            let data_obj = JSON.parse(result_json);

            $('#text-color').iris('color', data_obj.sliding_news_color);
            $('#text-color-hover').iris('color', data_obj.sliding_news_color_hover);
            $('#background-color').iris('color', data_obj.sliding_news_background_color);

        });

    }

});