jQuery(document).ready( function($) {

    // CREATE NEW BACKUP FILE

    $('.fupi_make_new_backup_btn').click(function(e) {
        
        e.preventDefault(); 
        
        if (
            confirm( fupi_import_export_data.reload_notice_text )
        ) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fupi_ajax_make_new_backup',
                    nonce: fupi_import_export_data.import_export_nonce,
                },
                success: function(response) {
                    if (response.success) {                    
                        alert( fupi_import_export_data.new_backup_text );
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        }
    });

    // RESTORE FROM UPLOADED FILE

    let upload_field = FP.findID('fupi_upload_settings_file');

    if ( upload_field ) {

        $('.fupi_upload_backup_file_btn').click(function(e) {
            e.preventDefault();
            if ( 
                confirm( fupi_import_export_data.confirm_text ) 
            ) {
                upload_field.click();
            }
        });

        upload_field.addEventListener( 'change', e => {

            const file = e.target.files[0];

            if ( file ) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        var jsonContent = JSON.parse(e.target.result);
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'fupi_ajax_upload_settings_from_file',
                                nonce: fupi_import_export_data.import_export_nonce,
                                settings: jsonContent
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert( fupi_import_export_data.alert_success_text );
                                    window.location.reload();
                                } else {
                                    alert('Error: ' + response.data.message);
                                    window.location.reload();
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        alert( fupi_import_export_data.alert_error_text );
                    }
                };
                reader.readAsText(file);
            }
        });
    }

    // RESTORE FROM SAVED FILE

    $('.fupi_backup_restore').click(function(e) {
        e.preventDefault();
        if (
            confirm( fupi_import_export_data.confirm_text )
        ) {
            let filename_el = e.target.closest('.fupi_pseudo_table_row'),
                filename = filename_el.dataset.file;

            console.log( 'Sending a restore request of file ' + filename);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fupi_ajax_restore_settings_backup',
                    nonce: fupi_import_export_data.import_export_nonce,
                    file_name: filename
                },
                success: function(response) {
                    if (response.success) {
                        alert( fupi_import_export_data.alert_success_text );
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        window.location.reload();
                    }
                }
            });
        }
    } );

    // DELETE FILE
    
    $('.fupi_backup_delete').click(function(e) {
        e.preventDefault();

        if (
            confirm( fupi_import_export_data.reload_notice_text )
        ) {

            let filename_el = e.target.closest('.fupi_pseudo_table_row'),
                filename = filename_el.dataset.file;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fupi_ajax_remove_settings_backup',
                    nonce: fupi_import_export_data.import_export_nonce,
                    file_name: filename
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        window.location.reload();
                    }
                }
            });
        };
    });
} );