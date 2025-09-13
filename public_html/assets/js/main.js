(function () {
    'use strict';
  
    // Add to Cart with fetch and toast notification
    const addToCartForms = document.querySelectorAll('[data-add-to-cart]');
    
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }
    
    // Toast notification function
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast toast--${type}`;
      toast.innerHTML = `
        <div class="toast__content">
          <span>${message}</span>
          <button class="toast__close" aria-label="Close notification">&times;</button>
        </div>
      `;
      
      toastContainer.appendChild(toast);
      
      // Animate in
      setTimeout(() => toast.classList.add('toast--visible'), 10);
      
      // Auto dismiss after 3 seconds
      const dismissTimeout = setTimeout(() => {
        dismissToast(toast);
      }, 3000);
      
      // Close button
      toast.querySelector('.toast__close').addEventListener('click', () => {
        clearTimeout(dismissTimeout);
        dismissToast(toast);
      });
    }
    
    function dismissToast(toast) {
      toast.classList.remove('toast--visible');
      toast.addEventListener('transitionend', () => toast.remove());
    }
    
    addToCartForms.forEach(form => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.classList.add('btn--loading');
        }
        
        const fd = new FormData(form);
        try {
          const res = await fetch('/cart.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const data = await res.json();
          if (data && data.success) {
            // Update cart badge with animation
            const badge = document.querySelector('.header .badge');
            if (badge && data.cartCount != null) {
              badge.textContent = data.cartCount;
              badge.classList.add('badge--pulse');
              setTimeout(() => badge.classList.remove('badge--pulse'), 1000);
            }
            showToast('Product added to cart successfully!');
          } else {
            showToast((data && data.error) || 'Could not add to cart', 'error');
          }
        } catch (_) {
          showToast('Network error. Please try again.', 'error');
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn--loading');
          }
        }
      });
    });
  
    // Enhanced Accessible Accordion with smooth animations
    document.querySelectorAll('[data-accordion]').forEach(btn => {
      btn.addEventListener('click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        const id = btn.getAttribute('aria-controls');
        const panel = document.getElementById(id);
        
        btn.setAttribute('aria-expanded', String(!expanded));
        
        if (panel) {
          if (expanded) {
            // Collapse animation
            panel.style.height = panel.scrollHeight + 'px';
            // Force reflow
            panel.offsetHeight;
            panel.style.height = '0';
            panel.style.opacity = '0';
            panel.style.overflow = 'hidden';
            
            panel.addEventListener('transitionend', function collapseEnd(e) {
              if (e.propertyName === 'height') {
                panel.removeEventListener('transitionend', collapseEnd);
                panel.setAttribute('hidden', '');
                panel.style.height = '';
                panel.style.opacity = '';
                panel.style.overflow = '';
              }
            });
          } else {
            // Expand animation
            panel.removeAttribute('hidden');
            panel.style.height = '0';
            panel.style.opacity = '0';
            panel.style.overflow = 'hidden';
            
            // Force reflow
            panel.offsetHeight;
            
            panel.style.height = panel.scrollHeight + 'px';
            panel.style.opacity = '1';
            
            panel.addEventListener('transitionend', function expandEnd(e) {
              if (e.propertyName === 'height') {
                panel.removeEventListener('transitionend', expandEnd);
                panel.style.height = '';
                panel.style.opacity = '';
                panel.style.overflow = '';
              }
            });
          }
        }
      });
    });
  
    // Advanced Service Worker with update notifications
    if ('serviceWorker' in navigator && window.isSecureContext) {
      window.addEventListener('load', async () => {
        try {
          const registration = await navigator.serviceWorker.register('/service-worker-advanced.js');
          
          // Check for updates
          registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New service worker available
                showUpdateNotification();
              }
            });
          });
          
          // Enable periodic background sync
          if ('periodicSync' in registration) {
            try {
              await registration.periodicSync.register('update-prices', {
                minInterval: 24 * 60 * 60 * 1000 // 24 hours
              });
            } catch (error) {
              console.log('Periodic sync not available');
            }
          }
        } catch (error) {
          console.error('Service worker registration failed:', error);
        }
      });
      
      // Show update notification
      function showUpdateNotification() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'update-banner';
        updateBanner.innerHTML = `
          <div class="update-banner__content">
            <span>A new version of PerfumeStore is available!</span>
            <button class="update-banner__button" onclick="window.location.reload()">Update Now</button>
          </div>
        `;
        document.body.appendChild(updateBanner);
        setTimeout(() => updateBanner.classList.add('update-banner--visible'), 100);
      }
    }
    
    // Intersection Observer for scroll animations
    const observeElements = document.querySelectorAll('.will-appear, .product-card, .section, .hero__content, .carousel__item');
    
    if ('IntersectionObserver' in window) {
      const appearOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
      };
      
      const appearOnScroll = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('has-appeared');
          observer.unobserve(entry.target);
        });
      }, appearOptions);
      
      observeElements.forEach(elem => {
        elem.classList.add('will-appear');
        appearOnScroll.observe(elem);
      });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
          e.preventDefault();
          targetElement.scrollIntoView({ behavior: 'smooth' });
          
          // Update URL without page jump
          history.pushState(null, null, targetId);
        }
      });
    });
  })();