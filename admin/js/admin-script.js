jQuery(document).ready(function($) {
    const messagesDiv = $('#sxtpp-messages');
    const scanButton = $('#sxtpp-scan-button');
    const createButton = $('#sxtpp-create-button');
    const scanSpinner = $('.sxtpp-spinner');
    const createSpinner = $('.sxtpp-create-spinner');
    const sitemapUrlInput = $('#sxtpp_sitemap_url');
    const resultsContainer = $('#sxtpp-scan-results');
    const resultsTbody = $('#sxtpp-results-tbody');
    const resultsCount = $('#sxtpp-results-count');
    const masterCheckbox = $('#sxtpp-master-checkbox');
    const masterCheckboxFooter = $('#sxtpp-master-checkbox-footer');
    const selectAllButton = $('#sxtpp-select-all');
    const deselectAllButton = $('#sxtpp-deselect-all');
    const selectAllButtonBottom = $('#sxtpp-select-all-bottom');
    const deselectAllButtonBottom = $('#sxtpp-deselect-all-bottom');

    // Function to display messages
    function showMessage(type, message) {
        messagesDiv.html(`<div class="notice sxtpp-message notice-${type} is-dismissible"><p>${message}</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>`);
        messagesDiv.find('.notice-dismiss').on('click', function() {
            $(this).closest('.notice').remove();
        });
    }

    // Handle scan button click
    scanButton.on('click', function(e) {
        e.preventDefault();
        messagesDiv.empty();
        resultsContainer.hide();
        resultsTbody.empty();
        createButton.hide();

        const sitemapUrl = sitemapUrlInput.val();
        const defaultPostType = $('input[name="sxtpp_default_post_type"]:checked').val();

        if (!sitemapUrl) {
            showMessage('error', sxtpp_ajax_object.scan_error_empty_url || 'Please enter a sitemap URL.');
            return;
        }

        scanButton.prop('disabled', true).text(sxtpp_ajax_object.loading_text);
        scanSpinner.addClass('is-active');

        // Placeholder AJAX call (actual implementation in PHP)
        $.ajax({
            url: sxtpp_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'sxtpp_scan_sitemap',
                sitemap_url: sitemapUrl,
                default_post_type: defaultPostType,
                nonce: sxtpp_ajax_object.scan_nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message);
                    // Placeholder for rendering results (will be implemented after sitemap parser)
                    //resultsTbody.html('<tr><td colspan="5">' + sxtpp_ajax_object.scan_success_message + '</td></tr>');
                    
                    if (response.success) {
                        showMessage('success', response.data.message);
                        resultsCount.text(`(${response.data.count} items found)`);
                        resultsContainer.show();
                        createButton.show(); // Show create button after scan

                        if (response.data.count > 0) {
                            resultsTbody.empty(); // Clear existing content
                            $.each(response.data.items, function(index, item) {
                                const row = `
                                    <tr>
                                        <th scope="row" class="check-column">
                                            <input type="checkbox" name="sxtpp_item_to_create[]" value="${encodeURIComponent(item.original_url)}" data-title="${encodeURIComponent(item.suggested_title)}" data-slug="${encodeURIComponent(item.suggested_slug)}" data-type="${encodeURIComponent(item.suggested_type)}">
                                        </th>
                                        <td class="title column-title"><strong>${item.suggested_title}</strong></td>
                                        <td class="slug column-slug">${item.suggested_slug}</td>
                                        <td class="type column-type">${item.suggested_type}</td>
                                        <td class="url column-url">${item.original_url}</td>
                                    </tr>
                                `;
                                resultsTbody.append(row);
                            });
                            // Uncheck master checkboxes if new results are loaded
                            masterCheckbox.prop('checked', false);
                            masterCheckboxFooter.prop('checked', false);

                        } else {
                            resultsTbody.html(`<tr><td colspan="5">${sxtpp_ajax_object.scan_no_results_message || 'No URLs found in the sitemap or all URLs were filtered out.'}</td></tr>`);
                            createButton.hide(); // Hide create button if no items
                        }

                    } else {
                        showMessage('error', response.data.message || 'An unknown error occurred during scan.');
                        resultsTbody.html(`<tr><td colspan="5">${sxtpp_ajax_object.scan_error_message || 'Error scanning sitemap.'}</td></tr>`);
                        createButton.hide();
                    }

                    resultsCount.text(`(${response.data.count || 0} items found - placeholder)`);
                    resultsContainer.show();
                    createButton.show(); // Show create button after scan (for now)
                } else {
                    showMessage('error', response.data.message || 'An unknown error occurred during scan.');
                }
            },
            error: function(xhr, status, error) {
                showMessage('error', sxtpp_ajax_object.scan_error_ajax || `AJAX Error: ${status} - ${error}`);
            },
            complete: function() {
                scanButton.prop('disabled', false).text(sxtpp_ajax_object.scan_button_text);
                scanSpinner.removeClass('is-active');
            }
        });
    });

// Handle create button click
createButton.on('click', function(e) {
    e.preventDefault();
    messagesDiv.empty();

    const selectedItems = [];
    resultsTbody.find('input[type="checkbox"]:checked').not(masterCheckbox).each(function() { // Exclude master checkbox
        const row = $(this).closest('tr');
        selectedItems.push(JSON.stringify({ // Stringify object for easier transmission
            original_url: decodeURIComponent($(this).val()),
            suggested_title: decodeURIComponent($(this).data('title')),
            suggested_slug: decodeURIComponent($(this).data('slug')),
            suggested_type: decodeURIComponent($(this).data('type'))
        }));
    });

    if (selectedItems.length === 0) {
        showMessage('error', sxtpp_ajax_object.create_error_no_selection || 'Please select at least one item to create.');
        return;
    }

    createButton.prop('disabled', true).text(sxtpp_ajax_object.loading_text);
    createSpinner.addClass('is-active');

    $.ajax({
        url: sxtpp_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'sxtpp_create_posts_pages',
            items: selectedItems, // Pass selected items as an array of JSON strings
            nonce: sxtpp_ajax_object.create_nonce
        },
        success: function(response) {
            if (response.success) {
                showMessage('success', response.data.message);
            } else {
                // For errors, display the main message and then any specific error details.
                let errorMessage = response.data.message || 'An unknown error occurred during creation.';
                if (response.data.details && response.data.details.length > 0) {
                    errorMessage += '<br><ul>';
                    $.each(response.data.details, function(i, detail) {
                        errorMessage += `<li>${detail}</li>`;
                    });
                    errorMessage += '</ul>';
                }
                showMessage('error', errorMessage);
            }

            // Remove successfully created rows from the table, regardless of overall success/failure
            if (response.data.created_urls && response.data.created_urls.length > 0) {
                $.each(response.data.created_urls, function(i, createdUrl) {
                    // Find the row by matching the original_url data attribute
                    resultsTbody.find(`input[value="${encodeURIComponent(createdUrl)}"]`).closest('tr').remove();
                });
                // Update the count displayed
                const currentCount = parseInt(resultsCount.text().replace(/\D/g, '')) || 0;
                resultsCount.text(`(${currentCount - response.data.created_urls.length} items remaining)`);
                if (resultsTbody.find('tr').length === 0) {
                    resultsTbody.html(`<tr><td colspan="5">${sxtpp_ajax_object.scan_no_results_message || 'No results yet. Scan a sitemap to see items.'}</td></tr>`);
                    createButton.hide();
                    resultsContainer.hide();
                }
                // Deselect master checkboxes
                masterCheckbox.prop('checked', false);
                masterCheckboxFooter.prop('checked', false);
            }
        },
        error: function(xhr, status, error) {
            showMessage('error', sxtpp_ajax_object.create_error_ajax || `AJAX Error: ${status} - ${error}`);
        },
        complete: function() {
            createButton.prop('disabled', false).text(sxtpp_ajax_object.create_button_text);
            createSpinner.removeClass('is-active');
        }
    });
});

    // Master checkbox logic (basic)
    masterCheckbox.on('change', function() {
        resultsTbody.find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
        masterCheckboxFooter.prop('checked', $(this).prop('checked'));
    });
    masterCheckboxFooter.on('change', function() {
        resultsTbody.find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
        masterCheckbox.prop('checked', $(this).prop('checked'));
    });

    // Select all/deselect all buttons
    selectAllButton.add(selectAllButtonBottom).on('click', function() {
        resultsTbody.find('input[type="checkbox"]').prop('checked', true);
        masterCheckbox.prop('checked', true);
        masterCheckboxFooter.prop('checked', true);
    });

    deselectAllButton.add(deselectAllButtonBottom).on('click', function() {
        resultsTbody.find('input[type="checkbox"]').prop('checked', false);
        masterCheckbox.prop('checked', false);
        masterCheckboxFooter.prop('checked', false);
    });
});