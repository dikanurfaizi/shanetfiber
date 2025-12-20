<script>
    document.addEventListener("DOMContentLoaded", function() {

        const chartEl = document.querySelector("#chart");
        if (!chartEl) return;

        function formatSpeed(value) {
            const units = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
            if (value <= 0) return '0 bps';
            const i = Math.floor(Math.log(value) / Math.log(1024));
            return (value / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
        }

        const chartOptions = {
            chart: {
                height: 300,
                type: "line",
                toolbar: {
                    show: false
                }
            },
            series: [{
                    name: "Download",
                    data: [],
                    type: "area"
                },
                {
                    name: "Upload",
                    data: [],
                    type: "area"
                }
            ],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: "smooth",
                width: 2
            },
            colors: ["#0090fc", "#ff6666"],
            yaxis: {
                labels: {
                    formatter: value => formatSpeed(value)
                }
            },
            xaxis: {
                range: 20
            },
            tooltip: {
                y: {
                    formatter: value => formatSpeed(value)
                }
            },
            noData: {
                text: "Loading..."
            }
        };

        const trafficChart = new ApexCharts(chartEl, chartOptions);
        trafficChart.render();

        const iface = "pppoe-{{ $customer->username }}";
        const url = "{{ route('mikrotik.traffic_interface', $mikrotik) }}";

        function refreshTraffic() {
            fetch(url + "?interface=" + encodeURIComponent(iface))
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || !data[0]) return;

                    trafficChart.appendData([{
                            data: data[0].Tx ?? 0
                        },
                        {
                            data: data[0].Rx ?? 0
                        }
                    ]);
                })
                .catch(() => {
                    console.warn("Traffic update failed.");
                });
        }

        setInterval(refreshTraffic, 2000);
    });
</script>
