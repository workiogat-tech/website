/**
 * Homepage Loader - photos start downloading as soon as this file runs.
 */

const HomepageLoader = {
    async loadHeroContent() {
        const heroSection = document.getElementById('heroSection');
        if (!heroSection) return;
        
        try {
            const result = await this.fetchHomepage();
            if (result && result.success && result.data && result.data.heroContent) {
                this.renderHero(result.data.heroContent);
                return;
            }

            if (typeof window.ClientStorage !== 'undefined') {
                const heroData = await window.ClientStorage.hero.get();
                const hasImage = heroData && (heroData.background_image || (heroData.slides && heroData.slides.length));
                if (hasImage) {
                    this.renderHero(heroData);
                }
            }
        } catch (error) {
            console.error('Error loading hero content:', error);
        }
    },

    fetchHomepage() {
        if (!this._homepagePromise) {
            const early = window.__pageData;
            this._homepagePromise = early || fetch('/api/public/homepage-data.php')
                .then(response => response.ok ? response.json() : null)
                .catch(() => null);
        }
        return this._homepagePromise;
    },

    preloadPhotos(urls) {
        (urls || []).forEach((url, index) => {
            if (!url || String(url).indexOf('data:') === 0) return;
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = url;
            if (index === 0) link.setAttribute('fetchpriority', 'high');
            document.head.appendChild(link);
        });
    },

    photoElement(url, eager) {
        const img = document.createElement('img');
        img.className = 'hero-slide-photo';
        img.alt = '';
        img.decoding = 'async';
        img.fetchPriority = eager ? 'high' : 'low';
        img.src = url;
        return img;
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
        const slideInterval = parseInt(data.slide_interval_ms || 5000);
        const backgroundSize = data.background_size || 'contain';
        const backgroundPosition = data.background_position || 'center';
        const imageScale = parseFloat(data.image_scale || 1) || 1;
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
            sliderContainer.setAttribute('data-background-size', backgroundSize);
            sliderContainer.setAttribute('data-background-position', backgroundPosition);
            sliderContainer.setAttribute('data-image-scale', imageScale);
            
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
                    slideItem.appendChild(this.photoElement(slide, index < uniqueSlides.length));
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
                    slideItem.appendChild(this.photoElement(slide, index === 0));
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
            
            // Initialize slider animation
            this.initSliderAnimation(sliderContainer);
        } else {
            // No slides, use background image if available
            if (data.background_image) {
                heroSection.style.backgroundImage = `url('${data.background_image.replace(/'/g, "\\'")}')`;
                heroSection.style.backgroundSize = backgroundSize;
                heroSection.style.backgroundPosition = backgroundPosition;
            }
        }
    },
    
    initSliderAnimation(container) {
        const slideMode = container.dataset.mode || 'marquee';
        const slideDirection = container.dataset.direction || 'left';
        const slideInterval = parseInt(container.dataset.interval || 5000);
        
        if (slideMode === 'marquee') {
            // Marquee animation is handled by CSS
            const marquee = container.querySelector('.hero-slides-marquee');
            if (marquee) {
                const direction = slideDirection === 'right' ? 'reverse' : 'normal';
                marquee.style.animationDirection = direction;
                // Calculate duration: total slides (3 copies) * interval per slide
                const slideItems = marquee.querySelectorAll('.hero-slide-item');
                const totalSlides = slideItems.length;
                const totalDuration = (totalSlides * slideInterval) / 1000; // Convert to seconds
                marquee.style.animationDuration = `${totalDuration}s`;
                
                // Set width for marquee container based on slide count
                const slideWidth = 100 / totalSlides;
                slideItems.forEach(item => {
                    item.style.width = `${slideWidth}%`;
                });
            }
        } else {
            // Fade mode - cycle through slides
            const slides = container.querySelectorAll('.fade-slide');
            if (slides.length > 1) {
                let currentIndex = 0;
                setInterval(() => {
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
            let imageUrl = image;
            if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                imageUrl = '/uploads/' + imageUrl.replace(/^.*[\\\/]/, '');
            }
            
            const sectionHTML = `
                <div class="three-column-item">
                    <div class="three-column-header">
                        <h3>${this.escapeHtml(title)}</h3>
                    </div>
                    <div class="three-column-image">
                        ${imageUrl ? 
                            `<img src="${this.escapeHtml(imageUrl)}" 
                                  alt="${this.escapeHtml(title)}" 
                                  fetchpriority="high"
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
