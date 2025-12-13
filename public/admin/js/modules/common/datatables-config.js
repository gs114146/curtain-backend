/**
 * Simple DataTables Configuration for Colors CRUD
 */

// Create DataTablesConfig object
var DataTablesConfig = {
    /**
     * Initialize Colors DataTable
     */
    initColorsTable: function () {
        return $('#colorsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routes.admin.colors.index,
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                {
                    data: 'color_value',
                    name: 'color_value',
                    render: function (data) {
                        return `
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 20px; height: 20px; background-color: ${data}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                <span>${data}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        const badgeClass = data === 'active' ? 'success' : 'secondary';
                        return `<span class="badge bg-${badgeClass}">${data}</span>`;
                    }
                },
                { data: 'products_count', name: 'products_count' },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `
                            <button class="btn  btn-outline-primary btn-edit me-1" data-id="${data}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn  btn-outline-danger btn-delete" data-id="${data}">
                                <i class="bx bx-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                processing: "Loading...",
                emptyTable: "No colors found",
                zeroRecords: "No matching colors found"
            }
        });
    },

    /**
     * Refresh DataTable
     */
    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

// Make available globally
window.DataTablesConfig = DataTablesConfig;

// Debug: Log that DataTablesConfig is loaded
// console.log('DataTablesConfig loaded:', typeof DataTablesConfig);
// console.log('DataTablesConfig.initColorsTable:', typeof DataTablesConfig.initColorsTable);