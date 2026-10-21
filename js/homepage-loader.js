/**
 * Homepage Loader - photos start downloading as soon as this file runs.
 */

const HomepageLoader = {
    async loadHeroContent() {
        const heroSection = document.getElementById('heroSection');
        if (!heroSection) return;

        const cached = this.readHeroCache();
        if (cached) this.renderHero(cached);

        try {
            let fresh = window.__heroFast ? await window.__heroFast : null;
            if (!fresh || (!fresh.slides && !fresh.background_image)) {
                const response = await fetch('/api/public/hero-slides.php', { cache: 'no-store' });
                fresh = response.ok ? await response.json() : null;
            }
            if (fresh && ((fresh.slides && fresh.slides.length) || fresh.background_image)) {
                if (!this.sameHero(cached, fresh)) this.renderHero(fresh);
                this.writeHeroCache(fresh);
                return;
            }

            const result = await this.fetchHomepage();
            if (result && result.success && result.data && result.data.heroContent) {
                this.renderHero(result.data.heroContent);
                this.writeHeroCache(result.data.heroContent);
            }
        } catch (error) {
            console.error('Error loading hero content:', error);
        }
    },

    fetchHomepage() {
        if (!this._homepagePromise) {
            const early = window.__pageData;
            this._homepagePromise = early || fetch('/api/public/homepage-data.php', { cache: 'no-store' })
                .then(response => response.ok ? response.json() : null)
                .catch(() => null);
        }
        return this._homepagePromise;
    },

    heroDisplayUrl(url) {
        if (!url) return url;
        const value = String(url).trim();
        if (!value || value.indexOf('data:') === 0 || /^https?:/i.test(value) || value.charAt(0) === '/') {
            return value;
        }
        return '/' + value.replace(/^\.?\//, '');
    },

    preloadPhotos(urls) {
        const seen = {};
        (urls || []).forEach((url, index) => {
            const src = this.heroDisplayUrl(url);
            if (!src || seen[src] || String(src).indexOf('data:') === 0 || index > 2) return;
            seen[src] = true;
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            if (index === 0) link.setAttribute('fetchpriority', 'high');
            document.head.appendChild(link);
        });
    },

    photoElement(url, eager, fit) {
        const img = document.createElement('img');
        img.className = 'hero-slide-photo';
        img.alt = '';
        img.decoding = 'async';
        img.fetchPriority = eager ? 'high' : 'auto';
        img.dataset.full = url;
        img.src = this.heroDisplayUrl(url);
        img.onerror = function () {
            const full = img.dataset.full;
            if (full && img.src !== full) {
                try { localStorage.setItem('sjs_img_resize', '0'); } catch (error) {}
                img.onerror = null;
                img.src = full;
            }
        };
        this.applyPhotoFit(img, fit);
        return img;
    },

    readHeroCache() {
        try {
            const saved = JSON.parse(localStorage.getItem('sjs_hero_cache') || 'null');
            return saved && Array.isArray(saved.slides) ? saved : null;
        } catch (error) {
            return null;
        }
    },

    writeHeroCache(data) {
        try {
            localStorage.setItem('sjs_hero_cache', JSON.stringify({
                slides: data.slides || [],
                background_image: data.background_image || '',
                slide_mode: data.slide_mode || 'marquee',
                slide_direction: data.slide_direction || 'left',
                slide_interval_ms: data.slide_interval_ms || 5000,
                background_size: data.background_size || 'contain',
                background_position: data.background_position || 'center',
                image_scale: data.image_scale || 1,
                headline: data.headline || '',
                subheadline: data.subheadline || '',
                description: data.description || '',
                headline_color: data.headline_color || '',
                subheadline_color: data.subheadline_color || ''
            }));
        } catch (error) {}
    },

    sameHero(saved, fresh) {
        if (!saved || !fresh) return false;
        const savedSlides = (saved.slides || []).join('|');
        const freshSlides = (fresh.slides || []).join('|');
        return savedSlides === freshSlides
            && Number(saved.image_scale || 1) === Number(fresh.image_scale || 1)
            && Number(saved.slide_interval_ms || 5000) === Number(fresh.slide_interval_ms || 5000)
            && (saved.background_size || 'contain') === (fresh.background_size || 'contain')
            && (saved.background_position || 'center') === (fresh.background_position || 'center')
            && (saved.slide_direction || 'left') === (fresh.slide_direction || 'left')
            && (saved.slide_mode || 'marquee') === (fresh.slide_mode || 'marquee')
            && (saved.headline || '') === (fresh.headline || '')
            && (saved.subheadline || '') === (fresh.subheadline || '');
    },

    applyPhotoFit(img, fit) {
        if (!img || !fit) return;
        const position = {
            center: 'center center',
            top: 'center top',
            bottom: 'center bottom',
            left: 'left center',
            right: 'right center'
        }[fit.position] || 'center center';
        const objectFit = fit.size === 'contain' ? 'contain' : 'cover';
        const scale = fit.scale > 0 ? fit.scale : 1;
        img.style.objectFit = objectFit;
        img.style.objectPosition = position;
        img.style.transform = 'scale(' + scale + ')';
        img.style.transformOrigin = position;
    },
    
    renderHero(data) {
        const heroSection = document.getElementById('heroSection');
        if (!heroSection) return;
        
        const title = heroSection.querySelector('.hero-title');
        const subtitle = heroSection.querySelector('.hero-subtitle');
        const description = heroSection.querySelector('.hero-description');
        
        // Update text content
        if (title && data.headline) {
            title.textContent = data.headline;
            if (data.headline_color) {
                title.style.color = data.headline_color;
            }
        }
        
        if (subtitle && data.subheadline) {
            subtitle.textContent = data.subheadline;
            if (data.subheadline_color) {
                subtitle.style.color = data.subheadline_color;
            }
        }
        
        if (description && data.description) {
            description.textContent = data.description;
        }
        
        // Get slides
        let heroSlides = [];
        let slideList = data.slides;
        if (typeof slideList === 'string') {
            try { slideList = JSON.parse(slideList); } catch (e) { slideList = []; }
        }
        if (slideList && !Array.isArray(slideList) && typeof slideList === 'object') {
            slideList = Object.values(slideList);
        }
        if (Array.isArray(slideList) && slideList.length > 0) {
            heroSlides = slideList.map(function (slide) {
                if (typeof slide === 'string') return slide.trim();
                if (slide && typeof slide === 'object') {
                    return String(slide.url || slide.image || slide.src || slide.file_path || slide.file_url || '').trim();
                }
                return '';
            }).filter(function (url) { return url && url.indexOf('data:') !== 0; });
        } else if (typeof data.background_image === 'string' && data.background_image && data.background_image.indexOf('data:') !== 0) {
            heroSlides = [data.background_image];
        }
        
        const slideMode = data.slide_mode || 'marquee';
        const slideDirection = data.slide_direction || 'left';
        const slideInterval = Math.max(1000, parseInt(data.slide_interval_ms || 5000, 10) || 5000);
        const photoFit = {
            size: data.background_size || 'contain',
            position: data.background_position || 'center',
            scale: parseFloat(data.image_scale || 1) || 1
        };
        this.preloadPhotos(heroSlides);
        
        // Remove existing slider
        const existingSlider = heroSection.querySelector('.hero-slides-container');
        if (existingSlider) {
            existingSlider.remove();
        }
        
        // Render slider if we have slides
        if (heroSlides.length > 0) {
            const sliderContainer = document.createElement('div');
            sliderContainer.className = 'hero-slides-container';
            sliderContainer.setAttribute('data-mode', slideMode);
            sliderContainer.setAttribute('data-direction', slideDirection);
            sliderContainer.setAttribute('data-interval', slideInterval);
            sliderContainer.setAttribute('data-background-size', photoFit.size);
            sliderContainer.setAttribute('data-background-position', photoFit.position);
            sliderContainer.setAttribute('data-image-scale', photoFit.scale);
            
            if (slideMode === 'marquee') {
                // Create 3 copies for seamless loop
                const uniqueSlides = [...new Set(heroSlides)];
                const duplicatedSlides = [];
                for (let cycle = 0; cycle < 3; cycle++) {
                    uniqueSlides.forEach(slide => duplicatedSlides.push(slide));
                }
                
                const marqueeDiv = document.createElement('div');
                marqueeDiv.className = 'hero-slides-marquee';
                duplicatedSlides.forEach((slide, index) => {
                    const slideItem = document.createElement('div');
                    slideItem.className = 'hero-slide-item';
                    slideItem.appendChild(this.photoElement(slide, index < uniqueSlides.length, photoFit));
                    marqueeDiv.appendChild(slideItem);
                });
                sliderContainer.appendChild(marqueeDiv);
            } else {
                // Fade mode
                const fadeDiv = document.createElement('div');
                fadeDiv.className = 'hero-slides-fade';
                heroSlides.forEach((slide, index) => {
                    const slideItem = document.createElement('div');
                    slideItem.className = `hero-slide-item fade-slide ${index === 0 ? 'active' : ''}`;
                    slideItem.appendChild(this.photoElement(slide, index === 0, photoFit));
                    fadeDiv.appendChild(slideItem);
                });
                sliderContainer.appendChild(fadeDiv);
            }
            
            // Insert slider before overlay
            const overlay = heroSection.querySelector('.hero-overlay');
            if (overlay) {
                heroSection.insertBefore(sliderContainer, overlay);
            } else {
                heroSection.appendChild(sliderContainer);
            }
            
            // Initialize slider animation after layout so each photo gets the real hero width
            this.initSliderAnimation(sliderContainer);
            requestAnimationFrame(() => this.initSliderAnimation(sliderContainer));
        } else {
            // No slides, use background image if available
            if (data.background_image) {
                heroSection.style.backgroundImage = `url('${data.background_image.replace(/'/g, "\\'")}')`;
                heroSection.style.backgroundSize = photoFit.size === 'auto' ? 'auto' : photoFit.size;
                heroSection.style.backgroundPosition = photoFit.position;
            }
        }
    },
    
    initSliderAnimation(container) {
        const slideMode = container.dataset.mode || 'marquee';
        const slideDirection = container.dataset.direction || 'left';
        const slideInterval = parseInt(container.dataset.interval || 5000);
        
        if (this._fadeTimer) {
            clearInterval(this._fadeTimer);
            this._fadeTimer = null;
        }

        if (slideMode === 'marquee') {
            const marquee = container.querySelector('.hero-slides-marquee');
            if (marquee) {
                const slideItems = marquee.querySelectorAll('.hero-slide-item');
                const totalSlides = slideItems.length || 1;
                const uniqueCount = Math.max(1, Math.round(totalSlides / 3));
                const secondsPerPass = (uniqueCount * slideInterval) / 1000;
                const frame = Math.max(container.clientWidth || 0, container.parentElement ? container.parentElement.clientWidth : 0, 1);
                marquee.style.width = (totalSlides * frame) + 'px';
                marquee.style.animation = (slideDirection === 'right' ? 'heroMarqueeRight' : 'heroMarquee')
                    + ' ' + secondsPerPass + 's linear infinite';
                slideItems.forEach(item => {
                    item.style.flex = '0 0 ' + frame + 'px';
                    item.style.width = frame + 'px';
                });
            }
        } else {
            const slides = container.querySelectorAll('.fade-slide');
            if (slides.length > 1) {
                let currentIndex = 0;
                this._fadeTimer = setInterval(() => {
                    slides[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex + 1) % slides.length;
                    slides[currentIndex].classList.add('active');
                }, slideInterval);
            }
        }
    },
    
    async loadBranding() {
        try {
            const result = await this.fetchHomepage();
            if (result && result.success && result.data && result.data.branding) {
                this.applyBranding(result.data.branding);
            }
        } catch (error) {
            console.error('Error loading branding:', error);
        }
    },
    
    applyBranding(branding) {
        // Update page title
        if (branding.school_name) {
            document.title = `${branding.school_name} - Home`;
        }
        
        // Update logo in header (if header is loaded)
        const logoImg = document.querySelector('.logo-image');
        if (logoImg && branding.logo) {
            logoImg.src = branding.logo;
            logoImg.alt = branding.school_name || 'School Logo';
        }
        
        // Update school name in header
        const logoPlaceholder = document.querySelector('.logo-placeholder span');
        if (logoPlaceholder && branding.school_name) {
            logoPlaceholder.textContent = branding.school_name.charAt(0);
        }
        
        // Apply colors
        if (branding.primary_color) {
            document.documentElement.style.setProperty('--primary-color', branding.primary_color);
        }
        if (branding.secondary_color) {
            document.documentElement.style.setProperty('--secondary-color', branding.secondary_color);
        }
    },
    
    async loadThreeColumnSections() {
        const section = document.querySelector('.three-column-sections');
        if (!section) return;
        
        try {
            // Try API first (always fresh data)
            try {
            const result = await this.fetchHomepage();
            if (result && result.success && result.data && result.data.threeColumnSections && result.data.threeColumnSections.length > 0) {
                this.preloadPhotos(result.data.threeColumnSections.map(section => section.image).filter(Boolean));
                this.renderThreeColumnSections(result.data.threeColumnSections);
                return;
            }
            } catch (apiError) {
                console.warn('API not available for three-column sections');
            }
            
            // If API fails, keep static content
        } catch (error) {
            console.error('Error loading three-column sections:', error);
        }
    },
    
    renderThreeColumnSections(sections) {
        const grid = document.querySelector('.three-column-grid');
        if (!grid || !sections || sections.length === 0) return;
        
        // Clear existing content
        grid.innerHTML = '';
        
        // Render each section
        sections.forEach((section, index) => {
            const title = section.title || '';
            const description = section.description || '';
            const image = section.image || '';
            
            // Normalize image URL
            const imageUrl = image ? this.heroDisplayUrl(image) : '';
            
            const sectionHTML = `
                <div class="three-column-item">
                    <div class="three-column-header">
                        <h3>${this.escapeHtml(title)}</h3>
                    </div>
                    <div class="three-column-image">
                        ${imageUrl ? 
                            `<img src="${this.escapeHtml(imageUrl)}" 
                                  alt="${this.escapeHtml(title)}" 
                                  loading="lazy"
                                  decoding="async"
                                  onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'three-column-placeholder\\'><span>🖼️</span><p>Image not found</p></div>';"
                                  style="width: 100%; height: 200px; object-fit: cover;">` :
                            `<div class="three-column-placeholder">
                                <span>🖼️</span>
                                <p>No image set</p>
                            </div>`
                        }
                    </div>
                    <div class="three-column-text">
                        <p>${this.escapeHtml(description)}</p>
                    </div>
                </div>
            `;
            
            grid.innerHTML += sectionHTML;
        });
        if (typeof window.refreshScrollReveal === 'function') window.refreshScrollReveal();
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    async init() {
        // Load all content
        await Promise.all([
            this.loadBranding(),
            this.loadHeroContent(),
            this.loadThreeColumnSections()
        ]);
        
        if (!this._resizeBound) {
            this._resizeBound = true;
            let resizeTimer = 0;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    document.querySelectorAll('.hero-slides-container').forEach(el => this.initSliderAnimation(el));
                }, 120);
            });
        }

        // Listen for storage changes to update immediately
        window.addEventListener('storage', () => {
            this.loadBranding();
            this.loadHeroContent();
            this.loadThreeColumnSections();
        });
        
        // Also listen for custom event from admin panel
        window.addEventListener('adminDataSaved', () => {
            this.loadBranding();
            this.loadHeroContent();
            this.loadThreeColumnSections();
        });
    }
};

HomepageLoader.fetchHomepage();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => HomepageLoader.init());
} else {
    HomepageLoader.init();
}

// Make it globally accessible
window.HomepageLoader = HomepageLoader;
