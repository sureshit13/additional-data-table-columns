jQuery(document).ready(function($) {
    $('#loader').hide(); 
     
    // on change event
    $('#post_type').on('change', function() {
        var postType = $(this).val();
        if (postType) {
                $.ajax({
                url: sc_ajax_obj.ajax_url,
                method: 'POST',
                data: {
                    action: 'sc_get_meta_fields',
                    post_type: postType
                },
                beforeSend: function() {
                    $('#loader').show(); // Show the loader before the AJAX call is sent
                },
                success: function(response) {
                    $('#meta-fields').show();
                    $('#meta-fields').html(response);
                    $('#meta-fields').prepend('<input id="check_all" type="checkbox" name="check_all" value="All"> All<br><br>');
                },
                complete: function() {
                    $('#loader').hide(); // Hide the loader when the request is complete
                }
            });
        }else{
            $('#meta-fields').hide();
        }
    });
    
    // on form submit even with ajax call function
    $('#sc-meta-fields-form').on('submit', function(e) {
        e.preventDefault();
    
        var postType = $('#post_type').val();
        var nonce = $('#sc-meta-fields-nonce').val();
    
        var selectedMetaKeys = [];
        var uncheckedMetaKeys = [];
        $('input[name="meta_keys[]"]').each(function() {
            if ($(this).is(':checked')) {
                selectedMetaKeys.push($(this).val()); // Add to checkedMetaKeys array
            } else {
                uncheckedMetaKeys.push($(this).val()); // Add to uncheckedMetaKeys array
            }
        });
        
        $.ajax({
            type: 'POST',
            url: sc_ajax_obj.ajax_url,
            data: {
                action: 'sc_get_form_data',
                nonce: nonce,
                post_type: postType,
                meta_keys: selectedMetaKeys,
                uncheck_meta_keys:uncheckedMetaKeys
            },
            success: function(response) {
                response = JSON.parse(response);
                if (response.status === 'success') {
                    $('#sc-meta-fields-form')[0].reset();
                    $('#meta-fields').hide();
                    Swal.fire({
                        html: '<h3 class="text-success">Success Modify Column.</h3>',
                        icon: 'success'
                    }); 
                } else {
                    console.log(response.message);
                }
            }
        });
    });

    // Checkbox event for #check_all
    $(document).on('change', '#check_all', function() {
        var isChecked = $(this).prop('checked');
        $('input[name="meta_keys[]"]').prop('checked', isChecked);
    });
});

