// Social Proof Notifications and Urgency Indicators
class SocialProofEngine {
  constructor() {
    this.notifications = [];
    this.isActive = true;
    this.notificationQueue = [];
    this.currentNotification = null;
    this.settings = {
      displayDuration: 5000,
      intervalMin: 10000,
      intervalMax: 30000,
      position: 'bottom-left',
      maxQueueSize: 10
    };
    
    this.init();
  }

  init() {
    this.createNotificationContainer();
    this.loadNotificationData();
    this.startNotificationCycle();
    this.initUrgencyIndicators();
    this.trackUserActivity();
  }

  createNotificationContainer() {
    const container = document.createElement('div');
    container.className = 'social-proof-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);
    this.container = container;
  }

  async loadNotificationData() {
    // Simulated notification data - in production, this would come from an API
    this.notificationTemplates = [
      {
        type: 'purchase',
        templates: [
          '{{name}} from {{location}} just purchased {{product}}',
          '{{product}} was just bought by someone in {{location}}',
          'A customer from {{location}} ordered {{product}} {{time}} ago'
        ]
      },
      {
        type: 'viewing',
        templates: [
          '{{count}} people are viewing {{product}} right now',
          '{{product}} is being viewed by {{count}} other customers',
          '{{count}} customers looked at {{product}} in the last hour'
        ]
      },
      {
        type: 'cart',
        templates: [
          '{{name}} added {{product}} to their cart',
          '{{product}} was just added to cart by someone in {{location}}',
          'Someone from {{location}} is checking out with {{product}}'
        ]
      },
      {
        type: 'stock',
        templates: [
          'Only {{count}} left in stock for {{product}}',
          '{{product}} is selling fast! {{count}} remaining',
          'Hurry! Limited stock available for {{product}}'
        ]
      },
      {
        type: 'discount',
        templates: [
          'Flash Sale: {{discount}}% off {{product}} ends in {{time}}',
          'Limited time offer on {{product}} - Save {{discount}}%',
          '{{product}} special price expires in {{time}}'
        ]
      }
    ];

    // Simulated data sources
    this.mockData = {
      names: ['Priya', 'Arjun', 'Neha', 'Raj', 'Ananya', 'Vikram', 'Shreya', 'Amit'],
      locations: ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Kolkata', 'Pune', 'Hyderabad', 'Jaipur'],
      products: ['Oud Royale', 'Rose Saffron Attar', 'Musk Collection', 'Floral Essence', 'Woody Notes'],
      times: ['2 minutes', '5 minutes', '10 minutes', '15 minutes', '30 minutes']
    };

    // Load real product data if available
    await this.fetchRealTimeData();
  }

  async fetchRealTimeData() {
    try {
      const response = await fetch('/api/social-proof', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      
      if (response.ok) {
        const data = await response.json();
        this.realTimeData = data;
      }
    } catch (error) {
      console.log('Using mock data for social proof');
    }
  }

  generateNotification() {
    const types = Object.keys(this.notificationTemplates);
    const randomType = types[Math.floor(Math.random() * types.length)];
    const typeData = this.notificationTemplates.find(t => t.type === randomType);
    const template = typeData.templates[Math.floor(Math.random() * typeData.templates.length)];
    
    // Generate notification data
    const data = this.generateNotificationData(randomType);
    let message = template;
    
    // Replace placeholders
    Object.keys(data).forEach(key => {
      message = message.replace(`{{${key}}}`, data[key]);
    });
    
    return {
      type: randomType,
      message: message,
      icon: this.getIconForType(randomType),
      id: Date.now()
    };
  }

  generateNotificationData(type) {
    const data = {};
    
    switch (type) {
      case 'purchase':
      case 'cart':
        data.name = this.getRandomItem(this.mockData.names);
        data.location = this.getRandomItem(this.mockData.locations);
        data.product = this.getRandomItem(this.mockData.products);
        data.time = this.getRandomItem(this.mockData.times);
        break;
        
      case 'viewing':
        data.count = Math.floor(Math.random() * 15) + 5;
        data.product = this.getRandomItem(this.mockData.products);
        break;
        
      case 'stock':
        data.count = Math.floor(Math.random() * 10) + 1;
        data.product = this.getRandomItem(this.mockData.products);
        break;
        
      case 'discount':
        data.discount = [10, 15, 20, 25, 30][Math.floor(Math.random() * 5)];
        data.product = this.getRandomItem(this.mockData.products);
        data.time = this.generateCountdown();
        break;
    }
    
    return data;
  }

  getRandomItem(array) {
    return array[Math.floor(Math.random() * array.length)];
  }

  generateCountdown() {
    const hours = Math.floor(Math.random() * 24) + 1;
    const minutes = Math.floor(Math.random() * 60);
    return `${hours}h ${minutes}m`;
  }

  getIconForType(type) {
    const icons = {
      purchase: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>`,
      viewing: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
      </svg>`,
      cart: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>`,
      stock: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>`,
      discount: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
      </svg>`
    };
    
    return icons[type] || icons.purchase;
  }

  showNotification(notification) {
    if (this.currentNotification) {
      this.hideCurrentNotification();
    }
    
    const notificationEl = document.createElement('div');
    notificationEl.className = `social-proof-notification social-proof-${notification.type}`;
    notificationEl.innerHTML = `
      <div class="social-proof-icon">${notification.icon}</div>
      <div class="social-proof-content">
        <p class="social-proof-message">${notification.message}</p>
        <span class="social-proof-time">Verified</span>
      </div>
      <button class="social-proof-close" aria-label="Close notification">&times;</button>
    `;
    
    this.container.appendChild(notificationEl);
    this.currentNotification = notificationEl;
    
    // Animate in
    requestAnimationFrame(() => {
      notificationEl.classList.add('social-proof-visible');
    });
    
    // Close button
    notificationEl.querySelector('.social-proof-close').addEventListener('click', () => {
      this.hideNotification(notificationEl);
    });
    
    // Auto hide
    this.hideTimeout = setTimeout(() => {
      this.hideNotification(notificationEl);
    }, this.settings.displayDuration);
    
    // Track analytics
    this.trackNotificationView(notification);
  }

  hideNotification(notificationEl) {
    if (!notificationEl) return;
    
    notificationEl.classList.remove('social-proof-visible');
    setTimeout(() => {
      if (notificationEl.parentNode) {
        notificationEl.parentNode.removeChild(notificationEl);
      }
    }, 300);
    
    if (notificationEl === this.currentNotification) {
      this.currentNotification = null;
      clearTimeout(this.hideTimeout);
    }
  }

  hideCurrentNotification() {
    if (this.currentNotification) {
      this.hideNotification(this.currentNotification);
    }
  }

  startNotificationCycle() {
    if (!this.isActive) return;
    
    const showNext = () => {
      if (!this.isActive) return;
      
      const notification = this.generateNotification();
      this.showNotification(notification);
      
      // Schedule next notification
      const nextDelay = Math.random() * 
        (this.settings.intervalMax - this.settings.intervalMin) + 
        this.settings.intervalMin;
        
      this.cycleTimeout = setTimeout(showNext, nextDelay);
    };
    
    // Start after initial delay
    setTimeout(showNext, 5000);
  }

  stopNotifications() {
    this.isActive = false;
    clearTimeout(this.cycleTimeout);
    this.hideCurrentNotification();
  }

  resumeNotifications() {
    this.isActive = true;
    this.startNotificationCycle();
  }

  // Urgency Indicators
  initUrgencyIndicators() {
    this.addStockIndicators();
    this.addCountdownTimers();
    this.addViewerCounts();
    this.addLimitedOffers();
  }

  addStockIndicators() {
    document.querySelectorAll('[data-stock-count]').forEach(element => {
      const stock = parseInt(element.dataset.stockCount);
      
      if (stock <= 5 && stock > 0) {
        const indicator = document.createElement('div');
        indicator.className = 'urgency-indicator stock-low';
        indicator.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <span>Only ${stock} left in stock!</span>
        `;
        element.appendChild(indicator);
        
        // Pulse animation
        indicator.classList.add('pulse-animation');
      }
    });
  }

  addCountdownTimers() {
    document.querySelectorAll('[data-countdown]').forEach(element => {
      const endTime = new Date(element.dataset.countdown).getTime();
      
      const updateTimer = () => {
        const now = new Date().getTime();
        const distance = endTime - now;
        
        if (distance < 0) {
          element.innerHTML = 'Offer Expired';
          return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.innerHTML = `
          <div class="countdown-timer">
            <div class="countdown-unit">
              <span class="countdown-value">${days}</span>
              <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-unit">
              <span class="countdown-value">${hours}</span>
              <span class="countdown-label">Hours</span>
            </div>
            <div class="countdown-unit">
              <span class="countdown-value">${minutes}</span>
              <span class="countdown-label">Mins</span>
            </div>
            <div class="countdown-unit">
              <span class="countdown-value">${seconds}</span>
              <span class="countdown-label">Secs</span>
            </div>
          </div>
        `;
        
        if (distance < 3600000) { // Less than 1 hour
          element.classList.add('countdown-urgent');
        }
      };
      
      updateTimer();
      setInterval(updateTimer, 1000);
    });
  }

  addViewerCounts() {
    document.querySelectorAll('[data-viewers]').forEach(element => {
      const baseCount = parseInt(element.dataset.viewers) || 5;
      
      const updateViewers = () => {
        const variation = Math.floor(Math.random() * 5) - 2;
        const currentViewers = Math.max(1, baseCount + variation);
        
        element.innerHTML = `
          <div class="viewer-count">
            <span class="viewer-dot"></span>
            <span>${currentViewers} people viewing this</span>
          </div>
        `;
      };
      
      updateViewers();
      setInterval(updateViewers, 30000); // Update every 30 seconds
    });
  }

  addLimitedOffers() {
    document.querySelectorAll('[data-limited-offer]').forEach(element => {
      const offerType = element.dataset.limitedOffer;
      
      const badge = document.createElement('div');
      badge.className = 'limited-offer-badge';
      
      switch (offerType) {
        case 'flash':
          badge.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
            <span>Flash Sale</span>
          `;
          break;
        case 'exclusive':
          badge.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
            <span>Exclusive</span>
          `;
          break;
        case 'limited':
          badge.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Limited Time</span>
          `;
          break;
      }
      
      element.appendChild(badge);
    });
  }

  trackUserActivity() {
    // Track page visibility
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.stopNotifications();
      } else {
        this.resumeNotifications();
      }
    });
    
    // Track user engagement
    let idleTime = 0;
    const idleInterval = setInterval(() => {
      idleTime++;
      if (idleTime > 10) { // 10 minutes
        this.stopNotifications();
      }
    }, 60000);
    
    // Reset idle time on user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
      document.addEventListener(event, () => {
        idleTime = 0;
        if (!this.isActive) {
          this.resumeNotifications();
        }
      });
    });
  }

  trackNotificationView(notification) {
    // Analytics tracking
    if (typeof gtag !== 'undefined') {
      gtag('event', 'social_proof_view', {
        'event_category': 'engagement',
        'event_label': notification.type,
        'value': notification.id
      });
    }
  }
}

// Initialize Social Proof Engine
document.addEventListener('DOMContentLoaded', () => {
  window.socialProofEngine = new SocialProofEngine();
});