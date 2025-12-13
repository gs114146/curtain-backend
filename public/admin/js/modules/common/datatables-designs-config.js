var DataTablesDesignsConfig = {
    initDesignsTable: function () {
        return $('#designsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routes.admin.designs.index,
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
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
                emptyTable: "No designs found",
                zeroRecords: "No matching designs found"
            }
        });
    },
    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

window.DataTablesDesignsConfig = DataTablesDesignsConfig;
