<!-- Include Admin Utilities -->
<script src="{{ asset('admin/js/modules/common/utils.js') }}"></script>

<!-- Include DataTables Configuration -->
<script src="{{ asset('admin/js/modules/common/datatables-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-designs-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-materials-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-transparency-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-characteristics-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-usage-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-products-config.js') }}?v={{ @filemtime(public_path('admin/js/modules/common/datatables-products-config.js')) ?: time() }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-consultations-config.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-contact-us-config.js') }}"></script>

<!-- Define Routes -->
<script>
    window.routes = {
        admin: {
            colors: {
                index: '{{ route('admin.colors.index') }}',
                store: '{{ route('admin.colors.store') }}',
                show: '{{ route('admin.colors.show', ':id') }}',
                update: '{{ route('admin.colors.update', ':id') }}',
                destroy: '{{ route('admin.colors.destroy', ':id') }}'
            },
            designs: {
                index: '{{ route('admin.designs.index') }}',
                store: '{{ route('admin.designs.store') }}',
                show: '{{ route('admin.designs.show', ':id') }}',
                update: '{{ route('admin.designs.update', ':id') }}',
                destroy: '{{ route('admin.designs.destroy', ':id') }}'
            },
            materials: {
                index: '{{ route('admin.materials.index') }}',
                store: '{{ route('admin.materials.store') }}',
                show: '{{ route('admin.materials.show', ':id') }}',
                update: '{{ route('admin.materials.update', ':id') }}',
                destroy: '{{ route('admin.materials.destroy', ':id') }}'
            },
            transparency: {
                index: '{{ route('admin.transparency.index') }}',
                store: '{{ route('admin.transparency.store') }}',
                show: '{{ route('admin.transparency.show', ':id') }}',
                update: '{{ route('admin.transparency.update', ':id') }}',
                destroy: '{{ route('admin.transparency.destroy', ':id') }}'
            },
            characteristics: {
                index: '{{ route('admin.characteristics.index') }}',
                store: '{{ route('admin.characteristics.store') }}',
                show: '{{ route('admin.characteristics.show', ':id') }}',
                update: '{{ route('admin.characteristics.update', ':id') }}',
                destroy: '{{ route('admin.characteristics.destroy', ':id') }}'
            },
            usage: {
                index: '{{ route('admin.usage.index') }}',
                store: '{{ route('admin.usage.store') }}',
                show: '{{ route('admin.usage.show', ':id') }}',
                update: '{{ route('admin.usage.update', ':id') }}',
                destroy: '{{ route('admin.usage.destroy', ':id') }}'
            },
            products: {
                index: '{{ route('admin.products.index') }}',
                store: '{{ route('admin.products.store') }}',
                show: '{{ route('admin.products.show', ':id') }}',
                edit: '{{ route('admin.products.edit', ':id') }}',
                editModal: '{{ route('admin.products.edit-modal', ':id') }}',
                update: '{{ route('admin.products.update', ':id') }}',
                destroy: '{{ route('admin.products.destroy', ':id') }}',
                relatedData: '{{ route('admin.products.related-data') }}',
                generateSku: '{{ route('admin.products.generate-sku') }}'
            },
            consultations: {
                index: '{{ route('admin.consultations.index') }}',
                show: '{{ route('admin.consultations.show', ':consultation') }}',
                update: '{{ route('admin.consultations.update', ':consultation') }}',
                destroy: '{{ route('admin.consultations.destroy', ':consultation') }}'
            },
            'contact-us': {
                index: '{{ route('admin.contact-us.index') }}',
                show: '{{ route('admin.contact-us.show', ':contact_u') }}',
                update: '{{ route('admin.contact-us.update', ':contact_u') }}',
                destroy: '{{ route('admin.contact-us.destroy', ':contact_u') }}'
            }
        }
    };
</script>

<!-- Include Colors Management -->
<script src="{{ asset('admin/js/modules/colors/colors.js') }}"></script>

<!-- Include Designs Management -->
<script src="{{ asset('admin/js/modules/designs/designs.js') }}"></script>

<!-- Include Materials Management -->
<script src="{{ asset('admin/js/modules/materials/materials.js') }}"></script>

<!-- Include Transparency Management -->
<script src="{{ asset('admin/js/modules/transparency/transparency.js') }}"></script>

<!-- Include Characteristics Management -->
<script src="{{ asset('admin/js/modules/characteristics/characteristics.js') }}"></script>

<!-- Include Usage Management -->
<script src="{{ asset('admin/js/modules/usage/usage.js') }}"></script>

<!-- Include Products Management -->
<script src="{{ asset('admin/js/modules/products/products.js') }}?v={{ @filemtime(public_path('admin/js/modules/products/products.js')) ?: time() }}"></script>

