var DataTablesProductsConfig = {
    initProductsTable: function () {
        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            scrollX: false,
            ajax: {
                url: window.routes.admin.products.index,
                type: 'GET'
            },
            columns: [
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    width: '70px',
                    className: 'text-center',
                    render: function (data, type, row) {
                        const src = data || '';
                        const title = row.name || 'Product image';
                        const safeSrc = $('<div>').text(src).html();
                        const safeTitle = $('<div>').text(title).html();
                        return `
                            <button type="button" class="products-thumb-btn" data-image="${safeSrc}" data-title="${safeTitle}" title="View image">
                                <img src="${safeSrc}" alt="${safeTitle}" class="products-table-thumb" loading="lazy">
                            </button>
                        `;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    width: '22%',
                    render: function (data) {
                        if (!data) return '-';
                        return `<span class="products-cell-text" title="${$('<div>').text(data).html()}">${$('<div>').text(data).html()}</span>`;
                    }
                },
                {
                    data: 'sku',
                    name: 'sku',
                    width: '14%',
                    render: function (data) {
                        if (!data) return '-';
                        const safe = $('<div>').text(data).html();
                        return `<span class="products-cell-text" title="${safe}">${safe}</span>`;
                    }
                },
                {
                    data: 'variants',
                    name: 'variants',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false,
                    width: '28%',
                    render: function (data) {
                        if (!data) return '-';
                        const safe = $('<div>').text(data).html();
                        return `<span class="products-cell-text" title="${safe}">${safe}</span>`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    width: '10%',
                    render: function (data) {
                        const badgeClass = data === 'active' ? 'success' : data === 'draft' ? 'warning' : 'secondary';
                        return `<span class="badge bg-${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '12%',
                    render: function (data) {
                        const editUrl = window.routes.admin.products.edit.replace(':id', data);
                        return `
                            <a class="btn  btn-outline-primary me-1" href="${editUrl}">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button class="btn  btn-outline-danger btn-delete" data-id="${data}">
                                <i class="bx bx-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            language: {
                processing: "Loading...",
                emptyTable: "No products found",
                zeroRecords: "No matching products found"
            }
        });

        this.bindImagePreview();

        return table;
    },

    bindImagePreview: function () {
        if (this._imagePreviewBound) {
            return;
        }
        this._imagePreviewBound = true;

        $(document).on('click', '#productsTable .products-thumb-btn', function () {
            const src = $(this).attr('data-image') || '';
            const title = $(this).attr('data-title') || 'Product image';
            const $modal = $('#productImagePreviewModal');
            $('#productImagePreviewImg').attr('src', src).attr('alt', title);
            $('#productImagePreviewLabel').text(title);

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).show();
            } else {
                $modal.modal('show');
            }
        });
    },

    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

window.DataTablesProductsConfig = DataTablesProductsConfig;
