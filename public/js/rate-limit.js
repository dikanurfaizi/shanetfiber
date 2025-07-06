$(function () {
    /*==================================================
        Server Dropdown Change Event
    ====================================================*/
    $("#server-dropdown").on("change", function () {
        var idServer = this.value;
        $("#parent-queue-dropdown").html("");
        $("#queue-type-dropdown").html("");
        $("#insert-before-dropdown").html("");

        $.ajax({
            url: "{{ url('api/fetch-parent-queues') }}",
            type: "POST",
            data: {
                mikrotik_id: idServer,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            success: function (result_server) {
                $("#parent-queue-dropdown").html(
                    '<option value="none">None</option>'
                );
                $.each(result_server, function (key, value) {
                    $("#parent-queue-dropdown").append(
                        '<option value="' +
                            value.name +
                            '">' +
                            value.name +
                            "</option>"
                    );
                });
                $("#insert-before-dropdown").html(
                    '<option value="default">Default</option><option value="bottom">Bottom</option><option value="first">First</option>'
                );
                $.each(result_server, function (key, value) {
                    $("#insert-before-dropdown").append(
                        '<option value="' +
                            value.name +
                            '">' +
                            value.name +
                            "</option>"
                    );
                });
            },
        });

        setTimeout(() => {
            $.ajax({
                url: "{{ url('api/fetch-queue-types') }}",
                type: "POST",
                data: {
                    mikrotik_id: idServer,
                    _token: "{{ csrf_token() }}",
                },
                dataType: "json",
                success: function (result_server) {
                    $("#queue-type-dropdown").html(
                        '<option value="none">None</option>'
                    );
                    $.each(result_server, function (key, value) {
                        $("#queue-type-dropdown").append(
                            '<option value="' +
                                value.name +
                                '">' +
                                value.name +
                                "</option>"
                        );
                    });
                },
            });
        }, 250);
    });

    /* ───── Helper ───── */
    const getValueAndUnit = (raw) => {
        if (!raw) return [null, null];

        const clean = raw.trim().replace(",", ".");
        const unit = (clean.match(/[a-zA-Z]/g) || ["k"])[0].toUpperCase(); // default K
        const number = parseFloat(clean.replace(/[^0-9.]/g, "")) || null;
        if (!number) return [null, null];

        return [number, unit];
    };
    const pair = (up, dn) =>
        up && dn ? `${up}${dn === "" ? "" : "/" + dn}` : null;

    /* ───── Main calc ───── */
    function calculateRateLimit() {
        // ambil semua input
        const maxUpRaw = $("#max_upload").val();
        const maxDnRaw = $("#max_download").val();
        const burstUpRaw = $("#burst_upload").val();
        const burstDnRaw = $("#burst_download").val();
        const thUpRaw = $("#threshold_upload").val();
        const thDnRaw = $("#threshold_download").val();
        const btUpRaw = $("#burst_time_upload").val();
        const btDnRaw = $("#burst_time_download").val();
        const limUpRaw = $("#limit_at_upload").val();
        const limDnRaw = $("#limit_at_download").val();
        const priority = $("#priority").val() || 8;

        /* ─ Normalize ─ */
        const [maxUpVal, maxUpUnit] = getValueAndUnit(maxUpRaw);
        const [maxDnVal, maxDnUnit] = getValueAndUnit(maxDnRaw);
        const [burstUpVal, burstUpUnit] = getValueAndUnit(burstUpRaw);
        const [burstDnVal, burstDnUnit] = getValueAndUnit(burstDnRaw);
        const [thUpVal, thUpUnit] = getValueAndUnit(thUpRaw);
        const [thDnVal, thDnUnit] = getValueAndUnit(thDnRaw);
        const limUp = limUpRaw?.trim(); // Limit‑At sudah satuan ‘k’
        const limDn = limDnRaw?.trim();

        const maxPair =
            maxUpVal && maxDnVal
                ? `${maxUpVal}${maxUpUnit}/${maxDnVal}${maxDnUnit}`
                : null;
        const burstPair =
            burstUpVal && burstDnVal
                ? `${burstUpVal}${burstUpUnit}/${burstDnVal}${burstDnUnit}`
                : null;
        const thPair =
            thUpVal && thDnVal
                ? `${thUpVal}${thUpUnit}/${thDnVal}${thDnUnit}`
                : null;
        const btPair = btUpRaw && btDnRaw ? `${btUpRaw}/${btDnRaw}` : null;
        const limPair = limUp && limDn ? `${limUp}/${limDn}` : null;

        /* ─ Business rule check ─ */
        let rateLimit = "";
        const allOtherFilled = burstPair && thPair && btPair && limPair;

        if (maxPair && allOtherFilled) {
            rateLimit = `${maxPair} ${burstPair} ${thPair} ${btPair} ${priority} ${limPair}`;
        } else if (maxPair) {
            // aturannya: hanya Max yang dipakai
            rateLimit = maxPair;
        }

        $("#rate-limit").val(rateLimit); // hidden / readonly field
    }

    /* ───── Event binding ───── */
    $(document).on(
        "input",
        "#max_upload, #max_download, #burst_upload, #burst_download," +
            " #threshold_upload, #threshold_download, #burst_time_upload, #burst_time_download," +
            " #limit_at_upload, #limit_at_download",
        calculateRateLimit
    );
    $("#priority").on("change", calculateRateLimit);
});
