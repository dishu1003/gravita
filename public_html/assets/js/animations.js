// Advanced Animations and Micro-interactions with GSAP
// Note: This assumes GSAP is loaded from CDN in the HTML

document.addEventListener('DOMContentLoaded', () => {
  // Register GSAP plugins
  if (typeof gsap !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger, TextPlugin, CustomEase);
    
    // Initialize animations
    initHeroAnimations();
    initProductAnimations();
    initScrollAnimations();
    initHoverEffects();
    initPageTransitions();
    initParallaxEffects();
    initMagneticButtons();
    initCursorFollower();
  }
});

// Hero Section Animations
function initHeroAnimations() {
  const hero = document.querySelector('.hero');
  if (!hero) return;

  // Split text for animation
  const heroTitle = hero.querySelector('.hero__title');
  const heroSubtitle = hero.querySelector('.hero__subtitle');
  
  if (heroTitle) {
    const words = heroTitle.textContent.split(' ');
    heroTitle.innerHTML = words.map(word => 
      `<span class="hero-word"><span class="hero-word-inner">${word}</span></span>`
    ).join(' ');
  }

  // Hero timeline
  const heroTl = gsap.timeline({
    defaults: { ease: 'power3.out' }
  });

  heroTl
    .fromTo('.hero__media', 
      { scale: 1.2, opacity: 0 },
      { scale: 1, opacity: 1, duration: 1.5 }
    )
    .fromTo('.hero__overlay',
      { opacity: 0 },
      { opacity: 0.4, duration: 1 },
      '-=1'
    )
    .fromTo('.hero-word-inner',
      { y: '100%', opacity: 0 },
      { 
        y: '0%', 
        opacity: 1, 
        duration: 0.8,
        stagger: 0.1,
        ease: 'power3.out'
      },
      '-=0.5'
    )
    .fromTo(heroSubtitle,
      { y: 30, opacity: 0 },
      { y: 0, opacity: 1, duration: 0.8 },
      '-=0.4'
    )
    .fromTo('.hero__cta .btn',
      { y: 20, opacity: 0 },
      { 
        y: 0, 
        opacity: 1, 
        duration: 0.6,
        stagger: 0.2
      },
      '-=0.3'
    );

  // Floating animation for hero elements
  gsap.to('.hero__content', {
    y: -20,
    duration: 3,
    repeat: -1,
    yoyo: true,
    ease: 'power1.inOut'
  });
}

// Product Card Animations
function initProductAnimations() {
  const productCards = gsap.utils.toArray('.product-card');
  
  productCards.forEach((card, index) => {
    // Entrance animation
    gsap.fromTo(card,
      {
        y: 60,
        opacity: 0,
        scale: 0.9
      },
      {
        scrollTrigger: {
          trigger: card,
          start: 'top 85%',
          toggleActions: 'play none none reverse'
        },
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 0.6,
        delay: index * 0.1,
        ease: 'power2.out'
      }
    );

    // Hover animation
    const img = card.querySelector('.product-card__img img');
    const body = card.querySelector('.product-card__body');
    
    card.addEventListener('mouseenter', () => {
      gsap.to(img, {
        scale: 1.1,
        duration: 0.3,
        ease: 'power2.out'
      });
      
      gsap.to(body, {
        y: -5,
        duration: 0.3,
        ease: 'power2.out'
      });
    });
    
    card.addEventListener('mouseleave', () => {
      gsap.to(img, {
        scale: 1,
        duration: 0.3,
        ease: 'power2.out'
      });
      
      gsap.to(body, {
        y: 0,
        duration: 0.3,
        ease: 'power2.out'
      });
    });
  });
}

// Scroll-triggered Animations
function initScrollAnimations() {
  // Animate sections on scroll
  gsap.utils.toArray('.section').forEach(section => {
    const title = section.querySelector('.section__title');
    const subtitle = section.querySelector('.section__subtitle');
    
    if (title) {
      gsap.fromTo(title,
        { y: 40, opacity: 0 },
        {
          scrollTrigger: {
            trigger: section,
            start: 'top 80%'
          },
          y: 0,
          opacity: 1,
          duration: 0.8,
          ease: 'power3.out'
        }
      );
    }
    
    if (subtitle) {
      gsap.fromTo(subtitle,
        { y: 30, opacity: 0 },
        {
          scrollTrigger: {
            trigger: section,
            start: 'top 80%'
          },
          y: 0,
          opacity: 1,
          duration: 0.8,
          delay: 0.2,
          ease: 'power3.out'
        }
      );
    }
  });

  // Feature callouts animation
  gsap.utils.toArray('.callout').forEach((callout, index) => {
    gsap.fromTo(callout,
      {
        y: 50,
        opacity: 0,
        rotateY: -15
      },
      {
        scrollTrigger: {
          trigger: '.feature-callouts',
          start: 'top 75%'
        },
        y: 0,
        opacity: 1,
        rotateY: 0,
        duration: 0.7,
        delay: index * 0.15,
        ease: 'power2.out'
      }
    );
  });

  // Progress indicator
  gsap.to('progress', {
    value: 100,
    ease: 'none',
    scrollTrigger: {
      scrub: 0.3
    }
  });
}

// Advanced Hover Effects
function initHoverEffects() {
  // Magnetic effect for buttons
  document.querySelectorAll('.btn').forEach(btn => {
    const magnetic = 0.2;
    
    btn.addEventListener('mousemove', (e) => {
      const rect = btn.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      
      gsap.to(btn, {
        x: x * magnetic,
        y: y * magnetic,
        duration: 0.3,
        ease: 'power2.out'
      });
    });
    
    btn.addEventListener('mouseleave', () => {
      gsap.to(btn, {
        x: 0,
        y: 0,
        duration: 0.3,
        ease: 'elastic.out(1, 0.3)'
      });
    });
  });

  // 3D tilt effect for product cards
  document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;
      
      gsap.to(card, {
        rotateY: (x - 0.5) * 10,
        rotateX: (0.5 - y) * 10,
        duration: 0.5,
        ease: 'power2.out',
        transformPerspective: 1000
      });
    });
    
    card.addEventListener('mouseleave', () => {
      gsap.to(card, {
        rotateY: 0,
        rotateX: 0,
        duration: 0.5,
        ease: 'power2.out'
      });
    });
  });
}

// Page Transitions
function initPageTransitions() {
  // Create page transition overlay
  const transitionOverlay = document.createElement('div');
  transitionOverlay.className = 'page-transition';
  transitionOverlay.innerHTML = `
    <div class="transition-logo">
      <svg class="transition-icon" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 6v6l4 2"/>
      </svg>
    </div>
  `;
  document.body.appendChild(transitionOverlay);

  // Animate links
  document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      if (href && href.startsWith('/') && !href.includes('#')) {
        e.preventDefault();
        
        gsap.timeline()
          .to(transitionOverlay, {
            opacity: 1,
            pointerEvents: 'all',
            duration: 0.3
          })
          .to('.transition-icon', {
            rotation: 360,
            scale: 1.2,
            duration: 0.5,
            ease: 'power2.inOut'
          })
          .call(() => {
            window.location.href = href;
          });
      }
    });
  });
}

// Parallax Effects
function initParallaxEffects() {
  // Hero parallax
  gsap.to('.hero__media', {
    yPercent: 50,
    ease: 'none',
    scrollTrigger: {
      trigger: '.hero',
      start: 'top top',
      end: 'bottom top',
      scrub: true
    }
  });

  // Section backgrounds parallax
  document.querySelectorAll('[data-parallax]').forEach(element => {
    const speed = element.dataset.parallax || 0.5;
    
    gsap.to(element, {
      yPercent: -50 * speed,
      ease: 'none',
      scrollTrigger: {
        trigger: element,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
      }
    });
  });
}

// Magnetic Buttons Enhanced
function initMagneticButtons() {
  document.querySelectorAll('[data-magnetic]').forEach(element => {
    const strength = element.dataset.magnetic || 0.5;
    
    element.addEventListener('mousemove', (e) => {
      const rect = element.getBoundingClientRect();
      const relX = e.clientX - rect.left - rect.width / 2;
      const relY = e.clientY - rect.top - rect.height / 2;
      
      gsap.to(element, {
        x: relX * strength,
        y: relY * strength,
        duration: 0.3,
        ease: 'power2.out'
      });
      
      // Inner text opposite movement
      const inner = element.querySelector('span');
      if (inner) {
        gsap.to(inner, {
          x: -relX * strength * 0.5,
          y: -relY * strength * 0.5,
          duration: 0.3,
          ease: 'power2.out'
        });
      }
    });
    
    element.addEventListener('mouseleave', () => {
      gsap.to(element, {
        x: 0,
        y: 0,
        duration: 0.5,
        ease: 'elastic.out(1, 0.3)'
      });
      
      const inner = element.querySelector('span');
      if (inner) {
        gsap.to(inner, {
          x: 0,
          y: 0,
          duration: 0.5,
          ease: 'elastic.out(1, 0.3)'
        });
      }
    });
  });
}

// Custom Cursor
function initCursorFollower() {
  if ('ontouchstart' in window) return; // Skip on touch devices
  
  const cursor = document.createElement('div');
  cursor.className = 'custom-cursor';
  cursor.innerHTML = '<div class="cursor-dot"></div><div class="cursor-outline"></div>';
  document.body.appendChild(cursor);
  
  const dot = cursor.querySelector('.cursor-dot');
  const outline = cursor.querySelector('.cursor-outline');
  
  let mouseX = 0;
  let mouseY = 0;
  let dotX = 0;
  let dotY = 0;
  let outlineX = 0;
  let outlineY = 0;
  
  // Update mouse position
  document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
  });
  
  // Animate cursor
  gsap.ticker.add(() => {
    // Dot follows immediately
    dotX += (mouseX - dotX) * 0.9;
    dotY += (mouseY - dotY) * 0.9;
    gsap.set(dot, { x: dotX, y: dotY });
    
    // Outline follows with delay
    outlineX += (mouseX - outlineX) * 0.1;
    outlineY += (mouseY - outlineY) * 0.1;
    gsap.set(outline, { x: outlineX - 20, y: outlineY - 20 });
  });
  
  // Cursor interactions
  const interactiveElements = document.querySelectorAll('a, button, [data-cursor]');
  
  interactiveElements.forEach(el => {
    el.addEventListener('mouseenter', () => {
      cursor.classList.add('cursor-hover');
      
      const cursorType = el.dataset.cursor;
      if (cursorType) {
        cursor.classList.add(`cursor-${cursorType}`);
      }
    });
    
    el.addEventListener('mouseleave', () => {
      cursor.classList.remove('cursor-hover');
      cursor.className = cursor.className.replace(/cursor-\w+/g, '').trim();
    });
  });
  
  // Hide/show cursor
  document.addEventListener('mouseenter', () => cursor.style.opacity = '1');
  document.addEventListener('mouseleave', () => cursor.style.opacity = '0');
}

// Utility function for splitting text
function splitText(element) {
  const text = element.textContent;
  const chars = text.split('');
  element.innerHTML = chars.map(char => 
    `<span class="char">${char === ' ' ? '&nbsp;' : char}</span>`
  ).join('');
  return element.querySelectorAll('.char');
}