jQuery(document).ready(function($) {
    // initialize editable for price
    $.fn.editable.defaults.mode = 'inline';

    function searchDevice() {
        var deviceModel = $('#device-model').val();
        $('#loading-indicator').css('visibility', 'visible');
        $.ajax({
            url: acf_device_prices_ajax.ajax_url,
            type: 'post',
            data: {
                action: 'search_device_prices',
                device_model: deviceModel
            },
            success: function(response) {
                var data = JSON.parse(response);
                var tableHtml = "<table><tr><th>Group</th><th>Service</th><th>Price</th></tr>";
                for(var i = 0; i < data.length; i++){
                    tableHtml += "<tr><td>"+data[i].group+"</td><td>"+data[i].name+"</td><td class='editable' data-pk='"+data[i].group+"|"+data[i].name+"' data-type='text' data-url='"+acf_device_prices_ajax.ajax_url+"' data-title='Enter price' style='background-color: #F9F9F9; cursor: pointer;'><i class='fas fa-pencil-alt' style='margin-right: 5px;'></i>"+data[i].price+"</td></tr>";
                }
                tableHtml += "</table>";
                $('#prices-table').html(tableHtml);
                $('#loading-indicator').css('visibility', 'hidden');
                $('.editable').editable({
                    params: function(params) {
                        params.action = 'update_device_price';
                        return params;
                    },
                    success: function(response, newValue) {
                        if(response.status == 'error') alert(response.message);
                    }
                });
            }
        });
    }

    $('#search-device').click(searchDevice);

    $('#device-model').on('keypress', function(e){
        if(e.which == 13) { // 13 is the enter key code
            e.preventDefault();
            searchDevice();
        }
    });
});

/* jQuery(document).ready(function($) {
    // initialize editable for price
    $.fn.editable.defaults.mode = 'inline';

    $('#search-device').click(function() {
        var deviceModel = $('#device-model').val();
        $('#loading-indicator').css('visibility', 'visible');
        $.ajax({
            url: acf_device_prices_ajax.ajax_url,
            type: 'post',
            data: {
                action: 'search_device_prices',
                device_model: deviceModel
            },
            success: function(response) {
                var data = JSON.parse(response);
                var tableHtml = "<table><tr><th>Group</th><th>Service</th><th>Price</th></tr>";
                for(var i = 0; i < data.length; i++){
                    tableHtml += "<tr><td>"+data[i].group+"</td><td>"+data[i].name+"</td><td class='editable' data-pk='"+data[i].group+"|"+data[i].name+"' data-type='text' data-url='"+acf_device_prices_ajax.ajax_url+"' data-title='Enter price' style='background-color: #F9F9F9; cursor: pointer;'><i class='fas fa-pencil-alt' style='margin-right: 5px;'></i>"+data[i].price+"</td></tr>";
                }
                tableHtml += "</table>";
                $('#prices-table').html(tableHtml);
                $('#loading-indicator').css('visibility', 'hidden');
                $('.editable').editable({
                    params: function(params) {
                        params.action = 'update_device_price';
                        return params;
                    },
                    success: function(response, newValue) {
                        if(response.status == 'error') alert(response.message);
                    }
                });
            }
        });
    });
});
 */