jQuery(document).ready(function($) {

    remove_border_last_cell_chart();

    //.group-trigger -> click - EVENT LISTENER
    $(document.body).on('click', '.group-trigger' , function(){

        //open and close the various sections of the chart area
        let target = $(this).attr('data-trigger-target');
        $('.' + target).toggle(0);
        $(this).find('.expand-icon').toggleClass('arrow-down');

        remove_border_last_cell_chart();

    });

    /*
     Remove the bottom border on the cells of the last row of the chart section
     */
    function remove_border_last_cell_chart(){
        $('table.daext-form tr > *').css('border-bottom-width', '1px');
        $('table.daext-form tr:visible:last > *').css('border-bottom-width', '0');
    }

});