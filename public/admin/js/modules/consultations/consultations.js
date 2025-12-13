$(document).ready(function () {
    const hasTable = $('#consultationsTable').length > 0;

    if (hasTable) {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#consultationsTable')) {
            $('#consultationsTable').DataTable();
        } else {
            if (typeof DataTablesConsultationsConfig === 'undefined' || typeof DataTablesConsultationsConfig.initConsultationsTable !== 'function') {
                console.error('DataTablesConsultationsConfig missing');
            } else {
                DataTablesConsultationsConfig.initConsultationsTable();
            }
        }
    }
});

