// Default coordinates (Jakarta)
const DEFAULT_LAT = -6.52029;
const DEFAULT_LNG = 107.45537;
const DEFAULT_ZOOM = 13;

// Initialize map
$(document).ready(function () {
    // Initialize map with default view
    var map = L.map("map-container").setView(
        [DEFAULT_LAT, DEFAULT_LNG],
        DEFAULT_ZOOM
    );

    // Add OpenStreetMap tiles with better visual
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Add marker for OLT
    var oltMarker = null;

    // Add initial OLT marker if coordinates are available
    var initialLat = $("#latitude").val();
    var initialLng = $("#longitude").val();

    if (initialLat && initialLng) {
        oltMarker = L.marker([initialLat, initialLng]).addTo(map);
        map.setView([initialLat, initialLng], 15);
    }

    // Handle map click
    map.on("click", function (e) {
        updateMarkerPosition(e.latlng.lat, e.latlng.lng);
    });

    // Handle current location button
    $("#get-location-btn").on("click", function () {
        const locationStatus = $("#location-status");
        const resetBtn = $("#reset-to-default");

        // Check if geolocation is supported by the browser
        if (!navigator.geolocation) {
            locationStatus.html(
                '<span class="text-danger"><i class="fas fa-exclamation-circle me-2 mr-1"></i>Geolocation is not supported by your browser</span>'
            );
            return;
        }

        // Show loading status
        locationStatus.html(
            '<span class="text-primary"><i class="fas fa-spinner fa-spin me-2 mr-1"></i>Getting your location...</span>'
        );

        // Get current position
        navigator.geolocation.getCurrentPosition(
            function (position) {
                // Success callback
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update form fields and map
                updateMarkerPosition(lat, lng);

                // Show success message
                locationStatus.html(
                    '<span class="text-success"><i class="fas fa-check-circle me-2 mr-1"></i>Location acquired successfully</span>'
                );

                // Tampilkan tombol reset setelah sukses
                resetBtn.show();
            },
            function (error) {
                // Error callback
                let errorMessage;
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Location permission was denied";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Location information is unavailable";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "The request to get location timed out";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "An unknown error occurred";
                        break;
                }
                locationStatus.html(
                    `<span class="text-danger"><i class="fas fa-exclamation-circle me-2 mr-1"></i>${errorMessage}</span>`
                );
            },
            {
                // Options
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    });

    // Set custom location from input fields
    $("#set-custom-location").on("click", function () {
        const lat = parseFloat($("#custom-lat").val());
        const lng = parseFloat($("#custom-lng").val());

        if (isNaN(lat) || isNaN(lng)) {
            Swal.fire({
                icon: "warning",
                title: "Invalid Input",
                text: "Please enter valid latitude and longitude values.",
            });
            return;
        }

        if (lat < -90 || lat > 90) {
            Swal.fire({
                icon: "warning",
                title: "Invalid Latitude",
                text: "Latitude must be between -90 and 90.",
            });
            return;
        }

        if (lng < -180 || lng > 180) {
            Swal.fire({
                icon: "warning",
                title: "Invalid Longitude",
                text: "Longitude must be between -180 and 180.",
            });
            return;
        }

        updateMarkerPosition(lat, lng);
        $("#location-status").html(
            '<span class="text-success"><i class="fas fa-check-circle me-2"></i>Location acquired successfully</span>'
        );
    });

    // Reset to default location
    $("#reset-to-default").on("click", function () {
        map.setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
        $("#custom-lat").val(DEFAULT_LAT);
        $("#custom-lng").val(DEFAULT_LNG);
        $("#location-status").html(
            '<span class="text-info"><i class="fas fa-info-circle me-2 mr-1"></i>Reset to default location</span>'
        );

        // Remove existing marker
        if (oltMarker) {
            map.removeLayer(oltMarker);
            oltMarker = null;
        }

        // Clear coordinate fields
        $("#latitude").val("");
        $("#longitude").val("");

        // Sembunyikan tombol reset kembali
        $(this).hide();
    });

    // Function to update marker position
    function updateMarkerPosition(lat, lng) {
        // Update form fields
        $("#latitude").val(lat.toFixed(6));
        $("#longitude").val(lng.toFixed(6));

        // Update custom location inputs
        $("#custom-lat").val(lat.toFixed(6));
        $("#custom-lng").val(lng.toFixed(6));

        // Update map view
        map.setView([lat, lng], 16);

        // Remove existing marker
        if (oltMarker) {
            map.removeLayer(oltMarker);
        }

        // Add new marker
        oltMarker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(
                "OLT Location: " + lat.toFixed(6) + ", " + lng.toFixed(6)
            )
            .openPopup();
    }

    // Improve map rendering
    setTimeout(function () {
        map.invalidateSize();
    }, 200);
});
