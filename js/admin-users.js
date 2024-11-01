/* Users Table */
jQuery(document).ready(function($) {
    let currentPage = 1;
    let currentSearch = '';

    $('#tefw_exempt_users_table').html('<div class="tefw-loading"><span class="spinner is-active" style="float: left; margin-top: 0px;"></span> Loading...</div>');
    

    function fetchUsers(page, search = '') {
        $.post(ajaxurl, {
            action: "get_tefw_exempt_users",
            page: page,
            search: search
        }, function(response) {
            $('#tefw_exempt_users_table').html(response.html);
            currentPage = page;
            currentSearch = search;
            
            // Event delegation for tefw-pagination
            $('body').on('click', '.tefw-page-btn', function(event) {
                event.preventDefault();  // Prevent form submission
                let page = $(this).data('page');
                fetchUsers(page);
            });
            // Handle tefw-pagination
            let totalPages = Math.ceil(response.total / 20);
            $('#tefw-pagination').html('');
            for (let i = 1; i <= totalPages; i++) {
                $('#tefw-pagination').append('<button class="tefw-page-btn" data-page="' + i + '">' + i + '</button>');
            }
        });
    }

    fetchUsers(1); // Fetch the first page initially

    // Event delegation for tefw-pagination
    $('body').on('click', '.tefw-page-btn', function(event) {
        event.preventDefault();
        fetchUsers($(this).data('page'), currentSearch);
    });

    // Search field event
    $('#tefw_search_field').on('keyup', function() {
        currentSearch = $(this).val();
        fetchUsers(1, currentSearch); // Start from the first page
    });

    /* Toggle "Add New" Form */
    $('#add-new-btn').click(function(e) {
        e.preventDefault();
        $('#add-new-form').toggle();
    });
    
    // Handle "Add New" Form Submission
    $('#new-exempt-form').submit(function(e) {
        e.preventDefault();
    
        const formData = new FormData(this);
        formData.append('action', 'add_new_exempt_user');
    
        $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                // Clear the form fields
                $('#new-exempt-form').trigger('reset');
                // Hide the form
                $('#add-new-form').hide();
                // Search for the new user
                $('#tefw_search_field').val(response.data);
                $('#tefw_search_field').trigger('input');
                fetchUsers(1, response.data);
            } else {
                // Handle errors
                alert(response.data);
            }
        }
        });
    });

    /* User Edit Status */
    $(document).on('change', 'td select[name="tefw_exempt_status"]', function() {
        var userId = $(this).data('user-id');
        var status = $(this).val();
        $.ajax({
            url: tefw_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'update_tefw_exempt_status',
                user_id: userId,
                status: status,
                nonce: tefw_ajax_object.nonce
            },
            success: function(response) {
                if(response.success) {
                    // highlight the row for 2 seconds
                    $('#user-' + userId).addClass('tefw-highlight');
                    setTimeout(function() {
                        $('#user-' + userId).removeClass('tefw-highlight');
                    }, 1000);
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
