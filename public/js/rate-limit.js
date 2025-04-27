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

    // Function to calculate rate limit
    function calculateRateLimit() {
        let uploadInput = $('input[name="max_upload"]').val().trim();
        let downloadInput = $('input[name="max_download"]').val().trim();
        let priority = $('select[name="priority"]').val();

        // Reset error message
        $("#error-max-upload").text("");
        $("#error-max-download").text("");

        // Ekstrak angka dan unit
        let uploadValue = 0;
        let downloadValue = 0;

        if (uploadInput) {
            uploadValue =
                parseFloat(
                    uploadInput.replace(/[^0-9\.\,]/g, "").replace(",", ".")
                ) || 0;
        }

        let uploadUnitRaw = uploadInput.replace(/[^a-zA-Z]/g, "");

        if (downloadInput) {
            downloadValue =
                parseFloat(
                    downloadInput.replace(/[^0-9\.\,]/g, "").replace(",", ".")
                ) || 0;
        }

        let downloadUnitRaw = downloadInput.replace(/[^a-zA-Z]/g, "");

        // Normalisasi unit
        let uploadUnit = "";
        let downloadUnit = "";
        let hasError = false;

        if (uploadUnitRaw.toLowerCase() === "k") {
            uploadUnit = "k";
        } else if (uploadUnitRaw.toLowerCase() === "m") {
            uploadUnit = "M";
        } else {
            $("#error-max-upload").text(
                'Input Upload harus menggunakan satuan "k" atau "M"'
            );
            hasError = true;
        }

        if (downloadUnitRaw.toLowerCase() === "k") {
            downloadUnit = "k";
        } else if (downloadUnitRaw.toLowerCase() === "m") {
            downloadUnit = "M";
        } else {
            $("#error-max-download").text(
                'Input Download harus menggunakan satuan "k" atau "M"'
            );
            hasError = true;
        }

        if (hasError) {
            return;
        }

        if (uploadValue && downloadValue && priority) {
            // Format rate limit
            let rateLimit = `${uploadValue}${uploadUnit}/${downloadValue}${downloadUnit}`;
            $("#rate-limit").val(rateLimit);
        }
    }

    $(document).on(
        "input",
        'input[name="max_upload"], input[name="max_download"], input[name="rasio_cir"]',
        calculateRateLimit
    );
    $(document).on("change", 'select[name="priority"]', calculateRateLimit);
});
