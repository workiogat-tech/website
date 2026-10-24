/**
 * Homepage Loader - photos start downloading as soon as this file runs.
 */

const HomepageLoader = {
    async loadHeroContent() {
        const heroSection = document.getElementById('heroSection');
        if (!heroSection) return;

        try {
            let fresh = window.__heroFast ? await window.__heroFast : null;
            if (!fresh || !Array.isArray(fresh.slides)) {
                const response = await fetch('/api/public/hero-slides.php', { cache: 'no-store' });
                fresh = response.ok ? await response.json() : null;
            }
            if (fresh && Array.isArray(fresh.slides)) {
                if (!fresh.slides.length) fresh.background_image = '';
                this.renderHero(fresh);
                this.writeHeroCache(fresh);
                return;
            }

            const result = await this.fetchHomepage();
            if (result && result.success && result.data && result.data.heroContent) {
                const hero = result.data.heroContent;
                if (Array.isArray(hero.slides) && !hero.slides.length) hero.background_image = '';
                this.renderHero(hero);
                this.writeHeroCache(hero);
                return;
            }
        } catch (error) {
            console.error('Error loading hero content:', error);
        }

        const cached = this.readHeroCache();
        if (cached && Array.isArray(cached.slides) && cached.slides.length) {
            this.renderHero(cached);
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
        img.style.transform = Math.abs(scale - 1) < 0.01 ? '' : 'scale(' + scale + ')';
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
        if (Array.isArray(slideList)) {
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
        const slideInterval = Math.min(20000, Math.max(250, parseInt(data.slide_interval_ms || 5000, 10) || 5000));
        const photoFit = {
            size: data.background_size || 'contain',
            position: data.background_position || 'center',
            scale: parseFloat(data.image_scale || 1) || 1
        };
        this.preloadPhotos(heroSlides);
        
        this.clearHeroMedia(heroSection);

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
            
            heroSection.querySelectorAll('.hero-immediate-photo').forEach(function (photo) {
                photo.remove();
            });

            // Insert slider before overlay
            const overlay = heroSection.querySelector('.hero-overlay');
            if (overlay) {
                heroSection.insertBefore(sliderContainer, overlay);
            } else {
                heroSection.appendChild(sliderContainer);
            }
            
            // Initialize slider animation after layout so each photo gets the real hero width
            this.initSliderAnimation(sliderContainer);
        }
    },

    clearHeroMedia(heroSection) {
        heroSection.querySelectorAll('.hero-slides-container, .hero-immediate-photo').forEach(function (node) {
            node.remove();
        });
        heroSection.style.backgroundImage = 'none';
    },
    
    slideWidth(container, item) {
        const frameW = Math.max(container.clientWidth || 0, 1);
        const frameH = Math.max(container.clientHeight || 0, 1);
        const fit = container.dataset.backgroundSize || 'contain';
        const img = item.querySelector('img');
        if (fit === 'contain' && img && img.naturalWidth > 0 && img.naturalHeight > 0) {
            return Math.max(1, Math.round(frameH * img.naturalWidth / img.naturalHeight));
        }
        return frameW;
    },

    layoutMarquee(container, fromResize) {
        const marquee = container.querySelector('.hero-slides-marquee');
        if (!marquee) return;

        const applyWidths = () => {
            marquee.querySelectorAll('.hero-slide-item').forEach((item) => {
                const width = this.slideWidth(container, item);
                item.style.setProperty('--hero-slide-w', width + 'px');
            });
        };

        const start = () => {
            if ((container.clientWidth || 0) < 40) {
                requestAnimationFrame(start);
                return;
            }
            applyWidths();
            if (marquee.classList.contains('is-moving')) return;
            const totalSlides = marquee.querySelectorAll('.hero-slide-item').length || 3;
            const uniqueCount = Math.max(1, Math.round(totalSlides / 3));
            const slideInterval = parseInt(container.dataset.interval || '5000', 10) || 5000;
            const duration = Math.max(0.8, (uniqueCount * slideInterval) / 1000);
            container.style.setProperty('--hero-duration', duration + 's');
            marquee.classList.add('is-moving');
        };

        if (fromResize) {
            if ((container.clientWidth || 0) >= 40) applyWidths();
            return;
        }

        const pending = Array.from(marquee.querySelectorAll('img')).filter((img) => !img.complete || !img.naturalWidth);
        if (!pending.length) {
            start();
            return;
        }

        let left = pending.length;
        let began = false;
        const done = () => {
            left -= 1;
            if (left <= 0 && !began) {
                began = true;
                start();
            }
        };
        pending.forEach((img) => {
            img.addEventListener('load', done, { once: true });
            img.addEventListener('error', done, { once: true });
        });
        setTimeout(() => {
            if (!began) {
                began = true;
                start();
            }
        }, 800);
    },

    initSliderAnimation(container) {
        const slideMode = container.dataset.mode || 'marquee';
        const slideInterval = parseInt(container.dataset.interval || 5000);
        
        if (this._fadeTimer) {
            clearInterval(this._fadeTimer);
            this._fadeTimer = null;
        }

        if (slideMode === 'marquee') {
            this.layoutMarquee(container, false);
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
        const sectionWrap = document.getElementById('home-columns');
        if (!grid || !sections || sections.length === 0) return;
        if (sectionWrap) sectionWrap.hidden = false;
        
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

    filled(value) {
        return value !== undefined && value !== null && String(value).trim() !== '';
    },

    setText(id, value) {
        const node = document.getElementById(id);
        if (!node) return;
        node.textContent = this.filled(value) ? String(value) : '';
        node.hidden = !this.filled(value);
    },

    setPhoto(id, url, alt) {
        const node = document.getElementById(id);
        if (!node) return;
        const src = this.filled(url) ? this.heroDisplayUrl(url) : '';
        if (!src) {
            node.hidden = true;
            node.innerHTML = '';
            return;
        }
        node.hidden = false;
        const img = document.createElement('img');
        img.src = src;
        img.alt = alt || '';
        img.loading = 'lazy';
        img.decoding = 'async';
        node.replaceChildren(img);
    },

    async loadHomeSections() {
        await this.loadThreeColumnSections();
        try {
            const result = await this.fetchHomepage();
            const data = result && result.success ? result.data : null;
            if (!data) return;
            this.renderAcademics(data.academicsContent);
            this.renderVision(data.visionMissionContent, data.coreValues);
            this.renderWhyChoose(data.whyChooseContent, data.whyChoosePoints);
            this.renderCustomSection(data.customSectionContent);
            if (typeof window.refreshScrollReveal === 'function') window.refreshScrollReveal();
        } catch (error) {
            console.error('Error loading homepage sections:', error);
        }
    },

    renderAcademics(content) {
        const section = document.getElementById('home-academics');
        if (!section) return;
        const item = content || {};
        const visible = ['title', 'description', 'motto', 'student_count', 'success_rate', 'image', 'button_text']
            .some((key) => this.filled(item[key]));
        section.hidden = !visible;
        if (!visible) return;
        this.setText('academicsTitle', item.title);
        this.setText('academicsDescription', item.description);
        this.setText('academicsMotto', item.motto);
        const stats = document.getElementById('academicsStats');
        if (stats) {
            stats.innerHTML = '';
            [['student_count', 'Students'], ['success_rate', 'Success Rate']].forEach((pair) => {
                if (!this.filled(item[pair[0]])) return;
                const stat = document.createElement('div');
                stat.className = 'stat-item';
                const number = document.createElement('div');
                number.className = 'stat-number';
                number.textContent = item[pair[0]];
                const label = document.createElement('div');
                label.className = 'stat-label';
                label.textContent = pair[1];
                stat.append(number, label);
                stats.appendChild(stat);
            });
            stats.hidden = !stats.childElementCount;
        }
        const button = document.getElementById('academicsButton');
        if (button) {
            const label = this.filled(item.button_text) ? item.button_text : '';
            const link = this.filled(item.curriculum_link) ? item.curriculum_link : '';
            button.hidden = !label;
            button.textContent = label;
            button.href = link || 'curriculum.html';
        }
        this.setPhoto('academicsImage', item.image, item.title);
    },

    renderVision(content, values) {
        const section = document.getElementById('home-vision');
        const cards = document.getElementById('visionMissionCards');
        if (!section || !cards) return;
        const item = content || {};
        const list = Array.isArray(values) ? values.filter((value) => this.filled(value.value_name || value.name)) : [];
        const hasCards = this.filled(item.vision_text) || this.filled(item.mission_text) || this.filled(item.vision_title) || this.filled(item.mission_title);
        section.hidden = !hasCards && !list.length;
        cards.innerHTML = '';
        if (this.filled(item.vision_title) || this.filled(item.vision_text)) {
            const card = document.createElement('div');
            card.className = 'vision-card';
            const title = document.createElement('h2');
            title.className = 'vision-mission-title';
            title.textContent = item.vision_title || '';
            const text = document.createElement('p');
            text.className = 'vision-mission-text';
            text.textContent = item.vision_text || '';
            card.append(title, text);
            cards.appendChild(card);
        }
        if (this.filled(item.mission_title) || this.filled(item.mission_text)) {
            const card = document.createElement('div');
            card.className = 'mission-card';
            const title = document.createElement('h2');
            title.className = 'vision-mission-title';
            title.textContent = item.mission_title || '';
            const text = document.createElement('p');
            text.className = 'vision-mission-text';
            text.textContent = item.mission_text || '';
            card.append(title, text);
            cards.appendChild(card);
        }
        const valuesWrap = document.getElementById('home-values');
        const valuesTitle = document.getElementById('valuesTitle');
        const grid = document.getElementById('valuesGrid');
        if (valuesWrap && valuesTitle && grid) {
            valuesWrap.hidden = !list.length;
            valuesTitle.textContent = item.core_values_title || '';
            valuesTitle.hidden = !this.filled(item.core_values_title);
            grid.innerHTML = '';
            list.forEach((value) => {
                const card = document.createElement('div');
                card.className = 'value-card-item';
                card.style.background = '#f3f4f6';
                card.textContent = value.value_name || value.name;
                grid.appendChild(card);
            });
        }
    },

    renderWhyChoose(content, points) {
        const section = document.getElementById('home-why');
        const list = document.getElementById('whyPoints');
        if (!section || !list) return;
        const item = content || {};
        const rows = Array.isArray(points) ? points.map((point) => {
            if (typeof point === 'string') return point;
            return point.point_text || point.text || '';
        }).filter((point) => this.filled(point)) : [];
        const visible = this.filled(item.title) || this.filled(item.image) || rows.length > 0;
        section.hidden = !visible;
        this.setText('whyTitle', item.title);
        list.innerHTML = '';
        rows.forEach((point) => {
            const row = document.createElement('div');
            row.className = 'point-item';
            const dot = document.createElement('div');
            dot.className = 'point-dot';
            const text = document.createElement('p');
            text.textContent = point;
            row.append(dot, text);
            list.appendChild(row);
        });
        this.setPhoto('whyImage', item.image, item.title);
    },

    renderCustomSection(content) {
        const section = document.getElementById('home-custom');
        if (!section) return;
        const item = content || {};
        const visible = this.filled(item.title) || this.filled(item.description) || this.filled(item.image);
        section.hidden = !visible;
        this.setText('customTitle', item.title);
        this.setText('customDescription', item.description);
        this.setPhoto('customImage', item.image, item.title);
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
            this.loadHomeSections()
        ]);
        
        if (!this._resizeBound) {
            this._resizeBound = true;
            let resizeTimer = 0;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    document.querySelectorAll('.hero-slides-container').forEach((el) => {
                        if ((el.dataset.mode || 'marquee') === 'marquee') this.layoutMarquee(el, true);
                    });
                }, 120);
            });
        }

        // Listen for storage changes to update immediately
        window.addEventListener('storage', () => {
            this.loadBranding();
            this.loadHeroContent();
            this.loadHomeSections();
        });
        
        // Also listen for custom event from admin panel
        window.addEventListener('adminDataSaved', () => {
            this.loadBranding();
            this.loadHeroContent();
            this.loadHomeSections();
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
