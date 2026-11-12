/**
 * Page Data Loader - Fetches data from PHP APIs and provides fallbacks
 * Matches the exact structure from PHP files
 */

const PageDataLoader = {
    baseURL: '/api/content',

    animateCount(node, raw) {
        if (!node) return;
        const text = String(raw == null ? node.textContent : raw).trim();
        const match = text.match(/^(\D*?)(\d[\d,]*(?:\.\d+)?)(.*)$/);
        if (!match) {
            node.textContent = text;
            return;
        }
        const prefix = match[1];
        const digits = match[2].replace(/,/g, '');
        const suffix = match[3];
        const target = Number(digits);
        if (!Number.isFinite(target)) {
            node.textContent = text;
            return;
        }
        const decimals = (digits.split('.')[1] || '').length;
        const grouped = match[2].indexOf(',') !== -1;
        const format = (value) => {
            const fixed = decimals ? value.toFixed(decimals) : String(Math.round(value));
            const parts = fixed.split('.');
            if (grouped) parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return prefix + parts.join('.') + suffix;
        };
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const run = () => {
            if (node.dataset.counted === '1') return;
            node.dataset.counted = '1';
            if (reduced) {
                node.textContent = text;
                return;
            }
            const duration = 1600;
            const begun = performance.now();
            const step = (now) => {
                const progress = Math.min(1, (now - begun) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);
                node.textContent = progress >= 1 ? text : format(target * eased);
                if (progress < 1) requestAnimationFrame(step);
            };
            node.textContent = format(0);
            requestAnimationFrame(step);
        };
        node.textContent = format(0);
        if (!('IntersectionObserver' in window)) {
            run();
            return;
        }
        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            observer.disconnect();
            run();
        }, { threshold: 0.45 });
        observer.observe(node);
    },
    
    async loadDb(key) {
        const endpoints = {
            about: 'about.php',
            clubs: 'clubs.php',
            curriculum: 'curriculum.php',
            facilities: 'facilities-settings.php',
            teachers: 'teachers-settings.php',
            testimonials: 'testimonials-settings.php',
            gallery: 'gallery-settings.php',
            activities: 'activities-settings.php',
            news: 'news-settings.php',
            announcements: 'announcements-settings.php',
            admission: 'admission.php',
            background: 'school-background.php',
            sponsorship: 'sponsorship.php',
            giveback: 'giveback.php',
            parents: 'parents.php',
            'people-behind-school': 'people-behind-school.php',
            resources: 'resources.php'
        };
        const lists = {
            facilities: 'facilities.php',
            teachers: 'teachers.php',
            testimonials: 'testimonials.php',
            activities: 'activities.php',
            news: 'news.php',
            announcements: 'announcements.php'
        };
        const read = async (path) => {
            if (window.__pagePath === path && window.__pageData) {
                return window.__pageData;
            }
            const response = await fetch(this.baseURL + '/' + path, { cache: 'no-store' });
            if (!response.ok) return null;
            return response.json();
        };
        try {
            const listPath = lists[key];
            const row = await read(endpoints[key] || (key + '.php'));
            let settings = (row && !row.error && !Array.isArray(row)) ? row : {};
            if (settings.data && typeof settings.data === 'object' && !Array.isArray(settings.data)) {
                settings = Object.assign({}, settings, settings.data);
            }
            Object.keys(settings).forEach((name) => {
                const value = settings[name];
                if (typeof value !== 'string') return;
                const trimmed = value.trim();
                if (!trimmed || (trimmed.charAt(0) !== '[' && trimmed.charAt(0) !== '{')) return;
                try { settings[name] = JSON.parse(trimmed); } catch (parseError) {}
            });
            if (listPath && !Array.isArray(settings[key])) {
                const list = await read(listPath);
                if (list && Array.isArray(list.data)) settings[key] = list.data;
                else if (list && Array.isArray(list[key])) settings[key] = list[key];
            }
            return settings;
        } catch (error) {
            return {};
        }
    },

    preload(url) {
        if (!url || String(url).indexOf('data:') === 0) return;
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = url;
        link.setAttribute('fetchpriority', 'high');
        document.head.appendChild(link);
    },

    paintHero(url) {
        if (!url || String(url).indexOf('data:') === 0) return;
        url = String(url).trim();
        if (!/^https?:/i.test(url) && url.charAt(0) !== '/') {
            url = '/' + url.replace(/^\.?\//, '');
        }
        this.preload(url);
        const hero = document.getElementById('page-hero') || document.querySelector('.page-hero-section, .admission-hero-section');
        const bg = (hero && hero.querySelector('.page-hero-background, .admission-hero-background'))
            || document.querySelector('.page-hero-background, .admission-hero-background');
        if (!bg) return;
        let img = bg.querySelector('img.page-hero-photo');
        if (!img) {
            img = document.createElement('img');
            img.className = 'page-hero-photo';
            img.alt = '';
            img.decoding = 'async';
            img.fetchPriority = 'high';
            bg.appendChild(img);
        }
        if (img.getAttribute('src') !== url) img.src = url;
    },

    // Helper to initialize MediaStorage
    initMediaStorage() {
        if (typeof window.MediaStorage === 'undefined') {
            window.MediaStorage = {
                loadMedia() {
                    try {
                        if (localStorage.getItem('media_photos_cleared') !== '1') {
                            localStorage.removeItem('media_items');
                            localStorage.setItem('media_photos_cleared', '1');
                            return [];
                        }
                        const stored = localStorage.getItem('media_items');
                        if (!stored || stored.length > 200000 || stored.indexOf('data:') !== -1) {
                            if (stored) localStorage.removeItem('media_items');
                            return [];
                        }
                        const parsed = JSON.parse(stored);
                        return Array.isArray(parsed) ? parsed : [];
                    } catch (e) {
                        try { localStorage.removeItem('media_items'); } catch (ignore) {}
                        return [];
                    }
                },
                loadSections() {
                    try {
                        const stored = localStorage.getItem('media_sections');
                        return stored ? JSON.parse(stored) : [];
                    } catch (e) {
                        return [];
                    }
                },
                loadSettings(key) {
                    try {
                        const stored = localStorage.getItem('admin_settings_' + key);
                        return stored ? JSON.parse(stored) : {};
                    } catch (e) {
                        return {};
                    }
                }
            };
        }
    },
    
    // Load about page data
    async loadAboutData() {
        try {
            const settings = await this.loadDb('about');
            const defaultData = this.getDefaultAboutData();
            const saved = settings && settings.id;
            const asList = (value) => {
                if (!value) return null;
                if (typeof value === 'string') {
                    try { value = JSON.parse(value); } catch (e) { return null; }
                }
                return Array.isArray(value) ? value : null;
            };
            const text = (value, fallback) => (value === undefined || value === null || value === '') ? fallback : value;

            if (!saved) {
                return defaultData;
            }

            const points = asList(settings.why_choose_points) || [];
            [settings.hero_image, settings.image, settings.about_image_2, settings.why_choose_image].forEach((url) => this.preload(url));
            return {
                aboutContent: {
                    ...defaultData.aboutContent,
                    title: text(settings.title, ''),
                    content: text(settings.content, ''),
                    hero_caption: text(settings.hero_caption, ''),
                    image: settings.image || '',
                    hero_image: settings.hero_image || '',
                    about_image_2: settings.about_image_2 || '',
                    mission_title: text(settings.mission_title, ''),
                    mission_content: [settings.mission_content, settings.mission_paragraph2].filter(Boolean).join('\n\n'),
                    core_values: asList(settings.core_values) || [],
                    statistics: asList(settings.statistics) || []
                },
                visionMissionContent: {
                    vision_text: text(settings.vision_text, '')
                },
                whyChooseContent: {
                    title: text(settings.why_choose_title, ''),
                    image: settings.why_choose_image || ''
                },
                whyChoosePoints: points.map((point) => typeof point === 'string' ? point : (point.point_text || '')).filter(Boolean)
            };
        } catch (error) {
            console.warn('Using default about data:', error);
            return this.getDefaultAboutData();
        }
    },
    
    getDefaultAboutData() {
        return {
            aboutContent: {
                title: '', content: '', mission_title: '', mission_content: '',
                hero_caption: '', image: '', about_image_2: '', hero_image: '',
                core_values: [], statistics: []
            },
            visionMissionContent: { vision_text: '' },
            whyChooseContent: { title: '', image: '' },
            whyChoosePoints: []
        };
    },
    
    asList(value) {
        if (Array.isArray(value)) return value;
        if (typeof value === 'string') {
            try {
                const parsed = JSON.parse(value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (parseError) {
                return [];
            }
        }
        return [];
    },

    pageContent(settings, nestedName) {
        const source = settings && typeof settings === 'object' ? settings : {};
        const nested = source[nestedName];
        if (nested && typeof nested === 'object' && !Array.isArray(nested)) {
            return Object.assign({}, source, nested);
        }
        return source;
    },

    // Load clubs data
    async loadClubsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('clubs');
            const defaultData = this.getDefaultClubsData();
            const content = this.pageContent(settings, 'clubsContent');
            
            return {
                clubsContent: {
                    ...defaultData.clubsContent,
                    ...content,
                    hero_image: content.hero_image || ''
                },
                clubs: this.asList(settings.clubs)
            };
        } catch (error) {
            return this.getDefaultClubsData();
        }
    },
    
    getDefaultClubsData() {
        return {
            clubsContent: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            clubs: []
        };
    },
    
    // Load curriculum data
    async loadCurriculumData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('curriculum');
            const defaultData = this.getDefaultCurriculumData();
            
            const content = this.pageContent(settings.basicContent || settings, 'curriculumContent');
            const programs = this.asList(settings.programs || settings.curriculumPrograms);
            const whyChoose = settings.whyChoose || settings.curriculumWhyChoose || {};
            const whyItems = this.asList(whyChoose.items || settings.curriculumWhyChooseItems);
            return {
                curriculumContent: {
                    ...defaultData.curriculumContent,
                    ...content,
                    hero_image: content.hero_image || ''
                },
                curriculumPrograms: programs,
                curriculumWhyChoose: {
                    ...defaultData.curriculumWhyChoose,
                    ...whyChoose
                },
                curriculumWhyChooseItems: whyItems
            };
        } catch (error) {
            return this.getDefaultCurriculumData();
        }
    },
    
    getDefaultCurriculumData() {
        return {
            curriculumContent: { page_title: '', page_subtitle: '', intro_text: '', hero_image: '' },
            curriculumPrograms: [],
            curriculumWhyChoose: { section_title: '' },
            curriculumWhyChooseItems: []
        };
    },
    
    // Load facilities data
    async loadFacilitiesData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('facilities');
            const defaultData = this.getDefaultFacilitiesData();
            
            return {
                facilitiesSettings: {
                    ...defaultData.facilitiesSettings,
                    ...this.pageContent(settings, 'facilitiesSettings'),
                    hero_image: settings.hero_image || ''
                },
                facilities: this.asList(settings.facilities),
                features: this.asList(settings.features)
            };
        } catch (error) {
            return this.getDefaultFacilitiesData();
        }
    },
    
    getDefaultFacilitiesData() {
        return {
            facilitiesSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            facilities: [],
            features: []
        };
    },
    
    // Load teachers data
    async loadTeachersData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('teachers');
            const defaultData = this.getDefaultTeachersData();
            
            return {
                teachersSettings: {
                    ...defaultData.teachersSettings,
                    ...this.pageContent(settings, 'teachersSettings'),
                    hero_image: settings.hero_image || ''
                },
                teachers: this.asList(settings.teachers).length ? this.asList(settings.teachers) : this.asList(settings.people_json)
            };
        } catch (error) {
            return this.getDefaultTeachersData();
        }
    },
    
    getDefaultTeachersData() {
        return {
            teachersSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            teachers: []
        };
    },
    
    // Load testimonials data
    async loadTestimonialsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('testimonials');
            const defaultData = this.getDefaultTestimonialsData();
            
            return {
                testimonialsSettings: {
                    ...defaultData.testimonialsSettings,
                    ...this.pageContent(settings, 'testimonialsSettings'),
                    hero_image: settings.hero_image || ''
                },
                testimonials: this.asList(settings.testimonials)
            };
        } catch (error) {
            return this.getDefaultTestimonialsData();
        }
    },
    
    getDefaultTestimonialsData() {
        return {
            testimonialsSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            testimonials: []
        };
    },
    
    // Load gallery data
    async loadGalleryData() {
        try {
            this.initMediaStorage();
            
            // Load gallery settings from localStorage
            const gallerySettings = await this.loadDb('gallery');
            
            // Load gallery images from localStorage
            const allMedia = window.MediaStorage.loadMedia();
            const sections = window.MediaStorage.loadSections();
            
            // Find Gallery section
            const gallerySection = sections.find(s => 
                s.name && s.name.toLowerCase() === 'gallery'
            );
            
            let galleryImages = [];
            if (gallerySection) {
                // Filter images by gallery section
                galleryImages = allMedia.filter(item => 
                    item.section_id == gallerySection.id || 
                    (item.section_name && item.section_name.toLowerCase() === 'gallery')
                );
            } else {
                // If no gallery section, show all images
                galleryImages = allMedia;
            }
            
            // Return data in the expected format
            return {
                gallerySettings: {
                    title: gallerySettings.title || 'School Gallery',
                    subtitle: gallerySettings.subtitle || 'Photos and Memories',
                    hero_image: gallerySettings.hero_image || ''
                },
                galleryImages: galleryImages.map(img => {
                    // Prioritize data URLs (base64) from localStorage, then file paths
                    const imageUrl = img.data || img.file_path || img.file_url || img.filename || '';
                    return {
                        file_path: imageUrl,
                        file_url: imageUrl,
                        filename: img.filename || img.original_name || '',
                        original_name: img.original_name || img.filename || 'Gallery Image'
                    };
                }).filter(img => img.file_path && img.file_path.trim())
            };
        } catch (error) {
            console.warn('Error loading gallery data from localStorage:', error);
            return this.getDefaultGalleryData();
        }
    },
    
    getDefaultGalleryData() {
        return {
            gallerySettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            galleryImages: []
        };
    },
    
    // Load events data
    async loadEventsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('activities');
            const defaultData = this.getDefaultEventsData();
            
            return {
                activitiesSettings: {
                    ...defaultData.activitiesSettings,
                    ...this.pageContent(settings, 'activitiesSettings'),
                    hero_image: settings.hero_image || ''
                },
                activities: this.asList(settings.activities)
            };
        } catch (error) {
            return this.getDefaultEventsData();
        }
    },
    
    getDefaultEventsData() {
        return {
            activitiesSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            activities: []
        };
    },
    
    // Load news data
    async loadNewsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('news');
            const defaultData = this.getDefaultNewsData();
            
            return {
                newsSettings: {
                    ...defaultData.newsSettings,
                    ...this.pageContent(settings, 'newsSettings'),
                    hero_image: settings.hero_image || ''
                },
                news: this.asList(settings.news)
            };
        } catch (error) {
            return this.getDefaultNewsData();
        }
    },
    
    getDefaultNewsData() {
        return {
            newsSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            news: []
        };
    },
    
    // Load announcements data
    async loadAnnouncementsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('announcements');
            const defaultData = this.getDefaultAnnouncementsData();
            
            return {
                announcementsSettings: {
                    ...defaultData.announcementsSettings,
                    ...this.pageContent(settings, 'announcementsSettings'),
                    hero_image: settings.hero_image || ''
                },
                announcements: this.asList(settings.announcements)
            };
        } catch (error) {
            return this.getDefaultAnnouncementsData();
        }
    },
    
    getDefaultAnnouncementsData() {
        return {
            announcementsSettings: {
                title: '',
                subtitle: '',
                hero_image: ''
            },
            announcements: []
        };
    },
    
    // Load admission data
    async loadAdmissionData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('admission');
            const defaultData = this.getDefaultAdmissionData();
            
            return {
                admissionContent: {
                    ...defaultData.admissionContent,
                    ...this.pageContent(settings, 'admissionContent'),
                    hero_image: settings.hero_image || ''
                },
                admissionLevels: this.asList(settings.admissionLevels || settings.admission_levels),
                feeStructure: {
                    ...defaultData.feeStructure,
                    ...(typeof settings.fee_structure === 'string' ? { content: settings.fee_structure } : (settings.fee_structure || settings.feeStructure || {}))
                },
                contactInfo: {
                    phone: (settings.contactInfo && settings.contactInfo.phone) || settings.contact_phone || '',
                    email: (settings.contactInfo && settings.contactInfo.email) || settings.contact_email || ''
                }
            };
        } catch (error) {
            return this.getDefaultAdmissionData();
        }
    },
    
    getDefaultAdmissionData() {
        return {
            admissionContent: {
                hero_image: ''
            },
            admissionLevels: [],
            feeStructure: {
                image: ''
            },
            contactInfo: {
                phone: '',
                email: ''
            }
        };
    },
    
    // Load school background data
    async loadSchoolBackgroundData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('background');
            const defaultData = this.getDefaultSchoolBackgroundData();
            
            const content = this.pageContent(settings, 'backgroundContent');
            const history = (content.history_section && typeof content.history_section === 'object' && !Array.isArray(content.history_section))
                ? content.history_section
                : {};
            const saved = !!(settings && (settings.id || settings.content || settings.history_section || settings.history_image));
            return {
                backgroundContent: {
                    ...defaultData.backgroundContent,
                    ...content,
                    hero_image: content.hero_image || settings.hero_image || '',
                    content: content.content || (saved ? '' : defaultData.backgroundContent.content),
                    content_title: content.content_title || '',
                    content_image: content.content_image || ''
                },
                historySection: {
                    title: history.title || content.history_title || '',
                    content: history.content || content.history_content || '',
                    image: history.image || content.history_image || ''
                },
                milestones: this.asList(content.milestones || settings.milestones)
            };
        } catch (error) {
            return this.getDefaultSchoolBackgroundData();
        }
    },
    
    getDefaultSchoolBackgroundData() {
        return {
            backgroundContent: {
                title: '',
                subtitle: '',
                hero_image: '',
                content: '',
                content_title: '',
                content_image: '',
                history_section: { title: '', content: '', image: '' },
                milestones: [],
                video_url: '',
                video_embed_code: ''
            },
            historySection: { title: '', content: '', image: '' },
            milestones: []
        };
    },
    
    // Load sponsorship data
    async loadSponsorshipData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('sponsorship');
            const defaultData = this.getDefaultSponsorshipData();
            
            return {
                sponsorship: {
                    ...defaultData.sponsorship,
                    ...this.pageContent(settings, 'sponsorship'),
                    hero_image: settings.hero_image || ''
                },
                successStories: this.asList(settings.successStories || settings.success_stories),
                sponsorshipLevels: this.asList(settings.sponsorshipLevels || settings.sponsorship_levels),
                steps: this.asList(settings.steps || settings.sponsorship_steps)
            };
        } catch (error) {
            return this.getDefaultSponsorshipData();
        }
    },
    
    getDefaultSponsorshipData() {
        return {
            sponsorship: {
                title: '', subtitle: '', hero_image: '',
                transformations_title: '', transformations_subtitle: '',
                impact_level_title: '', impact_level_subtitle: '',
                how_it_works_title: '', how_it_works_subtitle: '',
                cta_title: '', cta_subtitle: '',
                apply_button_text: '', sponsor_button_text: '',
                cta_bg_color: '', cta_title_color: '', cta_subtitle_color: '',
                cta_apply_bg_color: '', cta_apply_text_color: '',
                cta_sponsor_bg_color: '', cta_sponsor_text_color: ''
            },
            successStories: [],
            sponsorshipLevels: [],
            steps: []
        };
    },
    
    // Load give-back data
    async loadGiveBackData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('giveback');
            const defaultData = this.getDefaultGiveBackData();
            
            return {
                giveback: {
                    ...defaultData.giveback,
                    ...this.pageContent(settings, 'giveback'),
                    hero_image: settings.hero_image || ''
                },
                needs: this.asList(settings.needs),
                paymentDetails: settings.paymentDetails || settings.payment_details || null
            };
        } catch (error) {
            return this.getDefaultGiveBackData();
        }
    },
    
    getDefaultGiveBackData() {
        return {
            giveback: { title: '', subtitle: '', hero_image: '', contact_phone: '', contact_email: '' },
            needs: [],
            paymentDetails: null
        };
    },
    
    // Load parents data
    async loadParentsData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('parents');
            const defaultData = this.getDefaultParentsData();
            
            return {
                parentsContent: {
                    ...defaultData.parentsContent,
                    ...this.pageContent(settings, 'parentsContent'),
                    hero_image: settings.hero_image || ''
                },
                upcomingEvents: this.asList(settings.upcomingEvents || settings.upcoming_events),
                resources: this.asList(settings.resources),
                contactInfo: this.asList(settings.contactInfo || settings.contact_info)
            };
        } catch (error) {
            return this.getDefaultParentsData();
        }
    },
    
    getDefaultParentsData() {
        return {
            parentsContent: { title: '', subtitle: '', hero_image: '' },
            upcomingEvents: [],
            resources: [],
            contactInfo: []
        };
    },
    
    // Load people behind school data
    async loadPeopleBehindSchoolData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('people-behind-school');
            const defaultData = this.getDefaultPeopleBehindSchoolData();
            
            return {
                peopleContent: {
                    ...defaultData.peopleContent,
                    ...this.pageContent(settings, 'peopleContent'),
                    hero_image: settings.hero_image || ''
                },
                people: this.asList(settings.people).length ? this.asList(settings.people) : this.asList(settings.people_json)
            };
        } catch (error) {
            return this.getDefaultPeopleBehindSchoolData();
        }
    },
    
    getDefaultPeopleBehindSchoolData() {
        return {
            peopleContent: {
                title: '',
                subtitle: '',
                hero_image: '',
                content: '',
                video_url: '',
                video_embed_code: ''
            },
            people: []
        };
    },
    
    // Load resources data
    async loadResourcesData() {
        try {
            this.initMediaStorage();
            const settings = await this.loadDb('resources');
            const defaultData = this.getDefaultResourcesData();
            
            return {
                parentsContent: {
                    ...defaultData.parentsContent,
                    ...this.pageContent(settings, 'parentsContent'),
                    hero_image: settings.hero_image || ''
                },
                resourceDetailSections: this.asList(settings.resourceDetailSections || settings.resource_detail_sections)
            };
        } catch (error) {
            return this.getDefaultResourcesData();
        }
    },
    
    getDefaultResourcesData() {
        return {
            parentsContent: {
                hero_caption: '', hero_title: '', hero_image: '',
                section_title: '', section_subtitle: ''
            },
            resourceDetailSections: []
        };
    }
};
