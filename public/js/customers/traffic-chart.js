(function () {
    let trafficInterval = null;
    let chartInstance = null;

    function formatBps(value) {
        const sizes = ["bps", "kbps", "Mbps", "Gbps", "Tbps"];
        if (!value || value <= 0) return "0 bps";
        const i = Math.floor(Math.log(value) / Math.log(1024));
        return (value / Math.pow(1024, i)).toFixed(2) + " " + sizes[i];
    }

    window.initTrafficChart = function () {
        const el = document.getElementById("traffic-chart");
        if (!el) return;

        const iface = `<${el.dataset.iface}>`;
        const url = el.dataset.url;

        // 🧹 cleanup kalau tab dibuka ulang
        if (trafficInterval) {
            clearInterval(trafficInterval);
            trafficInterval = null;
        }

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        const options = {
            chart: {
                height: 300,
                type: "line",
                toolbar: { show: false },
            },
            dataLabels: { enabled: false },
            series: [
                { name: "Download", data: [], type: "area" },
                { name: "Upload", data: [], type: "area" },
            ],
            yaxis: {
                labels: { formatter: formatBps },
            },
            tooltip: {
                y: { formatter: formatBps },
            },
            stroke: {
                curve: "smooth",
                width: 2,
            },
            fill: {
                opacity: [0.15, 0.05],
            },
            colors: ["#0090fc", "#ff6666"],
            markers: {
                size: 3,
                hover: { size: 4 },
            },
            xaxis: { range: 15 },
            noData: { text: "Loading data..." },
        };

        chartInstance = new ApexCharts(el, options);
        chartInstance.render();

        const updateChart = () => {
            fetch(`${url}?interface=${encodeURIComponent(iface)}`)
                .then((res) => res.json())
                .then((data) => {
                    if (!data || !data[0]) return;

                    chartInstance.appendData([
                        { name: "Download", data: data[0].Tx },
                        { name: "Upload", data: data[0].Rx },
                    ]);
                })
                .catch(() => {});
        };

        updateChart();
        trafficInterval = setInterval(updateChart, 2000);
    };
})();
