window.initBillingEditForm = function () {
    const form = document.getElementById("billing-edit-form");
    if (!form) return;

    initBillingPackageDuration(form);
    initBillingDiscount(form);
    initBillingPeriodToggle(form);
    initBillingAddons(form);

    document.querySelectorAll(".addon-check").forEach((cb) => {
        cb.addEventListener("change", function () {
            const addonId = this.dataset.addon;
            const qty = document.querySelector(
                `.qty-input[data-addon="${addonId}"]`
            );
            const subtotal = document.querySelector(
                `.subtotal-input[data-addon="${addonId}"]`
            );

            if (this.checked) {
                qty.removeAttribute("disabled");
            } else {
                qty.value = 1;
                qty.setAttribute("disabled", true);
                subtotal.value = 0;
            }
        });
    });

    document.addEventListener("input", (e) => {
        if (e.target.classList.contains("addon-qty-mirror")) {
            e.target.value = e.target.value.replace(/\D/g, "") || 1;
        }
    });
};

window.initBillingRestoreForm = function () {
    const form = document.getElementById("customer-restore-form");
    if (!form) return;

    initBillingPackageDuration(form);
    initBillingDiscount(form);
    initBillingPeriodToggle(form);
    initBillingAddons(form);

    // 🔥 HITUNG DISKON SAAT LOAD (initial state)
    setTimeout(() => {
        const pkg = form.querySelector("#package-dropdown")?.value;
        const member = form.querySelector("#member-dropdown")?.value;
        if (pkg) {
            // trigger manual
            form.querySelector("#package-dropdown").dispatchEvent(
                new Event("change")
            );
            if (member) {
                form.querySelector("#member-dropdown").dispatchEvent(
                    new Event("change")
                );
            }
        }
    }, 0);
};

function initBillingPackageDuration(form) {
    const packageDropdown = form.querySelector("#package-dropdown");
    const durationWrapper = form.querySelector("#duration-wrapper");
    const durationSelect = form.querySelector("#duration-select");

    if (!packageDropdown || !durationWrapper || !durationSelect) return;

    packageDropdown.addEventListener("change", () => {
        const packageId = packageDropdown.value;

        if (!packageId) {
            durationWrapper.classList.add("d-none");
            durationSelect.innerHTML = "";
            return;
        }

        fetch("/cpanel/packages/durations/label/info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: JSON.stringify({ package_id: packageId }),
        })
            .then((r) => r.json())
            .then((data) => {
                durationSelect.innerHTML =
                    '<option value="">Select Duration</option>';

                data.forEach((item) => {
                    durationSelect.innerHTML += `
                        <option value="${item.id}">
                            ${item.duration?.name_en ?? "Unknown"}
                        </option>`;
                });

                durationWrapper.classList.remove("d-none");
            });
    });
}

function initBillingDiscount(form) {
    const packageDropdown = form.querySelector("#package-dropdown");
    const memberDropdown = form.querySelector("#member-dropdown");
    const discountDisplay = form.querySelector("#discount_display");

    if (!packageDropdown || !memberDropdown || !discountDisplay) return;

    function updateDiscount() {
        if (!packageDropdown.value) {
            discountDisplay.value = "";
            return;
        }

        fetch("/cpanel/packages/discount/calculate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: JSON.stringify({
                package_id: packageDropdown.value,
                member_id: memberDropdown.value,
            }),
        })
            .then((r) => r.json())
            .then((d) => {
                discountDisplay.value = `Rp ${d.effective_discount.toLocaleString(
                    "id-ID"
                )} (${d.effective_percent}%)`;
            });
    }

    packageDropdown.addEventListener("change", updateDiscount);
    memberDropdown.addEventListener("change", updateDiscount);
}

function initBillingPeriodToggle(form) {
    const periodeWrapper = form.querySelector("#periode-wrapper");
    const periodeSelect = form.querySelector(
        'select[name="billing_periode_name"]'
    );

    if (!periodeWrapper || !periodeSelect) return;

    function togglePeriode() {
        const selected = form.querySelector(
            'input[name="billing_option"]:checked'
        )?.value;

        const isOneTime = selected === "one_time";

        // toggle visibility
        periodeWrapper.classList.toggle("d-none", !isOneTime);

        // toggle enabled/disabled
        periodeSelect.disabled = !isOneTime;

        // optional safety: clear value when switching to monthly
        if (!isOneTime) {
            periodeSelect.value = "";
        }
    }

    form.querySelectorAll('input[name="billing_option"]').forEach((radio) => {
        radio.addEventListener("change", togglePeriode);
    });

    // init state
    togglePeriode();
}

function initBillingAddons(form) {
    function updateAddonFromRow(row) {
        if (!row) return;

        const addonId = row.dataset.addon;

        const checkbox = row.querySelector(".addon-toggle");
        const qtyInput = row.querySelector(".addon-qty");
        const subtotalEl = row.querySelector(".addon-subtotal");
        const unitPriceInput = row.querySelector(
            'input[name^="addons"][name$="[unit_price]"]'
        );

        if (!checkbox || !qtyInput || !subtotalEl || !unitPriceInput) return;

        let qty = parseInt(qtyInput.value || 1);

        if (!checkbox.checked) {
            qtyInput.disabled = true;
            qty = 0;
        } else {
            qtyInput.disabled = false;
            qty = Math.max(1, qty);
            qtyInput.value = qty;
        }

        const unitPrice = parseInt(unitPriceInput.value);
        const subtotal = qty * unitPrice;

        subtotalEl.innerText = "Rp " + subtotal.toLocaleString("id-ID");

        // 🔥 SYNC TO MOBILE
        syncAddonToMobile(form, addonId, checkbox.checked, qty, subtotal);
    }

    // Desktop checkbox
    form.querySelectorAll(".addon-toggle").forEach((cb) => {
        cb.addEventListener("change", () => {
            updateAddonFromRow(cb.closest("tr[data-addon]"));
        });
    });

    // Desktop qty
    form.querySelectorAll(".addon-qty").forEach((qty) => {
        qty.addEventListener("input", () => {
            updateAddonFromRow(qty.closest("tr[data-addon]"));
        });
    });

    // ==========================
    // MOBILE → DESKTOP
    // ==========================

    form.querySelectorAll(".addon-toggle-mirror").forEach((cb) => {
        cb.addEventListener("change", () => {
            const card = cb.closest(".addon-card[data-addon]");
            const addonId = card.dataset.addon;

            const desktopRow = form.querySelector(
                `tr[data-addon="${addonId}"]`
            );

            if (!desktopRow) return;

            const desktopCheckbox = desktopRow.querySelector(".addon-toggle");

            if (desktopCheckbox.checked !== cb.checked) {
                desktopCheckbox.checked = cb.checked;
                updateAddonFromRow(desktopRow);
            }
        });
    });

    form.querySelectorAll(".addon-qty-mirror").forEach((input) => {
        input.addEventListener("input", () => {
            const card = input.closest(".addon-card[data-addon]");
            const addonId = card.dataset.addon;

            const desktopRow = form.querySelector(
                `tr[data-addon="${addonId}"]`
            );

            if (!desktopRow) return;

            const desktopQty = desktopRow.querySelector(".addon-qty");

            desktopQty.value = input.value;
            updateAddonFromRow(desktopRow);
        });
    });
}

function syncAddonToMobile(form, addonId, checked, qty, subtotal) {
    const card = form.querySelector(`.addon-card[data-addon="${addonId}"]`);

    if (!card) return;

    card.classList.toggle("active", checked);

    const checkbox = card.querySelector(".addon-toggle-mirror");
    const qtyInput = card.querySelector(".addon-qty-mirror");
    const subtotalEl = card.querySelector(".addon-subtotal-mirror");

    checkbox.checked = checked;
    qtyInput.disabled = !checked;
    qtyInput.value = qty;

    subtotalEl.innerText = "Rp " + subtotal.toLocaleString("id-ID");
}
