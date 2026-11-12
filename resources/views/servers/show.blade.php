<x-layouts.mikrotiks.app-layout :title="__('server.title.show', ['name' => $mikrotik->name])">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('server.label.server_name') }}: {{ $mikrotik->name }}</h5>
        <div class="small">
            <span class="fw-500 text-primary">{{ __('hotspot.date-time') }}</span>
            · <span id="tanggal"></span> · <span id="waktu"></span> · <span id="time-zone-name"></span> · <span
                id="gmt-offset"></span>
        </div>
    </div>

    <div class="card-body">

        <div class="row">
            {{-- PPP Secret Total --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-lg border-start-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small fw-bold text-primary mb-1">{{ __('server.stats.ppp_secret') }}</div>
                                <div id="all-ppp-secrets-count" class="h5">
                                    <div id="loading" class="d-none">{{ __('server.label.loading') }}</div>
                                </div>
                                <div class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    {{ __('server.label.users') }}
                                </div>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-users fa-2x text-gray-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PPP Active --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-lg border-start-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small fw-bold text-primary mb-1">{{ __('server.stats.ppp_active') }}</div>
                                <div id="all-ppp-secrets-active-count" class="h5">
                                    <div id="loading-active" class="d-none">{{ __('server.label.loading') }}</div>
                                </div>
                                <div class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    {{ __('server.label.users') }}
                                </div>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-user-check fa-2x text-gray-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PPP Non Active --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-lg border-start-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small fw-bold text-primary mb-1">{{ __('server.stats.ppp_non_active') }}
                                </div>
                                <div id="all-ppp-secrets-inactive-count" class="h5">
                                    <div id="loading-inactive" class="d-none">{{ __('server.label.loading') }}</div>
                                </div>
                                <div class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    {{ __('server.label.users') }}
                                </div>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-user-slash fa-2x text-gray-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PPPoE Server --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-lg border-start-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small fw-bold text-primary mb-1">{{ __('server.stats.pppoe_server') }}
                                </div>
                                <div id="all-pppoe-servers-count" class="h5">
                                    <div id="loading-server" class="d-none">{{ __('server.label.loading') }}</div>
                                </div>
                                <div class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    {{ __('server.label.servers') }}
                                </div>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-server fa-2x text-gray-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- System Resources Info --}}
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">{{ __('hotspot.resource') }}: <span id="rbtype"></span></div>
                    <div class="card-body">
                        <div class="mb-4">
                            <p>{{ __('server.info.uptime') }}: <span id="uptime"
                                    class="badge text-white font-size-12 font-semibold bg-primary">--:--:--</span></p>
                            <div>
                                {{ __('server.info.ros_version') }} : <span id="version"></span>
                            </div>
                            <div>
                                {{ __('server.info.cpu_frequency') }} :
                                <span id="frequency">0</span> MHz
                            </div>
                            <div>
                                {{ __('server.info.cpu_core') }} : <span id="cpuCount">0</span>
                                {{ __('server.label.core') }}
                            </div>
                            <div>
                                {{ __('server.info.architecture') }} : <span id="arc"></span>
                            </div>
                            <div>
                                {{ __('server.info.board_name') }} : <span id="boardname"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hardware Monitoring Gauges (CPU, RAM, HDD) --}}
            <div class="col-lg-8 mb-4">
                <div class="card mb-4 h-100">
                    <div class="card-header">
                        <span class="fw-bold text-primary"><i class="fas fa-chart-line me-1"></i>
                            {{ __('server.header.system_monitoring') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="pt-2">
                            {{-- CPU Load --}}
                            <div class="custom-progess mb-5">
                                <h4 class="small">
                                    <span class="fas fa-microchip text-success font-size-12 font-semibold"></span> CPU
                                    <span id="pCpu" class="float-end fw-bold">0%</span>
                                </h4>
                                <div class="progress">
                                    <div id="cpu"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                        role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">0% CPU</div>
                                </div>
                            </div>

                            {{-- RAM Usage --}}
                            <div class="custom-progess mb-5">
                                <h4 class="small">
                                    <span class="fas fa-memory text-warning font-size-12 font-semibold"></span> RAM
                                    <span id="pRam" class="float-end fw-bold">0%</span>
                                </h4>
                                <div class="progress">
                                    <div id="memory"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                                        role="progressbar" style="width: 0%;">0 MB / 0 MB</div>
                                </div>
                            </div>

                            {{-- HDD Usage --}}
                            <div class="custom-progess mb-4">
                                <h4 class="small">
                                    <span class="fas fa-hdd text-danger font-size-12 font-semibold"></span> HDD
                                    <span id="pHdd" class="float-end fw-bold">0%</span>
                                </h4>
                                <div class="progress">
                                    <div id="hdd"
                                        class="progress-bar progress-bar-animated progress-bar-striped bg-danger"
                                        role="progressbar" style="width: 0%;">0 MB / 0 MB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                $("#loading").removeClass("d-none");

                function updateCountPppSecret() {
                    $.ajax({
                        url: "{{ route('get.ppp.secrets', ['mikrotik' => $mikrotik]) }}",
                        method: "GET",
                        success: function(response) {
                            const allPppSecretsCount = response.allPppSecretsCount ?? 0;
                            $("#all-ppp-secrets-count").text(allPppSecretsCount);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                function updateSystemResources() {
                    $.ajax({
                        url: "{{ route('mikrotik.resources', ['mikrotik' => $mikrotik]) }}",
                        method: "GET",
                        global: false,
                        dataType: 'json',
                        success: function(response) {
                            if (!response.resources || response.resources.length === 0) return;

                            const resource = response.resources[0];

                            const memoryUse = resource.totalMemory - resource.freeMemory;
                            const memoryPercentUse = resource.totalMemory > 0 ? Math.round((memoryUse /
                                resource.totalMemory) * 100) : 0;

                            const hddUse = resource.totalHdd - resource.freeHdd;
                            const hddPercentUse = resource.totalHdd > 0 ? Math.round((hddUse / resource
                                .totalHdd) * 100) : 0;

                            const uptimeRouter = resource.uptime;

                            $('#pCpu').html(resource.cpuLoad + '%');
                            $('#cpu').css('width', resource.cpuLoad + '%').html(resource.cpuLoad + '% ' +
                                resource.cpuFrequency + ' MHz');

                            $('#pRam').html(memoryPercentUse + '%');
                            $('#memory').css('width', memoryPercentUse + '%').html(resource.memoryUsage +
                                ' / ' + resource.totalMemorySpace);

                            $('#pHdd').html(hddPercentUse + '%');
                            $('#hdd').css('width', hddPercentUse + '%').html(resource.hddUsage + ' / ' +
                                resource.totalHddSpace);

                            $('#arc').html(resource.arc);
                            $('#frequency').html(resource.cpuFrequency);
                            $('#rbtype').html(resource.boardName);
                            $('#boardname').html(resource.boardName);
                            $('#version').html(resource.version);
                            $('#uptime').html(uptimeRouter);
                            $('#cpuCount').html(resource.cpuCount);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                function updateTime() {
                    $.ajax({
                        url: "{{ route('mikrotik.time', ['mikrotik' => $mikrotik->slug]) }}",
                        method: "GET",
                        success: function(response) {
                            if (!response.time || response.time.length === 0) return;

                            const times = response.time[0];
                            $('#waktu').html(times.time);
                            $('#tanggal').html(times.date);
                            $('#time-zone-name').html(times.timeZoneName);
                            $('#gmt-offset').html(times.gmtOffset);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                // Interval Update
                setInterval(function() {
                    updateSystemResources();
                    updateCountPppSecret();
                }, 1000);

                setInterval(function() {
                    updateTime();
                }, 1000);

                $(document).ajaxStop(function() {
                    $("#loading").addClass("d-none");
                });
            });
        </script>
    @endsection
</x-layouts.mikrotiks.app-layout>
