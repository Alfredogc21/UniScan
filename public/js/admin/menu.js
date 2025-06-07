/**
 * Menu/Help Page JavaScript for UniScan Admin
 * Created: June 6, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle image loading errors
    const images = document.querySelectorAll('.help-image img');
    images.forEach(img => {
        img.onerror = function() {
            this.onerror = null;
            this.src = '/img/uniscan_logo.png'; // Fallback image
        };
    });
    
    // Help Card Toggle Functionality
    const helpCards = document.querySelectorAll('.help-card');
    
    helpCards.forEach(card => {
        const header = card.querySelector('.help-card__header');
        const content = card.querySelector('.help-card__content');
        const toggle = card.querySelector('.help-card__toggle');
        
        // Add click event listener to header
        header.addEventListener('click', function() {
            // Toggle active class
            content.classList.toggle('active');
            toggle.classList.toggle('active');
            
            // Close other cards when opening a new one
            if (content.classList.contains('active')) {
                helpCards.forEach(otherCard => {
                    if (otherCard !== card) {
                        const otherContent = otherCard.querySelector('.help-card__content');
                        const otherToggle = otherCard.querySelector('.help-card__toggle');
                        otherContent.classList.remove('active');
                        otherToggle.classList.remove('active');
                    }
                });
            }
        });

        // Open first card by default
        if (card.id === 'dashboard-section') {
            content.classList.add('active');
            toggle.classList.add('active');
        }
    });

    // Accordion Functionality for FAQs
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        const content = item.querySelector('.accordion-content');
        
        header.addEventListener('click', function() {
            // Toggle active class
            header.classList.toggle('active');
            content.classList.toggle('active');
            
            // Update icon
            const icon = header.querySelector('i');
            if (header.classList.contains('active')) {
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
            
            // Close other accordion items when opening a new one
            accordionItems.forEach(otherItem => {
                if (otherItem !== item) {
                    const otherHeader = otherItem.querySelector('.accordion-header');
                    const otherContent = otherItem.querySelector('.accordion-content');
                    const otherIcon = otherHeader.querySelector('i');
                    
                    otherHeader.classList.remove('active');
                    otherContent.classList.remove('active');
                    otherIcon.classList.remove('fa-minus');
                    otherIcon.classList.add('fa-plus');
                }
            });
        });
    });

    // Mobile responsiveness for help cards
    function checkScreenSize() {
        const activeCards = document.querySelectorAll('.help-card__content.active');
        
        if (window.innerWidth < 768) {
            activeCards.forEach(card => {
                // Add specific mobile styles if needed
                card.style.padding = '1rem';
            });
        } else {
            activeCards.forEach(card => {
                // Reset to default padding for larger screens
                card.style.padding = '0 1.5rem 1.5rem';
            });
        }
    }

    // Initial check
    checkScreenSize();
    
    // Listen for window resize
    window.addEventListener('resize', debounce(checkScreenSize, 250));
    
    // Debounce function to limit resize event firing
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Add touch swipe detection for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    const helpContainer = document.querySelector('.help-container');
    
    helpContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    helpContainer.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);
    
    function handleSwipe() {
        const swipeThreshold = 50;
        
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe left - could add functionality here
        }
        
        if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe right - could add functionality here
        }
    }
      // Support button functionality
    // Removing event listener to allow default email behavior
    // The email link now works naturally with mailto: protocol

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
});
