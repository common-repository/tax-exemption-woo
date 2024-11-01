/* Tab Toggles */
jQuery(document).ready(function ($) {
    $('.nav-tab').on('click', function (e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        var target = $(this).attr('href');
        $('.tab-panel').removeClass('active');
        $(target).addClass('active');
    });
});
/* Only show tax-exemption-certificate-enabled if certificate is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-certificate-enabled').hide();
    $('.tefw_certificate_enable input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-certificate-enabled').show();
        } else {
            $('.tax-exemption-certificate-enabled').hide();
        }
    });
    $('.tefw_certificate_enable input[type="checkbox"]').change();
});
/* Only show tax-exemption-product-enabled if product is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-product-enabled').hide();
    $('.tefw_product_enable input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-product-enabled').show();
        } else {
            $('.tax-exemption-product-enabled').hide();
        }
    });
    $('.tefw_product_enable input[type="checkbox"]').change();
});
/* Only show tax-exemption-approval-enabled if approval is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-approval-enabled').hide();
    $('.tefw_approval_enable input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-approval-enabled').show();
        } else {
            $('.tax-exemption-approval-enabled').hide();
        }
    });
    $('.tefw_approval_enable input[type="checkbox"]').change();
});
/* Only show tax-exemption-approval-message if tefw_approval_message is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-approval-message').hide();
    $('.tax-exemption-approval-enabled input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-approval-message').show();
        } else {
            $('.tax-exemption-approval-message').hide();
        }
    });
    $('.tax-exemption-approval-enabled input[type="checkbox"]').change();
});
/* Only show tax-exemption-selected-user-roles if user roles is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-selected-user-roles').hide();
    $('.tax-exemption-user-roles-enable input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-selected-user-roles').show();
        } else {
            $('.tax-exemption-selected-user-roles').hide();
        }
    });
    $('.tax-exemption-user-roles-enable input[type="checkbox"]').change();
});
/* Only show tax-exemption-user-countries-enable if countries is enabled */
jQuery(document).ready(function($) {
    $('.tax-exemption-selected-countries').hide();
    $('.tax-exemption-countries input[type="checkbox"]').change(function() {
        if ($(this).is(':checked')) {
            $('.tax-exemption-selected-countries').show();
        } else {
            $('.tax-exemption-selected-countries').hide();
        }
    });
    $('.tax-exemption-countries input[type="checkbox"]').change();
});
/* Prefill fields on edit */
jQuery(document).ready(function($) {
    $(document).on('click', 'a.tefw-edit-user', function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., navigation)

        // Fetch the user ID from the data attribute
        const userId = $(this).data('user-id');

        // Fetch the row corresponding to this user
        const row = $('#user-' + userId);

        // Fetch individual pieces of user data from the row

        // If checkbox inside td.tefw_exempt is checked set value to "yes"
        const exempt = row.find('td.tefw_exempt').find('input[type="checkbox"]').is(':checked') ? '1' : '0';


        const username = row.find('td.tefw_exempt_username').text();
        const name = row.find('td.tefw_exempt_name').text();
        const reason = row.find('td.tefw_exempt_reason').text();
        const expiration = row.find('td.tefw_exempt_expiration').text();
        const tefw_exempt = row.find('td.tefw_exempt').val();
        const custom1 = row.find('td.tefw_exempt_custom_1').text();
        const custom2 = row.find('td.tefw_exempt_custom_2').text();
        const custom3 = row.find('td.tefw_exempt_custom_3').text();
        const custom4 = row.find('td.tefw_exempt_custom_4').text();
        const custom5 = row.find('td.tefw_exempt_custom_5').text();
        
        const status = row.find('td.tefw_exempt_status').find('select').val();

        // Set #tefw_current_file_current to the current file name
        const currentFile = row.find('td.tefw_exempt_file').html();
        const currentFileName = row.find('input.tefw_exempt_file_name').val();
        $('#tefw_current_file').html(currentFile);
        // Edit text but not link to say "Current File"
        $('#tefw_current_file').find('a.tefw_exempt_file_icon').hide();
        $('#tefw_current_file').find('a.tefw_exempt_file_text').text(currentFileName);
        // add delete button
        if(currentFileName) {
            $('#tefw_current_file').append(' - (<a href="#" class="tefw-delete-file-button" data-user-id="' + userId + '">Delete</a>)');
            // add a line break to current file
            $('#tefw_current_file').append('<br/>');
        }

        // Pre-fill the form fields with the fetched data
        $('#username').val(username);
        $('#name').val(name);
        $('#reason').val(reason);
        $('#expiration').val(expiration);
        // If tefw_exempt is one set checkbox to checked, else unchecked
        $('#tefw_exempt').prop('checked', exempt === '1');

        // Handle custom fields
        const customFields = [
            'tefw_exempt_custom_1',
            'tefw_exempt_custom_2',
            'tefw_exempt_custom_3',
            'tefw_exempt_custom_4',
            'tefw_exempt_custom_5'
        ];
        customFields.forEach(function(field) {
            const fieldValue = row.find('td.' + field).text();
            const $inputField = $('#' + field);
            const inputFieldType = $inputField.prop('type');

            // Check the type of the input field and populate it accordingly
            if (inputFieldType === 'text') {
                $inputField.val(fieldValue);
            } else if (inputFieldType === 'checkbox') {
                $inputField.prop('checked', fieldValue === '1');  // Assuming 'Yes' means checked
            } else if (inputFieldType === 'radio') {
                $inputField.filter('[value="' + fieldValue + '"]').prop('checked', true);
            } else if (inputFieldType === 'select') {
                $inputField.val(fieldValue);
            }
        });

        // Prefil Tax Class
        const taxClass = row.find('.tefw_tax_class').text();
        if($('#tax_class option[value="' + taxClass + '"]').length > 0) {
            $('#tax_class').val(taxClass);
        }

        // Prefill AvaTax
        const avatax = row.find('.tefw_exempt_avatax_code').text();
        $('#wc_avatax_tax_exemption').val(avatax);

        // Update select field to value
        $('#tefw_exempt_status').val(status);

        // Show the form
        $('#add-new-form').show();

        // Scroll to the top of the form -100px
        $('html, body').animate({
            scrollTop: $("#add-new-form").offset().top - 90
        }, 100);

    });
    /* tefw-clear-button - clear fields */
    $('.tefw-clear-button').click(function(e) {
        e.preventDefault();
        $('#username').val('');
        $('#name').val('');
        $('#reason').val('');
        $('#expiration').val('');
        $('#tefw_exempt').val('1');
        $('#tefw_exempt_custom_1').val('');
        $('#tefw_exempt_custom_2').val('');
        $('#tefw_exempt_custom_3').val('');
        $('#tefw_exempt_custom_4').val('');
        $('#tefw_exempt_custom_5').val('');
        $('#tefw_exempt_file').val('');
        $('#tefw_current_file').html('');
        $('#tefw_exempt_status').val('');
    });
});

// Hide save button on users tab
jQuery(document).ready(function($) {
    $('.nav-tab-wrapper a').click(function() {
        if ($(this).attr('href') === '#users') {
            $('.submit').hide();
        } else {
            $('.submit').show();
        }
    });

    // New functionality to clear the search field
    $('#clear-search').hide(); // Initially hide the clear button
    
    // Show the clear button when there is text in the input field
    $('#tefw_search_field').on('input', function() {
        if ($(this).val().length > 0) {
        $('#clear-search').show();
        } else {
        $('#clear-search').hide();
        }
    });

    // Clear the input field when the clear button is clicked
    $('#clear-search').click(function() {
        $('#tefw_search_field').val('');
        $(this).hide();
        $('#tefw_search_field').keyup();
    });

});

// Delete file button handler - run wp_ajax_tefw_delete_tax_exemption_file action
jQuery(document).ready(function($) {
    $(document).on('click', 'a.tefw-delete-file-button', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const row = $('#user-' + userId);
        const currentFile = row.find('td.tefw_exempt_file').html();
        const currentFileName = row.find('input.tefw_exempt_file_name').val();
        const data = {
            action: 'delete_tax_exemption_file',
            user_id: userId,
            file_name: currentFileName,
            nonce: tefw_ajax_object.nonce
        };
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                // Clear the file name and link
                $('#tefw_current_file').html('');
                // Hide the delete file button
                $('a.tefw-delete-file-button').hide();
                // Update the file name in the table
                row.find('td.tefw_exempt_file').html('');
                row.find('input.tefw_exempt_file_name').val('');
            } else {
                alert('Failed to delete file.');
            }
        });
    });
});

// Show/hide options based on field type
jQuery(document).ready(function($) {

    // Function to toggle field options based on field type
    function tefw_toggleFieldOptions(fieldType) {
        var optionsRow = $('.tax-exemption-field-options', $(fieldType).closest('.tax-exemption-custom-fields-table'));
        if ($(fieldType).val() === 'select' || $(fieldType).val() === 'radio') {
            optionsRow.show();
        } else {
            optionsRow.hide();
        }
    }

    // Initial toggle
    tefw_toggleFieldOptions('.tax-exemption-field-type');

    // Toggle field options on field type change
    $('.tax-exemption-field-type').change(function() {
        tefw_toggleFieldOptions(this);
    });

    $('.tax-exemption-field-type').change();

});