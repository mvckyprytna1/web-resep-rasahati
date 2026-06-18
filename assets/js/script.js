document.addEventListener("DOMContentLoaded", function() {

    // ==========================================
    // 1. DYNAMIC STICKY NAVBAR ON SCROLL
    // ==========================================
    const navbar = document.getElementById("navbar");
    
    window.addEventListener("scroll", function() {
        if (window.scrollY > 50) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });

    // ==========================================
    // 2. HAMBURGER MOBILE MENU CONTROL
    // ==========================================
    const hamburger = document.getElementById("hamburger");
    const navLinks = document.getElementById("nav-links");
    const links = document.querySelectorAll(".nav-link");

    if (hamburger && navLinks) {
        hamburger.addEventListener("click", function() {
            hamburger.classList.toggle("active");
            navLinks.classList.toggle("active");
            document.body.classList.toggle("menu-open");
        });
    }

    links.forEach(link => {
        link.addEventListener("click", function() {
            if (hamburger && navLinks) {
                hamburger.classList.remove("active");
                navLinks.classList.remove("active");
                document.body.classList.remove("menu-open");
            }
        });
    });

    // ==========================================
    // 3. SEAMLESS POPUP DIALOG (MODAL)
    // ==========================================
    const modal = document.getElementById("custom-modal");
    const closeModalElements = [
        document.getElementById("close-modal"),
        document.getElementById("btn-close-modal-ok")
    ];
    const modalIcon = document.getElementById("modal-icon");
    const modalTitle = document.getElementById("modal-title");
    const modalMessage = document.getElementById("modal-message");

    function openModal(title, message, isSuccess = true) {
        if (!modal) return;
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        
        if (isSuccess) {
            modalIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            modalIcon.style.color = 'var(--color-soft-green)';
            modalIcon.style.backgroundColor = '#F1F9EC';
        } else {
            modalIcon.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
            modalIcon.style.color = '#C2410C';
            modalIcon.style.backgroundColor = '#FFF7ED';
        }
        
        modal.classList.add("open");
    }

    closeModalElements.forEach(element => {
        if (element) {
            element.addEventListener("click", function() {
                modal.classList.remove("open");
            });
        }
    });

    window.addEventListener("click", function(event) {
        if (modal && event.target === modal) {
            modal.classList.remove("open");
        }
    });

    // ==========================================
    // 4. VALIDASI & SUBMIT FORM NEWSLETTER
    // ==========================================
    const newsletterForm = document.getElementById("newsletter-form");
    if (newsletterForm) {
        newsletterForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            
            if (emailInput && emailInput.value.trim() !== "") {
                openModal(
                    "Fitur Masih dalam Pengembangan!",
                    `Maaf! Email Anda (${emailInput.value}) belum terdaftar dalam buletin mingguan RasaHati. Fitur masih dalam tahap pengembangan`,
                    true
                );
                emailInput.value = "";
            } else {
                openModal(
                    "Formulir Tidak Valid",
                    "Silakan masukkan alamat email yang benar untuk melanjutkan.",
                    false
                );
            }
        });
    }

    // ==========================================
    // 5. BUTTON & CARDS ACTION INTERACTION
    // ==========================================
    const btnMealPlan = document.getElementById("btn-meal-plan");
    if (btnMealPlan) {
        btnMealPlan.addEventListener("click", function() {
            openModal(
                "Meal Planning Masih dalam Pengembangan!",
                "Modul Perencana Masih dalam Pengembangan! Comingsoon.",
                true
            );
        });
    }

    // ==========================================
    // 6. SCROLL ANIMATION SYSTEM (FADE-UP ON SCROLL)
    // ==========================================
    const sectionsToAnimate = [
        '.section-header',
        '.category-card',
        '.recipe-card',
        '.benefit-card',
        '.meal-plan-card',
        '.testi-card',
        '.newsletter-card'
    ];

    sectionsToAnimate.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            el.classList.add('fade-in-element');
        });
    });

    document.querySelectorAll('.hero-content, .hero-visual').forEach(el => {
        el.classList.add('fade-in-element');
    });

    const scrollElements = document.querySelectorAll(".fade-in-element");

    const elementInView = (el, dividend = 1) => {
        const elementTop = el.getBoundingClientRect().top;
        return (
            elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend
        );
    };

    const displayScrollElement = (element) => {
        element.classList.add("appear");
    };

    const handleScrollAnimation = () => {
        scrollElements.forEach((el) => {
            if (elementInView(el, 1.15)) {
                displayScrollElement(el);
            }
        });
    };

    window.addEventListener("scroll", () => {
        handleScrollAnimation();
    });

    setTimeout(handleScrollAnimation, 150);
});