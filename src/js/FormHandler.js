jQuery(document).ready(function($) {

    $('#btnWidgetSubmit').click(function(e) {
        e.preventDefault();
        let input = $('#widgetCount').val();
        console.log("Passing through " + input);
        $.ajax({
            url: "/src/php/WidgetRequest.php",
            type: "GET",
            data: { widgetCount: input},
            success: function (response) {
                console.log("Success?");
                console.log(response + " < Response");
                $('#requestOutput').html(response);
            },
            error: function (request, status, error) {
                console.error(request.responseText);
                console.error(status.responseText);
                console.error(error.responseText);
            }
        });
    });

    console.log("Ready");

});