(function(){
    'use strict';
    const carousels = document.querySelectorAll('[data-carousel]');
    
    carousels.forEach(carousel => {
      const items = carousel.querySelectorAll('.carousel__item');
      const itemCount = items.length;
      if (itemCount === 0) return;
      
      // Add pagination dots if more than one item
      if (itemCount > 1) {
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'carousel__pagination';
        
        for (let i = 0; i < itemCount; i++) {
          const dot = document.createElement('button');
          dot.className = 'carousel__dot';
          dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
          dot.dataset.index = i;
          paginationContainer.appendChild(dot);
        }
        
        carousel.appendChild(paginationContainer);
      }
      
      let index = 0;
      let startX, moveX;
      let isAnimating = false;
      
      // Update carousel state
      function update(animate = true) {
        if (isAnimating) return;
        
        // Update items position with smooth transition
        items.forEach((item, i) => {
          item.style.transform = `translateX(${(i-index)*100}%)`;
          item.style.transition = animate ? 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1)' : '';
          
          // Set aria attributes for accessibility
          item.setAttribute('aria-hidden', i !== index);
        });
        
        // Update pagination dots
        const dots = carousel.querySelectorAll('.carousel__dot');
        dots.forEach((dot, i) => {
          dot.classList.toggle('carousel__dot--active', i === index);
          dot.setAttribute('aria-current', i === index ? 'true' : 'false');
        });
        
        // Update prev/next buttons state
        const prevBtn = carousel.querySelector('[data-carousel-prev]');
        const nextBtn = carousel.querySelector('[data-carousel-next]');
        
        if (prevBtn) prevBtn.classList.toggle('carousel__btn--disabled', index === 0);
        if (nextBtn) nextBtn.classList.toggle('carousel__btn--disabled', index === itemCount - 1);
      }
      
      // Initialize
      update(false);
      
      // Add event listeners for navigation buttons
      carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => {
        if (index > 0) {
          index--;
          update();
        } else {
          // Optional: Add bounce effect for edge cases
          items.forEach(item => {
            item.style.transform = `translateX(${(parseInt(item.style.transform.replace('translateX(', '')) + 3)}%)`;
            setTimeout(() => {
              item.style.transform = `translateX(${(parseInt(item.style.transform.replace('translateX(', '')) - 3)}%)`;
            }, 150);
          });
        }
      });
      
      carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => {
        if (index < itemCount - 1) {
          index++;
          update();
        } else {
          // Optional: Add bounce effect for edge cases
          items.forEach(item => {
            item.style.transform = `translateX(${(parseInt(item.style.transform.replace('translateX(', '')) - 3)}%)`;
            setTimeout(() => {
              item.style.transform = `translateX(${(parseInt(item.style.transform.replace('translateX(', '')) + 3)}%)`;
            }, 150);
          });
        }
      });
      
      // Add event listeners for pagination dots
      carousel.querySelectorAll('.carousel__dot').forEach(dot => {
        dot.addEventListener('click', () => {
          index = parseInt(dot.dataset.index);
          update();
        });
      });
      
      // Touch support
      carousel.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        moveX = 0;
        
        // Disable transition during touch
        items.forEach(item => {
          item.style.transition = '';
        });
      }, { passive: true });
      
      carousel.addEventListener('touchmove', (e) => {
        if (!startX) return;
        
        moveX = e.touches[0].clientX - startX;
        const movePercent = (moveX / carousel.offsetWidth) * 100;
        
        // Move all items according to touch position
        items.forEach((item, i) => {
          item.style.transform = `translateX(${((i-index)*100) + movePercent}%)`;
        });
      }, { passive: true });
      
      carousel.addEventListener('touchend', () => {
        if (!startX || !moveX) return;
        
        // Determine if we should change slide based on move distance
        if (Math.abs(moveX) > carousel.offsetWidth / 4) {
          if (moveX > 0 && index > 0) {
            index--;
          } else if (moveX < 0 && index < itemCount - 1) {
            index++;
          }
        }
        
        // Reset and update with animation
        startX = null;
        moveX = null;
        update();
      });
      
      // Auto-advance carousel (optional)
      if (carousel.hasAttribute('data-carousel-auto')) {
        const interval = parseInt(carousel.getAttribute('data-carousel-auto')) || 5000;
        
        let autoplayTimer = setInterval(() => {
          if (document.hidden) return; // Pause when page is not visible
          
          if (index < itemCount - 1) {
            index++;
          } else {
            index = 0; // Loop back to first slide
          }
          update();
        }, interval);
        
        // Pause autoplay on hover/touch
        carousel.addEventListener('mouseenter', () => clearInterval(autoplayTimer));
        carousel.addEventListener('touchstart', () => clearInterval(autoplayTimer), { passive: true });
        
        // Resume autoplay when mouse leaves
        carousel.addEventListener('mouseleave', () => {
          clearInterval(autoplayTimer);
          autoplayTimer = setInterval(() => {
            if (index < itemCount - 1) {
              index++;
            } else {
              index = 0;
            }
            update();
          }, interval);
        });
      }
      
      // Keyboard navigation
      carousel.setAttribute('tabindex', '0');
      carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' && index > 0) {
          index--;
          update();
        } else if (e.key === 'ArrowRight' && index < itemCount - 1) {
          index++;
          update();
        }
      });
    });
  })();