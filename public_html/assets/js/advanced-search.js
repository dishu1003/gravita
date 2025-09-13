// Advanced Search with AI-powered autocomplete and visual search
class AdvancedSearch {
  constructor() {
    this.searchInput = null;
    this.searchResults = null;
    this.searchOverlay = null;
    this.debounceTimer = null;
    this.searchHistory = this.loadSearchHistory();
    this.trendingSearches = ['Oud Perfume', 'Rose Attar', 'Musk Collection', 'Travel Size'];
    this.init();
  }

  init() {
    this.createSearchUI();
    this.attachEventListeners();
    this.initVoiceSearch();
    this.initVisualSearch();
  }

  createSearchUI() {
    // Enhanced search modal
    const searchModal = document.createElement('div');
    searchModal.className = 'search-modal';
    searchModal.innerHTML = `
      <div class="search-modal__backdrop"></div>
      <div class="search-modal__container">
        <div class="search-modal__header">
          <div class="search-input-wrapper">
            <div class="search-input-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </div>
            <input type="text" class="search-modal__input" placeholder="Search perfumes, notes, collections..." autocomplete="off">
            <div class="search-input-actions">
              <button class="search-action-btn voice-search-btn" aria-label="Voice search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                  <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                  <line x1="12" y1="19" x2="12" y2="23"></line>
                  <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
              </button>
              <button class="search-action-btn visual-search-btn" aria-label="Visual search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
              </button>
            </div>
          </div>
          <button class="search-modal__close" aria-label="Close search">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="search-modal__content">
          <div class="search-suggestions">
            <div class="search-section trending-searches">
              <h3>Trending Searches</h3>
              <div class="search-tags">
                ${this.trendingSearches.map(term => `
                  <button class="search-tag" data-search="${term}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    ${term}
                  </button>
                `).join('')}
              </div>
            </div>
            
            <div class="search-section recent-searches" style="display: ${this.searchHistory.length ? 'block' : 'none'}">
              <h3>Recent Searches</h3>
              <div class="search-history-list">
                ${this.searchHistory.map(term => `
                  <button class="search-history-item" data-search="${term}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"></circle>
                      <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    ${term}
                  </button>
                `).join('')}
              </div>
            </div>
          </div>
          
          <div class="search-results" style="display: none;">
            <div class="search-results__loading">
              <div class="loading-spinner"></div>
              <span>Searching...</span>
            </div>
            <div class="search-results__content"></div>
          </div>
        </div>
        
        <input type="file" class="visual-search-input" accept="image/*" style="display: none;">
      </div>
    `;

    document.body.appendChild(searchModal);
    
    this.searchModal = searchModal;
    this.searchInput = searchModal.querySelector('.search-modal__input');
    this.searchResults = searchModal.querySelector('.search-results');
    this.searchSuggestions = searchModal.querySelector('.search-suggestions');
  }

  attachEventListeners() {
    // Open search with keyboard shortcut
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        this.openSearch();
      }
    });

    // Search button in header
    const searchTriggers = document.querySelectorAll('[data-search-trigger]');
    searchTriggers.forEach(trigger => {
      trigger.addEventListener('click', () => this.openSearch());
    });

    // Close search
    this.searchModal.querySelector('.search-modal__close').addEventListener('click', () => this.closeSearch());
    this.searchModal.querySelector('.search-modal__backdrop').addEventListener('click', () => this.closeSearch());

    // Search input
    this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    this.searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        this.saveToHistory(e.target.value);
      }
    });

    // Trending and recent searches
    this.searchModal.addEventListener('click', (e) => {
      if (e.target.matches('[data-search]')) {
        const searchTerm = e.target.getAttribute('data-search');
        this.searchInput.value = searchTerm;
        this.handleSearch(searchTerm);
      }
    });

    // Voice search
    this.searchModal.querySelector('.voice-search-btn').addEventListener('click', () => this.startVoiceSearch());

    // Visual search
    this.searchModal.querySelector('.visual-search-btn').addEventListener('click', () => {
      this.searchModal.querySelector('.visual-search-input').click();
    });

    this.searchModal.querySelector('.visual-search-input').addEventListener('change', (e) => {
      if (e.target.files && e.target.files[0]) {
        this.handleVisualSearch(e.target.files[0]);
      }
    });
  }

  openSearch() {
    this.searchModal.classList.add('search-modal--active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => this.searchInput.focus(), 100);
  }

  closeSearch() {
    this.searchModal.classList.remove('search-modal--active');
    document.body.style.overflow = '';
    this.searchInput.value = '';
    this.resetSearchView();
  }

  handleSearch(query) {
    clearTimeout(this.debounceTimer);
    
    if (query.length < 2) {
      this.resetSearchView();
      return;
    }

    this.showLoading();
    
    this.debounceTimer = setTimeout(() => {
      this.performSearch(query);
    }, 300);
  }

  async performSearch(query) {
    try {
      // Simulate AI-powered search with multiple data sources
      const results = await this.searchProducts(query);
      const suggestions = await this.getSearchSuggestions(query);
      
      this.displayResults(results, suggestions, query);
    } catch (error) {
      console.error('Search error:', error);
      this.showError();
    }
  }

  async searchProducts(query) {
    // In production, this would be an API call
    const response = await fetch(`/api/search?q=${encodeURIComponent(query)}&ai=true`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    if (!response.ok) throw new Error('Search failed');
    
    return response.json();
  }

  async getSearchSuggestions(query) {
    // AI-powered suggestions based on query
    const suggestions = [
      { type: 'category', text: `Perfumes with ${query}` },
      { type: 'note', text: `${query} scent notes` },
      { type: 'similar', text: `Similar to ${query}` }
    ];
    
    return suggestions;
  }

  displayResults(results, suggestions, query) {
    const resultsContent = this.searchModal.querySelector('.search-results__content');
    
    let html = `
      <div class="search-results-header">
        <h3>${results.count || 0} results for "${query}"</h3>
        <div class="search-filters">
          <button class="filter-chip active" data-filter="all">All</button>
          <button class="filter-chip" data-filter="perfumes">Perfumes</button>
          <button class="filter-chip" data-filter="notes">By Notes</button>
          <button class="filter-chip" data-filter="price">By Price</button>
        </div>
      </div>
    `;

    if (suggestions && suggestions.length > 0) {
      html += `
        <div class="search-ai-suggestions">
          <h4>AI Suggestions</h4>
          <div class="suggestion-chips">
            ${suggestions.map(s => `
              <button class="suggestion-chip" data-suggestion="${s.text}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                ${s.text}
              </button>
            `).join('')}
          </div>
        </div>
      `;
    }

    if (results.products && results.products.length > 0) {
      html += `
        <div class="search-products-grid">
          ${results.products.map(product => `
            <a href="/product.php?slug=${product.slug}" class="search-product-card">
              <div class="search-product-image">
                <img src="/uploads/${product.image}" alt="${product.name}" loading="lazy">
                ${product.is_bestseller ? '<span class="badge-bestseller">Bestseller</span>' : ''}
              </div>
              <div class="search-product-info">
                <h4>${this.highlightMatch(product.name, query)}</h4>
                <p class="search-product-notes">${product.scent_notes}</p>
                <div class="search-product-price">
                  ${product.mrp ? `<span class="price-mrp">₹${product.mrp}</span>` : ''}
                  <span class="price-current">₹${product.price}</span>
                </div>
              </div>
            </a>
          `).join('')}
        </div>
      `;
    } else {
      html += `
        <div class="search-no-results">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
            <path d="M11 8v3M11 14h.01"></path>
          </svg>
          <h4>No products found</h4>
          <p>Try searching with different keywords or browse our categories</p>
          <a href="/shop.php" class="btn btn--primary">Browse All Products</a>
        </div>
      `;
    }

    resultsContent.innerHTML = html;
    this.showResults();
  }

  highlightMatch(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
  }

  initVoiceSearch() {
    if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
      this.searchModal.querySelector('.voice-search-btn').style.display = 'none';
      return;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    this.recognition = new SpeechRecognition();
    this.recognition.lang = 'en-US';
    this.recognition.continuous = false;
    this.recognition.interimResults = true;

    this.recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      this.searchInput.value = transcript;
      
      if (event.results[0].isFinal) {
        this.handleSearch(transcript);
      }
    };

    this.recognition.onerror = (event) => {
      console.error('Voice search error:', event.error);
      this.showToast('Voice search failed. Please try again.', 'error');
    };
  }

  startVoiceSearch() {
    const voiceBtn = this.searchModal.querySelector('.voice-search-btn');
    voiceBtn.classList.add('voice-search-active');
    
    this.recognition.start();
    
    this.recognition.onend = () => {
      voiceBtn.classList.remove('voice-search-active');
    };
  }

  initVisualSearch() {
    // Visual search capabilities
    this.visualSearchAPI = '/api/visual-search';
  }

  async handleVisualSearch(file) {
    this.showLoading();
    
    const formData = new FormData();
    formData.append('image', file);
    
    try {
      const response = await fetch(this.visualSearchAPI, {
        method: 'POST',
        body: formData
      });
      
      if (!response.ok) throw new Error('Visual search failed');
      
      const results = await response.json();
      this.displayResults(results, [], 'Visual Search Results');
    } catch (error) {
      console.error('Visual search error:', error);
      this.showError('Visual search is currently unavailable');
    }
  }

  showLoading() {
    this.searchSuggestions.style.display = 'none';
    this.searchResults.style.display = 'block';
    this.searchResults.querySelector('.search-results__loading').style.display = 'flex';
    this.searchResults.querySelector('.search-results__content').style.display = 'none';
  }

  showResults() {
    this.searchResults.querySelector('.search-results__loading').style.display = 'none';
    this.searchResults.querySelector('.search-results__content').style.display = 'block';
  }

  showError(message = 'Search failed. Please try again.') {
    const resultsContent = this.searchModal.querySelector('.search-results__content');
    resultsContent.innerHTML = `
      <div class="search-error">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <p>${message}</p>
      </div>
    `;
    this.showResults();
  }

  resetSearchView() {
    this.searchSuggestions.style.display = 'block';
    this.searchResults.style.display = 'none';
  }

  saveToHistory(query) {
    if (!query || query.length < 2) return;
    
    this.searchHistory = this.searchHistory.filter(item => item !== query);
    this.searchHistory.unshift(query);
    this.searchHistory = this.searchHistory.slice(0, 5);
    
    localStorage.setItem('searchHistory', JSON.stringify(this.searchHistory));
  }

  loadSearchHistory() {
    try {
      return JSON.parse(localStorage.getItem('searchHistory') || '[]');
    } catch {
      return [];
    }
  }

  showToast(message, type = 'info') {
    // Reuse toast from main.js
    if (window.showToast) {
      window.showToast(message, type);
    }
  }
}

// Initialize advanced search
document.addEventListener('DOMContentLoaded', () => {
  window.advancedSearch = new AdvancedSearch();
});