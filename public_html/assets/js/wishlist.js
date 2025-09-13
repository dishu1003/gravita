// Advanced Wishlist System with Local Storage and Server Sync
class WishlistManager {
  constructor() {
    this.wishlistKey = 'perfumestore_wishlist';
    this.syncKey = 'perfumestore_wishlist_sync';
    this.wishlist = this.loadWishlist();
    this.syncQueue = this.loadSyncQueue();
    this.isAuthenticated = false;
    this.init();
  }

  init() {
    this.checkAuthentication();
    this.attachEventListeners();
    this.updateWishlistUI();
    this.syncWithServer();
    this.initPeriodicSync();
  }

  checkAuthentication() {
    // Check if user is logged in
    this.isAuthenticated = document.body.dataset.authenticated === 'true';
  }

  loadWishlist() {
    try {
      const saved = localStorage.getItem(this.wishlistKey);
      return saved ? JSON.parse(saved) : [];
    } catch (e) {
      console.error('Failed to load wishlist:', e);
      return [];
    }
  }

  loadSyncQueue() {
    try {
      const saved = localStorage.getItem(this.syncKey);
      return saved ? JSON.parse(saved) : [];
    } catch (e) {
      return [];
    }
  }

  saveWishlist() {
    try {
      localStorage.setItem(this.wishlistKey, JSON.stringify(this.wishlist));
      this.updateWishlistUI();
      this.showWishlistNotification();
    } catch (e) {
      console.error('Failed to save wishlist:', e);
    }
  }

  saveSyncQueue() {
    try {
      localStorage.setItem(this.syncKey, JSON.stringify(this.syncQueue));
    } catch (e) {
      console.error('Failed to save sync queue:', e);
    }
  }

  attachEventListeners() {
    // Wishlist toggle buttons
    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-wishlist-toggle]')) {
        e.preventDefault();
        const button = e.target.closest('[data-wishlist-toggle]');
        const productId = button.dataset.productId;
        const productData = {
          id: productId,
          name: button.dataset.productName,
          price: button.dataset.productPrice,
          image: button.dataset.productImage,
          slug: button.dataset.productSlug
        };
        this.toggleWishlist(productData);
      }
    });

    // Listen for storage changes from other tabs
    window.addEventListener('storage', (e) => {
      if (e.key === this.wishlistKey) {
        this.wishlist = this.loadWishlist();
        this.updateWishlistUI();
      }
    });

    // Sync on visibility change
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        this.syncWithServer();
      }
    });
  }

  toggleWishlist(product) {
    const index = this.wishlist.findIndex(item => item.id === product.id);
    
    if (index > -1) {
      // Remove from wishlist
      this.wishlist.splice(index, 1);
      this.addToSyncQueue({ action: 'remove', productId: product.id });
      this.showToast(`${product.name} removed from wishlist`, 'info');
    } else {
      // Add to wishlist
      this.wishlist.push({
        ...product,
        addedAt: new Date().toISOString()
      });
      this.addToSyncQueue({ action: 'add', productId: product.id });
      this.showToast(`${product.name} added to wishlist`, 'success');
    }
    
    this.saveWishlist();
    this.updateProductButton(product.id);
    
    // Immediate sync if online
    if (navigator.onLine) {
      this.syncWithServer();
    }
  }

  addToSyncQueue(operation) {
    this.syncQueue.push({
      ...operation,
      timestamp: new Date().toISOString()
    });
    this.saveSyncQueue();
  }

  updateWishlistUI() {
    // Update all wishlist buttons
    document.querySelectorAll('[data-wishlist-toggle]').forEach(button => {
      const productId = button.dataset.productId;
      this.updateProductButton(productId);
    });

    // Update wishlist count badge
    this.updateWishlistBadge();

    // Update wishlist page if exists
    this.updateWishlistPage();
  }

  updateProductButton(productId) {
    const buttons = document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${productId}"]`);
    const isInWishlist = this.wishlist.some(item => item.id === productId);
    
    buttons.forEach(button => {
      const icon = button.querySelector('.wishlist-icon');
      const text = button.querySelector('.wishlist-text');
      
      if (isInWishlist) {
        button.classList.add('wishlist-active');
        button.setAttribute('aria-pressed', 'true');
        if (icon) {
          icon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          `;
        }
        if (text) text.textContent = 'In Wishlist';
      } else {
        button.classList.remove('wishlist-active');
        button.setAttribute('aria-pressed', 'false');
        if (icon) {
          icon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          `;
        }
        if (text) text.textContent = 'Add to Wishlist';
      }
    });
  }

  updateWishlistBadge() {
    const badges = document.querySelectorAll('.wishlist-badge');
    const count = this.wishlist.length;
    
    badges.forEach(badge => {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-flex' : 'none';
      
      // Animate badge on change
      badge.classList.add('badge--pulse');
      setTimeout(() => badge.classList.remove('badge--pulse'), 600);
    });
  }

  updateWishlistPage() {
    const wishlistContainer = document.querySelector('[data-wishlist-container]');
    if (!wishlistContainer) return;

    if (this.wishlist.length === 0) {
      wishlistContainer.innerHTML = `
        <div class="wishlist-empty">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <h3>Your wishlist is empty</h3>
          <p>Start adding products you love!</p>
          <a href="/shop.php" class="btn btn--primary">Browse Products</a>
        </div>
      `;
    } else {
      const productsHTML = this.wishlist.map(product => `
        <div class="wishlist-item" data-wishlist-item="${product.id}">
          <a href="/product.php?slug=${product.slug}" class="wishlist-item__image">
            <img src="/uploads/${product.image}" alt="${product.name}" loading="lazy">
          </a>
          <div class="wishlist-item__info">
            <h3><a href="/product.php?slug=${product.slug}">${product.name}</a></h3>
            <p class="wishlist-item__price">₹${product.price}</p>
            <p class="wishlist-item__added">Added ${this.formatDate(product.addedAt)}</p>
          </div>
          <div class="wishlist-item__actions">
            <form data-add-to-cart>
              <input type="hidden" name="product_id" value="${product.id}">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="btn btn--primary btn--sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="9" cy="21" r="1"></circle>
                  <circle cx="20" cy="21" r="1"></circle>
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Add to Cart
              </button>
            </form>
            <button 
              class="btn btn--outline btn--sm" 
              data-wishlist-toggle
              data-product-id="${product.id}"
              data-product-name="${product.name}"
              data-product-price="${product.price}"
              data-product-image="${product.image}"
              data-product-slug="${product.slug}"
            >
              Remove
            </button>
          </div>
        </div>
      `).join('');

      wishlistContainer.innerHTML = `
        <div class="wishlist-header">
          <h2>My Wishlist (${this.wishlist.length} items)</h2>
          <button class="btn btn--outline" onclick="wishlistManager.shareWishlist()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="18" cy="5" r="3"></circle>
              <circle cx="6" cy="12" r="3"></circle>
              <circle cx="18" cy="19" r="3"></circle>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg>
            Share Wishlist
          </button>
        </div>
        <div class="wishlist-grid">
          ${productsHTML}
        </div>
      `;
    }
  }

  async syncWithServer() {
    if (!this.isAuthenticated || !navigator.onLine || this.syncQueue.length === 0) {
      return;
    }

    try {
      const response = await fetch('/api/wishlist/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          operations: this.syncQueue,
          currentWishlist: this.wishlist.map(item => item.id)
        })
      });

      if (response.ok) {
        const data = await response.json();
        
        // Update local wishlist with server data
        if (data.wishlist) {
          this.wishlist = data.wishlist;
          this.saveWishlist();
        }
        
        // Clear sync queue
        this.syncQueue = [];
        this.saveSyncQueue();
      }
    } catch (error) {
      console.error('Wishlist sync failed:', error);
    }
  }

  initPeriodicSync() {
    // Sync every 5 minutes if online
    setInterval(() => {
      if (navigator.onLine) {
        this.syncWithServer();
      }
    }, 5 * 60 * 1000);
  }

  shareWishlist() {
    const wishlistUrl = `${window.location.origin}/wishlist/shared/${this.generateShareId()}`;
    const shareText = `Check out my wishlist at PerfumeStore! ${this.wishlist.length} amazing fragrances.`;

    if (navigator.share) {
      navigator.share({
        title: 'My PerfumeStore Wishlist',
        text: shareText,
        url: wishlistUrl
      }).catch(err => console.log('Share failed:', err));
    } else {
      // Fallback to copy link
      this.copyToClipboard(wishlistUrl);
      this.showToast('Wishlist link copied to clipboard!', 'success');
    }
  }

  generateShareId() {
    // Generate a unique share ID based on wishlist content
    const ids = this.wishlist.map(item => item.id).sort().join('-');
    return btoa(ids).replace(/[^a-zA-Z0-9]/g, '').substring(0, 10);
  }

  copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
  }

  formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'today';
    if (diffDays === 1) return 'yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    
    return date.toLocaleDateString();
  }

  showWishlistNotification() {
    // Create floating wishlist preview
    const preview = document.createElement('div');
    preview.className = 'wishlist-preview';
    preview.innerHTML = `
      <div class="wishlist-preview__header">
        <h4>Wishlist Updated</h4>
        <span class="wishlist-preview__count">${this.wishlist.length} items</span>
      </div>
      <div class="wishlist-preview__items">
        ${this.wishlist.slice(-3).map(item => `
          <div class="wishlist-preview__item">
            <img src="/uploads/${item.image}" alt="${item.name}">
            <span>${item.name}</span>
          </div>
        `).join('')}
      </div>
      <a href="/wishlist.php" class="wishlist-preview__link">View Wishlist</a>
    `;
    
    document.body.appendChild(preview);
    
    // Animate in
    setTimeout(() => preview.classList.add('wishlist-preview--visible'), 10);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
      preview.classList.remove('wishlist-preview--visible');
      setTimeout(() => preview.remove(), 300);
    }, 3000);
  }

  showToast(message, type = 'info') {
    // Reuse toast from main.js or create new one
    if (window.showToast) {
      window.showToast(message, type);
    } else {
      // Simple fallback
      const toast = document.createElement('div');
      toast.className = `toast toast--${type}`;
      toast.textContent = message;
      document.body.appendChild(toast);
      
      setTimeout(() => toast.classList.add('toast--visible'), 10);
      setTimeout(() => {
        toast.classList.remove('toast--visible');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }
  }
}

// Initialize wishlist manager
document.addEventListener('DOMContentLoaded', () => {
  window.wishlistManager = new WishlistManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = WishlistManager;
}