// Toggle password visibility
function togglePasswordVisibility() {
    const input = document.getElementById("passwordInput");
    const icon = document.getElementById("passwordIcon");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Function to change category
function changeCategory(category) {
    // Hide all package containers
    document.querySelectorAll(".category-packages").forEach((container) => {
        container.classList.add("d-none");
    });

    // Show selected category
    document.getElementById(category + "Packages").classList.remove("d-none");

    // Update active tab styling
    document.querySelectorAll(".category-tab").forEach((tab) => {
        tab.classList.remove("active");
        tab.style.backgroundColor = "white";
        tab.style.color = "#374151";
    });

    // Style the active tab
    const activeTab = document.querySelector(
        `.category-tab[data-category="${category}"]`
    );
    activeTab.classList.add("active");

    // Set color based on category
    let bgColor = "#3b82f6"; // default blue
    if (category === "Internet Stream") bgColor = "#8b5cf6";
    if (category === "Internet + TV") bgColor = "#10b981";

    activeTab.style.backgroundColor = bgColor;
    activeTab.style.color = "white";

    // Reinitialize swiper for the active category
    setTimeout(() => {
        new Swiper(".paket-slider", {
            slidesPerView: 1,
            spaceBetween: 20,
            navigation: {
                nextEl: ".paket-slider .swiper-button-next",
                prevEl: ".paket-slider .swiper-button-prev",
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    }, 100);
}

// Initialize first category as active
document.addEventListener("DOMContentLoaded", function () {
    const firstCategory =
        document.querySelector(".category-tab").dataset.category;
    changeCategory(firstCategory);
});

// Initialize Hero Slider
new Swiper(".hero-slider", {
    loop: true,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    pagination: {
        el: ".hero-slider .swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".hero-slider .swiper-button-next",
        prevEl: ".hero-slider .swiper-button-prev",
    },
});
