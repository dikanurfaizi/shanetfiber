<x-layouts.mikrotiks.app-layout title="PPP Profiles">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">PPP Profiles</h5>
    </div>

    <div class="card-body col-lg-6">
        @if ($errors->any())
            <div class="form-group mt-4 mb-4">
                <div class="pull-right alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

        @endif
        <div style="overflow-x: auto;">
            <table id="profilesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>{{ __('pppoe.profile.name') }}</th>
                        <th>{{ __('pppoe.profile.rate-limit') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($profiles as $profile)
                        <tr>
                            <td><span
                                    class="badge rounded-pill bg-secondary-soft text-secondary">{{ $loop->iteration }}</span>
                            </td>
                            <td><span class="badge bg-blue-soft text-primary">{{ $profile['name'] }}</span></td>
                            @if (isset($profile['rate-limit']))
                                <td><span
                                        class="badge bg-blue-soft text-primary">{{ $profile['rate-limit'] ?? 'Not Set' }}</span>
                                </td>
                            @else
                                <td><span
                                        class="badge bg-blue-soft text-danger">{{ $profile['rate-limit'] ?? 'Not Set' }}</span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {!! Form::open(['route' => ['ppp.profiles.clone', 'mikrotik' => $mikrotik], 'method' => 'POST']) !!}
            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-clone"></i> &nbsp
                {{ __('pppoe.profile.button.copy') }}</button>
            {!! Form::close() !!}
        </div>
    </div>


    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                $('#secretsTable').DataTable({

                });

            });
        </script>
    @endsection
</x-layouts.mikrotiks.app-layout>