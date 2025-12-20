function confirmAddonAction(el) {
    const url = el.dataset.url;
    const action = el.dataset.action;
    const isDeactivate = action === "deactivate";

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    Swal.fire({
        title: isDeactivate ? "Disable Addon?" : "Enable Addon?",
        text: isDeactivate
            ? "This addon will be disabled."
            : "This addon will be enabled.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: isDeactivate ? "#d33" : "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: isDeactivate ? "Yes, Disable" : "Yes, Enable",
        cancelButtonText: "Cancel",
        reverseButtons: true,
    }).then((result) => {
        if (!result.isConfirmed) return;

        const form = document.createElement("form");
        form.method = "POST";
        form.action = url;

        const csrf = document.createElement("input");
        csrf.type = "hidden";
        csrf.name = "_token";
        csrf.value = csrfToken;

        const method = document.createElement("input");
        method.type = "hidden";
        method.name = "_method";
        method.value = "PUT";

        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    });
}
