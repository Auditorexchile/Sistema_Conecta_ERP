/**
 * AuditorEx Chile - JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funciones
    initNavigation();
    initSlider();
    initScrollEffects();
    
    console.log('AuditorEx Chile - Website loaded');
});

// Navegación móvil
function initNavigation() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    const dropdowns = document.querySelectorAll('.dropdown');
    
    // Toggle menú móvil
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Cerrar menú al hacer click en un link
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
            }
        });
    });
    
    // Dropdown en móvil
    dropdowns.forEach(dropdown => {
        const dropdownLink = dropdown.querySelector('a');
        if (dropdownLink) {
            dropdownLink.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        }
    });
    
    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(e) {
        if (navMenu && navToggle &&
            !navMenu.contains(e.target) &&
            !navToggle.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });
}

// Slider del Hero
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dotsContainer = document.querySelector('.slider-dots');
    
    if (!slides.length) return;
    
    let currentSlide = 0;
    let slideInterval;
    
    // Crear dots
    if (dotsContainer) {
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('slider-dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
    }
    
    const dots = document.querySelectorAll('.slider-dot');
    
    function goToSlide(index) {
        slides[currentSlide].classList.remove('active');
        if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
        
        currentSlide = index;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
        
        resetInterval();
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }
    
    function resetInterval() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    // Exponer funciones globalmente para botones en HTML
    window.cambiarSlide = function(direction) {
        if (direction === 1) {
            nextSlide();
        } else {
            prevSlide();
        }
    };
    
    // Auto-play
    resetInterval();
    
    // Pausar en hover
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', () => clearInterval(slideInterval));
        heroSection.addEventListener('mouseleave', resetInterval);
    }
}

// Efectos de scroll
function initScrollEffects() {
    const scrollTop = document.getElementById('scrollTop');
    
    // Mostrar/ocultar botón scroll to top
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            if (scrollTop) scrollTop.classList.add('show');
        } else {
            if (scrollTop) scrollTop.classList.remove('show');
        }
        
        // Sticky navbar (ya está fixed, pero se puede agregar efecto)
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.pageYOffset > 100) {
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            } else {
                navbar.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            }
        }
    });
    
    // Scroll to top
    if (scrollTop) {
        scrollTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Smooth scroll para links internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#' || href === '') return;
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const navbar = document.querySelector('.navbar');
            const navbarHeight = navbar ? navbar.offsetHeight : 0;
            const targetPosition = target.offsetTop - navbarHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Validación simple de formulario
const forms = document.querySelectorAll('form');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredInputs = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#e63946';
            } else {
                input.style.borderColor = '';
            }
        });
        
        // Si el formulario usa mailto:, dejarlo pasar
        if (form.action.includes('mailto:')) {
            return true;
        }
    });
});

// Animación de entrada para elementos al hacer scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observar elementos con animación
document.querySelectorAll('.servicio-card, .caracteristica-item, .info-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});
