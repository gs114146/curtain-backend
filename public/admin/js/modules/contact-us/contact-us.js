$(document).ready(function () {
    const hasTable = $('#contactUsTable').length > 0;

    if (hasTable) {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#contactUsTable')) {
            $('#contactUsTable').DataTable();
        } else {
            if (typeof DataTablesContactUsConfig === 'undefined' || typeof DataTablesContactUsConfig.initContactUsTable !== 'function') {
                console.error('DataTablesContactUsConfig missing');
            } else {
                DataTablesContactUsConfig.initContactUsTable();
            }
        }
    }
});

