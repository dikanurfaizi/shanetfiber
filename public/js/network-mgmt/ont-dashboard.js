function initTooltips(context = document) {
    const tooltips = [].slice.call(
        context.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltips.map((el) => new bootstrap.Tooltip(el));
}

document.addEventListener("DOMContentLoaded", () => initTooltips());

// kalau nanti kamu load ONUs pakai AJAX:
document.addEventListener("ajaxContentLoaded", (e) => initTooltips(e.target));
