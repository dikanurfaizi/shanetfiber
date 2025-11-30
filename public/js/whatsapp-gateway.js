document.addEventListener("DOMContentLoaded", () => {
    initToggleGateway();
    initUpdateGatewaySettings();

    $(document).on("click", ".btn-delete-account", function (e) {
        e.preventDefault();
        let form = $(this).closest("form");

        Swal.fire({
            title: "Delete Account?",
            text: "This WhatsApp Gateway account will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it",
            cancelButtonText: "Cancelled",
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

/**
 * HANDLE TOGGLE ENABLE/DISABLE
 */
function initToggleGateway() {
    const checkbox = $("#switch-gateway");
    const badge = $("#gateway-badge");

    function updateBadgeUI(isEnabled) {
        if (isEnabled) {
            badge
                .removeClass("bg-danger")
                .addClass("bg-success text-white")
                .text(window.appData.translations.enable);
        } else {
            badge
                .removeClass("bg-success")
                .addClass("bg-danger text-white")
                .text(window.appData.translations.disable);
        }
    }

    const isEnabled = window.appData.statusGateway === 0;
    checkbox.prop("checked", isEnabled);
    updateBadgeUI(isEnabled);

    checkbox.on("change", function (e) {
        const enabledNow = e.currentTarget.checked;
        updateBadgeUI(enabledNow);

        $.ajax({
            url: window.appData.toggleRoute,
            type: "PATCH",
            data: {
                disabled: enabledNow ? 0 : 1,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                new Notify({
                    status: res.success ? "success" : "error",
                    title: res.title,
                    text: res.message,
                    autoclose: true,
                });
            },
            error: function () {
                checkbox.prop("checked", !enabledNow);
                updateBadgeUI(!enabledNow);

                new Notify({
                    status: "error",
                    title: "Error",
                    text: "Failed to update gateway.",
                });
            },
        });
    });
}

/**
 * HANDLE GRAPH URL + API VERSION EDIT MODE
 */
function initUpdateGatewaySettings() {
    const viewMode = $("#baseurl-view-mode");
    const editMode = $("#baseurl-edit-mode");

    const baseUrlInput = $("#meta_graph_url");
    const baseUrlDisplay = $("#meta_graph_url_display");

    const versionInput = $("#meta_version");
    const versionDisplay = $("#meta_version_display");

    const wabaIdInput = $("#meta_waba_id");
    const wabaIdDisplay = $("#meta_waba_id_display");

    $("#btn-edit-gateway").on("click", () => {
        viewMode.hide();
        editMode.show();
    });

    $("#btn-cancel-gateway").on("click", () => {
        editMode.hide();
        viewMode.show();
    });

    $("#gateway-update-form").on("submit", function (e) {
        e.preventDefault();

        const btnSave = $("#btn-save-gateway");
        const originalHtml = btnSave.html();

        if (!baseUrlInput.val().trim()) {
            new Notify({
                status: "warning",
                text: "Graph API URL cannot be empty.",
            });
            return;
        }

        if (!versionInput.val().trim()) {
            new Notify({
                status: "warning",
                text: "API version cannot be empty.",
            });
            return;
        }

        btnSave.prop("disabled", true).html(`
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Saving...
        `);

        $.ajax({
            url: window.appData.baseUrlUpdateRoute,
            type: "PATCH",
            data: {
                meta_graph_url: baseUrlInput.val(),
                meta_version: versionInput.val(),
                meta_waba_id: wabaIdInput.val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {

                baseUrlDisplay.text(res.meta_graph_url);
                versionDisplay.text(res.meta_version);
                wabaIdDisplay.text(res.meta_waba_id);

                new Notify({
                    status: "success",
                    title: res.title,
                    text: res.message,
                    effect: "slide",
                });

                editMode.hide();
                viewMode.show();
            },
            error: function (e) {
                console.log("error", e);
                new Notify({
                    status: "error",
                    title: "Error",
                    text: "Unable to update settings.",
                    effect: "slide",
                });
            },
            complete: function () {
                btnSave.prop("disabled", false).html(originalHtml);
            },
        });
    });
}
