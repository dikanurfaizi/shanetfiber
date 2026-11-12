<x-layouts.app-layout title="Packet Show">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('package_categories.title.show') }}</h5>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('package_categories.label.name') }}</strong><br>
                    {{ $package_category->name ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package_categories.label.status') }}</strong><br>
                    {{ $package_category->is_active ? 'Active' : 'Inactive' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package_categories.label.description') }}</strong><br>
                    {{ $package_category->description ?? 'no data' }}
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('package_categories.label.created_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($package_category->created_at)->translatedFormat('d F Y H:i') }} WIB
                </div>

                <div class="mb-3">
                    <strong>{{ __('package_categories.label.updated_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($package_category->updated_at)->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        @can('package-category-edit')
            <a class="btn btn-sm btn-success" href="{{ route('categories.edit', $package_category->slug) }}">
                <i class="fa fa-edit"></i>{{ __('package_categories.button.edit') }}
            </a>
        @endcan
        <a class="btn btn-sm btn-primary" href="{{ route('categories.index') }}">
            <i class="fa fa-arrow-left"></i>{{ __('package_categories.button.back') }}
        </a>
    </div>

</x-layouts.app-layout>
