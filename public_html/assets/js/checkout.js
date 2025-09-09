(function(){
    'use strict';
    const form = document.getElementById('checkout-form');
    if (!form) return;
    
    // Create loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
      <div class="loading-spinner"></div>
      <p class="loading-text">Processing your order...</p>
    `;
    document.body.appendChild(loadingOverlay);
    
    // Form validation
    function validateForm() {
      let isValid = true;
      const requiredFields = form.querySelectorAll('[required]');
      
      // Reset previous validation errors
      form.querySelectorAll('.form-error').forEach(el => el.remove());
      
      requiredFields.forEach(field => {
        field.classList.remove('input-error');
        
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('input-error');
          
          // Add error message
          const errorMsg = document.createElement('div');
          errorMsg.className = 'form-error';
          errorMsg.textContent = 'This field is required';
          field.parentNode.appendChild(errorMsg);
        }
        
        // Email validation
        if (field.type === 'email' && field.value.trim()) {
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailPattern.test(field.value)) {
            isValid = false;
            field.classList.add('input-error');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'form-error';
            errorMsg.textContent = 'Please enter a valid email address';
            field.parentNode.appendChild(errorMsg);
          }
        }
        
        // Phone validation
        if (field.name === 'phone' && field.value.trim()) {
          const phonePattern = /^\d{10}$/;
          if (!phonePattern.test(field.value.replace(/[\s-]/g, ''))) {
            isValid = false;
            field.classList.add('input-error');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'form-error';
            errorMsg.textContent = 'Please enter a valid 10-digit phone number';
            field.parentNode.appendChild(errorMsg);
          }
        }
      });
      
      return isValid;
    }
    
    // Show loading overlay
    function showLoading() {
      loadingOverlay.classList.add('loading-overlay--visible');
    }
    
    // Hide loading overlay
    function hideLoading() {
      loadingOverlay.classList.remove('loading-overlay--visible');
    }
    
    // Show toast notification
    function showToast(message, type = 'success') {
      // Check if toast container exists, create if not
      let toastContainer = document.querySelector('.toast-container');
      if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
      }
      
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
      
      // Auto dismiss after 5 seconds
      const dismissTimeout = setTimeout(() => {
        dismissToast(toast);
      }, 5000);
      
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
    
    // Form submission handler
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      
      // Validate form
      if (!validateForm()) {
        // Scroll to first error
        const firstError = form.querySelector('.input-error');
        if (firstError) {
          firstError.focus();
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      
      // Disable submit button and show loading
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('btn--loading');
      }
      
      showLoading();
      
      try {
        const fd = new FormData(form);
        const res = await fetch(window.__CHECKOUT_ENDPOINT__ || '/checkout.php', { 
          method: 'POST', 
          body: fd 
        });
        
        const data = await res.json().catch(() => null);
        
        if (!data || !data.success) { 
          hideLoading();
          showToast((data && data.error) || 'Checkout error. Please try again.', 'error');
          
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn--loading');
          }
          return; 
        }
        
        // Configure Razorpay
        const options = {
          key: data.key_id,
          amount: data.rzp_order.amount,
          currency: data.rzp_order.currency,
          name: 'PerfumeStore',
          description: 'Order #' + data.order_id,
          order_id: data.rzp_order.id,
          prefill: { name: data.customer.name, email: data.customer.email, contact: data.customer.phone },
          notes: { order_id: String(data.order_id) },
          handler: function () { 
            showToast('Payment successful! Redirecting to your orders...', 'success');
            setTimeout(() => {
              window.location.href = '/account/orders.php'; 
            }, 2000);
          },
          modal: {
            ondismiss: function() {
              hideLoading();
              if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn--loading');
              }
              showToast('Payment cancelled. You can try again when ready.', 'error');
            }
          },
          theme: { color: '#6366f1' }
        };
        
        const rzp = new Razorpay(options);
        
        // Add event handlers
        rzp.on('payment.failed', function(response) {
          hideLoading();
          showToast('Payment failed: ' + (response.error.description || 'Please try again.'), 'error');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn--loading');
          }
        });
        
        hideLoading(); // Hide loading before opening Razorpay
        rzp.open();
        
      } catch (error) {
        hideLoading();
        showToast('An unexpected error occurred. Please try again.', 'error');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('btn--loading');
        }
        console.error('Checkout error:', error);
      }
    });
  })();