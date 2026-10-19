/**
 * Page Data Loader - Fetches data from PHP APIs and provides fallbacks
 * Matches the exact structure from PHP files
 */

const PageDataLoader = {
    baseURL: '/api/content',
    
    // Helper to initialize MediaStorage
    initMediaStorage() {
        if (typeof window.MediaStorage === 'undefined') {
            window.MediaStorage = {
                loadMedia() {
                    try {
                        const stored = localStorage.getItem('media_items');
                        if (!stored) return [];
                        const parsed = JSON.parse(stored);
                        return Array.isArray(parsed) ? parsed : [];
                    } catch (e) {
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
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('about') || {};
            const defaultData = this.getDefaultAboutData();
            
            // Merge saved settings with defaults
            return {
                aboutContent: {
                    ...defaultData.aboutContent,
                    ...settings.aboutContent,
                    hero_image: settings.hero_image || settings.aboutContent?.hero_image || defaultData.aboutContent.hero_image,
                    title: settings.title || settings.aboutContent?.title || defaultData.aboutContent.title,
                    hero_caption: settings.hero_caption || settings.aboutContent?.hero_caption || defaultData.aboutContent.hero_caption
                },
                visionMissionContent: {
                    ...defaultData.visionMissionContent,
                    ...settings.visionMissionContent
                },
                whyChooseContent: {
                    ...defaultData.whyChooseContent,
                    ...settings.whyChooseContent
                },
                whyChoosePoints: settings.whyChoosePoints || defaultData.whyChoosePoints
            };
        } catch (error) {
            console.warn('Using default about data:', error);
            return this.getDefaultAboutData();
        }
    },
    
    getDefaultAboutData() {
        return {
            aboutContent: {
                title: 'About SJS',
                content: 'SJS is dedicated to providing quality education and nurturing young minds. We are committed to creating a nurturing and challenging educational environment that empowers students to achieve academic excellence, develop critical thinking skills, and become responsible global citizens.',
                mission_title: 'Our Mission',
                mission_content: 'To provide a nurturing and challenging educational environment that empowers students to achieve academic excellence, develop critical thinking skills, and become responsible global citizens.\n\nWe believe in holistic education that balances academic rigor with character development, creativity, and social responsibility. Our dedicated faculty and state-of-the-art facilities create an environment where every student can thrive.',
                hero_caption: 'Know Us Better',
                image: '',
                about_image_2: '',
                hero_image: '',
                core_values: ['Faith', 'Discipline', 'Integrity', 'Self-Reliance', 'Patriotism', 'Holistic Development'],
                statistics: [
                    {label: 'Students', value: '2,500+', icon: 'Users'},
                    {label: 'Courses', value: '20+', icon: 'BookOpen'},
                    {label: 'Awards', value: '25+', icon: 'Award'},
                    {label: 'Success Rate', value: '98%', icon: 'Target'}
                ]
            },
            visionMissionContent: {
                vision_text: 'To build a developmental generation.'
            },
            whyChooseContent: {
                title: 'Why Choose SJS'
            },
            whyChoosePoints: [
                'Strong spiritual foundation and values',
                'Academic excellence with experienced teaching staff',
                'Both day and boarding options',
                'A nurturing, safe, and inclusive environment',
                'Commitment to holistic growth and ICT development'
            ]
        };
    },
    
    // Load clubs data
    async loadClubsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('clubs') || {};
            const defaultData = this.getDefaultClubsData();
            
            return {
                clubsContent: {
                    ...defaultData.clubsContent,
                    ...settings.clubsContent,
                    hero_image: settings.hero_image || settings.clubsContent?.hero_image || defaultData.clubsContent.hero_image
                },
                clubs: settings.clubs || defaultData.clubs || []
            };
        } catch (error) {
            return this.getDefaultClubsData();
        }
    },
    
    getDefaultClubsData() {
        return {
            clubsContent: {
                title: 'Clubs',
                subtitle: 'Extracurricular Activities and Student Organizations',
                hero_image: ''
            },
            clubs: []
        };
    },
    
    // Load curriculum data
    async loadCurriculumData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('curriculum') || {};
            const defaultData = this.getDefaultCurriculumData();
            
            return {
                curriculumContent: {
                    ...defaultData.curriculumContent,
                    ...settings.curriculumContent,
                    hero_image: settings.hero_image || settings.curriculumContent?.hero_image || defaultData.curriculumContent.hero_image
                },
                curriculumPrograms: settings.curriculumPrograms || defaultData.curriculumPrograms,
                curriculumWhyChoose: {
                    ...defaultData.curriculumWhyChoose,
                    ...settings.curriculumWhyChoose
                },
                curriculumWhyChooseItems: settings.curriculumWhyChooseItems || defaultData.curriculumWhyChooseItems
            };
        } catch (error) {
            return this.getDefaultCurriculumData();
        }
    },
    
    getDefaultCurriculumData() {
        return {
            curriculumContent: {
                page_title: 'Curriculum',
                page_subtitle: 'Our Educational Programs',
                intro_text: 'We offer a comprehensive curriculum designed to develop well-rounded individuals prepared for success in all areas of life.',
                hero_image: ''
            },
            curriculumPrograms: [
                {
                    program_title: 'Nursery Education Program',
                    description: 'Our nursery program focuses on early childhood development, fostering communication skills, creativity, social interaction, and independence in a nurturing environment.',
                    subjects: 'Language Development\nEarly Mathematics\nCreative Arts\nPhysical Activities'
                },
                {
                    program_title: 'Primary Education Program',
                    description: 'Our primary education program offers a comprehensive curriculum that builds strong academic foundations. Students develop critical thinking, problem-solving skills, and a love for learning across all subjects.',
                    subjects: 'Mathematics\nEnglish Language\nScience\nSocial Studies\nReligious Education\nPhysical Education'
                }
            ],
            curriculumWhyChoose: {
                section_title: 'Why Choose Our Curriculum?'
            },
            curriculumWhyChooseItems: [
                {
                    title: 'Academic Excellence',
                    description: 'Rigorous academic programs that challenge and inspire students',
                    icon_name: 'graduation'
                },
                {
                    title: 'Holistic Development',
                    description: 'Focus on character building, creativity, and physical well-being',
                    icon_name: 'user'
                },
                {
                    title: 'Character Building',
                    description: 'Values-based education that shapes responsible citizens',
                    icon_name: 'award'
                },
                {
                    title: 'Modern Facilities',
                    description: 'State-of-the-art resources to support learning',
                    icon_name: 'book'
                }
            ]
        };
    },
    
    // Load facilities data
    async loadFacilitiesData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('facilities') || {};
            const defaultData = this.getDefaultFacilitiesData();
            
            return {
                facilitiesSettings: {
                    ...defaultData.facilitiesSettings,
                    ...settings.facilitiesSettings,
                    hero_image: settings.hero_image || settings.facilitiesSettings?.hero_image || defaultData.facilitiesSettings.hero_image
                },
                facilities: settings.facilities || defaultData.facilities || [],
                features: settings.features || defaultData.features || []
            };
        } catch (error) {
            return this.getDefaultFacilitiesData();
        }
    },
    
    getDefaultFacilitiesData() {
        return {
            facilitiesSettings: {
                title: 'School Facilities',
                subtitle: 'World-Class Facilities for Learning and Growth',
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
            const settings = window.MediaStorage.loadSettings('teachers') || {};
            const defaultData = this.getDefaultTeachersData();
            
            return {
                teachersSettings: {
                    ...defaultData.teachersSettings,
                    ...settings.teachersSettings,
                    hero_image: settings.hero_image || settings.teachersSettings?.hero_image || defaultData.teachersSettings.hero_image
                },
                teachers: settings.teachers || settings.people_json || defaultData.teachers || []
            };
        } catch (error) {
            return this.getDefaultTeachersData();
        }
    },
    
    getDefaultTeachersData() {
        return {
            teachersSettings: {
                title: 'Meet Our Staff',
                subtitle: 'Our Expert Teachers',
                hero_image: ''
            },
            teachers: []
        };
    },
    
    // Load testimonials data
    async loadTestimonialsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('testimonials') || {};
            const defaultData = this.getDefaultTestimonialsData();
            
            return {
                testimonialsSettings: {
                    ...defaultData.testimonialsSettings,
                    ...settings.testimonialsSettings,
                    hero_image: settings.hero_image || settings.testimonialsSettings?.hero_image || defaultData.testimonialsSettings.hero_image
                },
                testimonials: settings.testimonials || defaultData.testimonials || []
            };
        } catch (error) {
            return this.getDefaultTestimonialsData();
        }
    },
    
    getDefaultTestimonialsData() {
        return {
            testimonialsSettings: {
                title: 'Testimonials',
                subtitle: 'What Our Community Says',
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
            const gallerySettings = window.MediaStorage.loadSettings('gallery') || {};
            
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
                title: 'School Gallery',
                subtitle: 'Photos and Memories',
                hero_image: ''
            },
            galleryImages: []
        };
    },
    
    // Load events data
    async loadEventsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('activities') || {};
            const defaultData = this.getDefaultEventsData();
            
            return {
                activitiesSettings: {
                    ...defaultData.activitiesSettings,
                    ...settings.activitiesSettings,
                    hero_image: settings.hero_image || settings.activitiesSettings?.hero_image || defaultData.activitiesSettings.hero_image
                },
                activities: settings.activities || settings.clubs || defaultData.activities || []
            };
        } catch (error) {
            return this.getDefaultEventsData();
        }
    },
    
    getDefaultEventsData() {
        return {
            activitiesSettings: {
                title: 'Events',
                subtitle: 'Upcoming Events',
                hero_image: ''
            },
            activities: []
        };
    },
    
    // Load news data
    async loadNewsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('news') || {};
            const defaultData = this.getDefaultNewsData();
            
            return {
                newsSettings: {
                    ...defaultData.newsSettings,
                    ...settings.newsSettings,
                    hero_image: settings.hero_image || settings.newsSettings?.hero_image || defaultData.newsSettings.hero_image
                },
                news: settings.news || defaultData.news || []
            };
        } catch (error) {
            return this.getDefaultNewsData();
        }
    },
    
    getDefaultNewsData() {
        return {
            newsSettings: {
                title: 'News',
                subtitle: 'Latest News and Updates',
                hero_image: ''
            },
            news: []
        };
    },
    
    // Load announcements data
    async loadAnnouncementsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('announcements') || {};
            const defaultData = this.getDefaultAnnouncementsData();
            
            return {
                announcementsSettings: {
                    ...defaultData.announcementsSettings,
                    ...settings.announcementsSettings,
                    hero_image: settings.hero_image || settings.announcementsSettings?.hero_image || defaultData.announcementsSettings.hero_image
                },
                announcements: settings.announcements || defaultData.announcements || []
            };
        } catch (error) {
            return this.getDefaultAnnouncementsData();
        }
    },
    
    getDefaultAnnouncementsData() {
        return {
            announcementsSettings: {
                title: 'Announcements',
                subtitle: 'Important Announcements and Updates',
                hero_image: ''
            },
            announcements: []
        };
    },
    
    // Load admission data
    async loadAdmissionData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('admission') || {};
            const defaultData = this.getDefaultAdmissionData();
            
            return {
                admissionContent: {
                    ...defaultData.admissionContent,
                    ...settings.admissionContent,
                    hero_image: settings.hero_image || settings.admissionContent?.hero_image || defaultData.admissionContent.hero_image
                },
                admissionLevels: settings.admissionLevels || defaultData.admissionLevels,
                feeStructure: {
                    ...defaultData.feeStructure,
                    ...settings.feeStructure
                },
                contactInfo: {
                    ...defaultData.contactInfo,
                    ...settings.contactInfo
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
            admissionLevels: [
                {
                    icon: '🎒',
                    heading: 'Nursery Information',
                    title: 'Nursery',
                    description: 'Our nursery program provides a nurturing and stimulating environment for young learners. Children are introduced to foundational concepts through play-based learning, developing social skills, creativity, and early literacy. To receive an admission letter, parents must pay an admission fee of 40,000 Ugandan Shillings at the school.'
                },
                {
                    icon: '📚',
                    heading: 'Primary Information',
                    title: 'Primary',
                    description: 'Our primary education program offers a comprehensive curriculum that builds strong academic foundations. Students develop critical thinking, problem-solving skills, and a love for learning across all subjects. To receive an admission letter, parents must pay an admission fee of 40,000 Ugandan Shillings at the school.'
                }
            ],
            feeStructure: {
                image: ''
            },
            contactInfo: {
                phone: '+256 705666621',
                email: 'mukonohisc@gmail.com'
            }
        };
    },
    
    // Load school background data
    async loadSchoolBackgroundData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('background') || {};
            const defaultData = this.getDefaultSchoolBackgroundData();
            
            return {
                backgroundContent: {
                    ...defaultData.backgroundContent,
                    ...settings.backgroundContent,
                    hero_image: settings.hero_image || settings.backgroundContent?.hero_image || defaultData.backgroundContent.hero_image
                },
                historySection: {
                    ...defaultData.historySection,
                    ...settings.historySection,
                    ...settings.backgroundContent?.history_section
                }
            };
        } catch (error) {
            return this.getDefaultSchoolBackgroundData();
        }
    },
    
    getDefaultSchoolBackgroundData() {
        return {
            backgroundContent: {
                title: 'School Background',
                subtitle: 'Know Us Better',
                hero_image: '',
                content: '',
                history_section: {
                    title: 'Our History',
                    content: 'SJS was founded in 1985 and is proudly celebrating 40 years of excellence in 2025. The school sits on land donated by the Mukono Diocese Church of Uganda, under the leadership of the Late Archbishop Livingstone Mpalanyi Nkoyooyo, the first Bishop of Mukono.\n\nIt began with just seven students at SJS Boarding Primary School, guided by Headteacher Mr. Mugwano Noah. The first Headteacher, Late Mr. Batte Abraham, worked alongside the first Board Chairperson, Rev. Canon Yusufu Tubwomwe, to lay the school\'s strong foundation.',
                    image: ''
                },
                milestones: [],
                video_url: '',
                video_embed_code: ''
            },
            // Also provide historySection at top level for easier access
            historySection: {
                title: 'Our History',
                content: 'SJS was founded in 1985 and is proudly celebrating 40 years of excellence in 2025. The school sits on land donated by the Mukono Diocese Church of Uganda, under the leadership of the Late Archbishop Livingstone Mpalanyi Nkoyooyo, the first Bishop of Mukono.\n\nIt began with just seven students at SJS Boarding Primary School, guided by Headteacher Mr. Mugwano Noah. The first Headteacher, Late Mr. Batte Abraham, worked alongside the first Board Chairperson, Rev. Canon Yusufu Tubwomwe, to lay the school\'s strong foundation.',
                image: ''
            }
        };
    },
    
    // Load sponsorship data
    async loadSponsorshipData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('sponsorship') || {};
            const defaultData = this.getDefaultSponsorshipData();
            
            return {
                sponsorship: {
                    ...defaultData.sponsorship,
                    ...settings.sponsorship,
                    hero_image: settings.hero_image || settings.sponsorship?.hero_image || defaultData.sponsorship.hero_image
                },
                successStories: settings.successStories || settings.success_stories || defaultData.successStories || [],
                sponsorshipLevels: settings.sponsorshipLevels || settings.sponsorship_levels || defaultData.sponsorshipLevels,
                steps: settings.steps || settings.sponsorship_steps || defaultData.steps
            };
        } catch (error) {
            return this.getDefaultSponsorshipData();
        }
    },
    
    getDefaultSponsorshipData() {
        return {
            sponsorship: {
                title: 'Sponsorship',
                subtitle: 'Support Our Students',
                hero_image: '',
                transformations_title: 'Transformations That Inspire',
                transformations_subtitle: 'See the incredible impact of sponsorship through the eyes of our children. Every story is a testament to the power of education and hope.',
                impact_level_title: 'Choose Your Impact Level',
                impact_level_subtitle: 'Every contribution makes a difference. Choose the sponsorship level that fits your heart and budget.',
                how_it_works_title: 'How Your Sponsorship Works',
                how_it_works_subtitle: 'Our sponsorship process is simple, transparent, and impactful. Here\'s how you can change a child\'s life.',
                cta_title: 'Ready to Change a Life?',
                cta_subtitle: 'Join our sponsorship program today and be part of a child\'s journey to a brighter future.',
                apply_button_text: 'Apply for Sponsorship',
                sponsor_button_text: 'Sponsor a Child'
            },
            successStories: [],
            sponsorshipLevels: [
                {
                    name: 'Full Scholarship',
                    amount: 'UGX 500,000',
                    description: 'Complete tuition, books, and uniform for one academic year',
                    benefits: ['Full tuition coverage', 'Textbooks and supplies', 'School uniform', 'Monthly progress reports']
                },
                {
                    name: 'Partial Scholarship',
                    amount: 'UGX 250,000',
                    description: 'Half tuition coverage for one academic year',
                    benefits: ['50% tuition coverage', 'Textbooks', 'Quarterly progress reports', 'Student communication']
                },
                {
                    name: 'Book & Uniform',
                    amount: 'UGX 100,000',
                    description: 'Essential supplies and uniform for one student',
                    benefits: ['Complete textbook set', 'School uniform', 'School bag', 'Semester progress report']
                }
            ],
            steps: [
                {title: 'Choose Your Child', description: 'Browse profiles and select a child whose story touches your heart.'},
                {title: 'Make Your Pledge', description: 'Choose your sponsorship level and set up your monthly contribution.'},
                {title: 'Stay Connected', description: 'Receive regular updates, photos, and letters from your sponsored child.'},
                {title: 'See the Impact', description: 'Watch your sponsored child grow, learn, and achieve their dreams.'}
            ]
        };
    },
    
    // Load give-back data
    async loadGiveBackData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('giveback') || {};
            const defaultData = this.getDefaultGiveBackData();
            
            return {
                giveback: {
                    ...defaultData.giveback,
                    ...settings.giveback,
                    hero_image: settings.hero_image || settings.giveback?.hero_image || defaultData.giveback.hero_image
                },
                needs: settings.needs || defaultData.needs || [],
                paymentDetails: settings.paymentDetails || settings.payment_details || defaultData.paymentDetails
            };
        } catch (error) {
            return this.getDefaultGiveBackData();
        }
    },
    
    getDefaultGiveBackData() {
        return {
            giveback: {
                title: 'Your Gift. Their Game. SJS',
                subtitle: 'Your generosity helps us provide students with the tools they need to learn, grow, and succeed. From computers and projectors to sports equipment and classroom resources, every donation goes directly toward creating better learning opportunities.',
                hero_image: '',
                contact_phone: '+256 705666621',
                contact_email: 'info@sjs.edu'
            },
            needs: [],
            paymentDetails: null
        };
    },
    
    // Load parents data
    async loadParentsData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('parents') || {};
            const defaultData = this.getDefaultParentsData();
            
            return {
                parentsContent: {
                    ...defaultData.parentsContent,
                    ...settings.parentsContent,
                    hero_image: settings.hero_image || settings.parentsContent?.hero_image || defaultData.parentsContent.hero_image
                },
                upcomingEvents: settings.upcomingEvents || defaultData.upcomingEvents,
                resources: settings.resources || defaultData.resources,
                contactInfo: settings.contactInfo || defaultData.contactInfo
            };
        } catch (error) {
            return this.getDefaultParentsData();
        }
    },
    
    getDefaultParentsData() {
        return {
            parentsContent: {
                title: 'Parent Portal',
                subtitle: 'Resources for Parents',
                hero_image: ''
            },
            upcomingEvents: [
                {title: 'Parent-Teacher Conference', date: 'March 15, 2024', time: '2:00 PM - 4:00 PM', location: 'School Auditorium', type: 'Meeting'},
                {title: 'Science Fair', date: 'March 22, 2024', time: '10:00 AM - 2:00 PM', location: 'School Gymnasium', type: 'Event'},
                {title: 'Sports Day', date: 'March 29, 2024', time: '9:00 AM - 3:00 PM', location: 'School Grounds', type: 'Event'}
            ],
            resources: [
                {title: 'Parent Handbook', description: 'Complete guide to school policies and procedures', icon: '📘', link: 'resources.html#parent-handbook'},
                {title: 'Academic Support', description: 'Resources for helping your child with studies', icon: '🎓', link: 'resources.html#academic-support'},
                {title: 'Health & Safety', description: 'Important health and safety information', icon: '🏥', link: 'resources.html#health-safety'},
                {title: 'Transportation', description: 'Bus schedules and transportation policies', icon: '🚌', link: 'resources.html#transportation'}
            ],
            contactInfo: [
                {title: 'Main Office', value: '+256 123 456 789', description: 'General inquiries', icon: '📞', link: 'tel:+256123456789'},
                {title: 'Email', value: 'info@excellenceacademy.com', description: 'General questions', icon: '✉️', link: 'mailto:info@excellenceacademy.com'},
                {title: 'Office Hours', value: '7:30 AM - 4:30 PM', description: 'Monday to Friday', icon: '🕐', link: null},
                {title: 'Address', value: '123 Education Street, Kampala', description: 'Main campus', icon: '📍', link: 'https://maps.google.com/?q=123+Education+Street,+Kampala'}
            ]
        };
    },
    
    // Load people behind school data
    async loadPeopleBehindSchoolData() {
        try {
            this.initMediaStorage();
            const settings = window.MediaStorage.loadSettings('people-behind-school') || {};
            const defaultData = this.getDefaultPeopleBehindSchoolData();
            
            return {
                peopleContent: {
                    ...defaultData.peopleContent,
                    ...settings.peopleContent,
                    hero_image: settings.hero_image || settings.peopleContent?.hero_image || defaultData.peopleContent.hero_image
                },
                people: settings.people || settings.people_json || defaultData.people || []
            };
        } catch (error) {
            return this.getDefaultPeopleBehindSchoolData();
        }
    },
    
    getDefaultPeopleBehindSchoolData() {
        return {
            peopleContent: {
                title: 'People Behind the School',
                subtitle: 'Meet the Team',
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
            const settings = window.MediaStorage.loadSettings('resources') || {};
            const defaultData = this.getDefaultResourcesData();
            
            return {
                parentsContent: {
                    ...defaultData.parentsContent,
                    ...settings.parentsContent,
                    hero_image: settings.hero_image || settings.parentsContent?.hero_image || defaultData.parentsContent.hero_image
                },
                resourceDetailSections: settings.resourceDetailSections || settings.resource_detail_sections || defaultData.resourceDetailSections
            };
        } catch (error) {
            return this.getDefaultResourcesData();
        }
    },
    
    getDefaultResourcesData() {
        return {
            parentsContent: {
                hero_caption: 'Resources & Information',
                hero_title: 'Resources',
                hero_image: '',
                section_title: 'Resources & Information',
                section_subtitle: 'Access important documents, policies, and resources to support your child\'s education.'
            },
            resourceDetailSections: [
                {
                    title: 'Parent Handbook',
                    description: 'The Parent Handbook provides clear guidelines on school policies, academic expectations, student behaviour, and communication between the school and parents. It helps parents understand school routines, responsibilities and support available for their children\'s education.',
                    image: '',
                    id: 'parent-handbook'
                },
                {
                    title: 'Academic Support',
                    description: 'Parents are expected to support their children\'s learning by ensuring regular attendance, completing homework, and providing learning materials. They should communicate with teachers, attend meetings, and monitor academic progress. Parental support helps improve discipline, motivation, and overall student achievement.',
                    image: '',
                    id: 'academic-support'
                },
                {
                    title: 'Health & Safety',
                    description: 'The school is committed to providing a safe and healthy learning environment for all learners and staff. Basic health services, hygiene practices, and safety rules are in place to prevent accidents and illness. Parents are encouraged to cooperate with the school to ensure children follow health and safety guidelines.',
                    image: '',
                    id: 'health-safety'
                },
                {
                    title: 'Transportation',
                    description: 'Bus schedules and transportation policies. The school provides safe and reliable transportation services for learners using the school bus. All buses operate on designated routes and follow strict safety rules to protect learners. Parents are advised to ensure timely drop-off and pickup at assigned bus points.',
                    image: '',
                    id: 'transportation'
                }
            ]
        };
    }
};
