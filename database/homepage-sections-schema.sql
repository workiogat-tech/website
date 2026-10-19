-- Homepage Sections Database Schema
-- Add these tables to manage all homepage content

-- School Life Section Items
CREATE TABLE IF NOT EXISTS school_life_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon_type VARCHAR(50) DEFAULT 'svg',
    icon_code TEXT,
    icon_emoji VARCHAR(10),
    link_url VARCHAR(500),
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Academics Section Content
CREATE TABLE IF NOT EXISTS academics_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    student_count VARCHAR(50),
    success_rate VARCHAR(50),
    motto TEXT,
    image VARCHAR(500),
    curriculum_link VARCHAR(500),
    button_text VARCHAR(200),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sports & Athletics Items
CREATE TABLE IF NOT EXISTS sports_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    icon_emoji VARCHAR(10),
    icon_color VARCHAR(7),
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vision, Mission, Core Values Content
CREATE TABLE IF NOT EXISTS vision_mission_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vision_title VARCHAR(200) DEFAULT 'OUR VISION',
    vision_text TEXT,
    vision_icon_emoji VARCHAR(10) DEFAULT '💡',
    mission_title VARCHAR(200) DEFAULT 'OUR MISSION',
    mission_text TEXT,
    mission_icon_emoji VARCHAR(10) DEFAULT '🎓',
    core_values_title VARCHAR(200) DEFAULT 'OUR CORE VALUES',
    core_values_icon_emoji VARCHAR(10) DEFAULT '👥',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Core Values Items
CREATE TABLE IF NOT EXISTS core_values_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    value_name VARCHAR(200) NOT NULL,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Why Choose Section Content
CREATE TABLE IF NOT EXISTS why_choose_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    image VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Why Choose Points
CREATE TABLE IF NOT EXISTS why_choose_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    point_text TEXT NOT NULL,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Join Section Content
CREATE TABLE IF NOT EXISTS join_section_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    contact_button_text VARCHAR(200),
    contact_button_link VARCHAR(500),
    learn_more_button_text VARCHAR(200),
    learn_more_button_link VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Navigation Menu Items
CREATE TABLE IF NOT EXISTS navigation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(200) NOT NULL,
    url VARCHAR(500),
    parent_id INT NULL,
    order_index INT DEFAULT 0,
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES navigation_items(id) ON DELETE CASCADE,
    INDEX idx_order (order_index),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Footer Quick Links
CREATE TABLE IF NOT EXISTS footer_quick_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(200) NOT NULL,
    url VARCHAR(500),
    section VARCHAR(100) DEFAULT 'quick-links',
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index),
    INDEX idx_section (section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Footer Hours of Operation
CREATE TABLE IF NOT EXISTS footer_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    day_range VARCHAR(200),
    hours VARCHAR(200),
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data
INSERT INTO school_life_items (title, description, icon_type, icon_code, link_url, order_index) VALUES
('Events', 'Stay updated with our school events, celebrations, and important dates', 'svg', '<svg class="card-icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/></svg>', 'events.php', 1),
('Clubs', 'Join student clubs and organizations to explore your interests', 'svg', '<svg class="card-icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" fill="none"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" fill="none"/></svg>', 'clubs.php', 2),
('Facilities', 'Explore our modern facilities and resources', 'svg', '<svg class="card-icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" fill="none"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2" fill="none"/></svg>', 'facilities.php', 3)
ON DUPLICATE KEY UPDATE title=title;

INSERT INTO academics_content (title, description, student_count, success_rate, motto, curriculum_link) VALUES
('Academics', 'SJS Academy is a dynamic and growing co-educational institution. With a current student population of 2,524, we continue to uphold a strong reputation for academic excellence, moral values, and holistic development. Guided by our school motto, "Faith Promotes Education," we are committed to nurturing students into responsible, God-fearing, and self-reliant citizens.', '2,524', '98%', 'Faith Promotes Education', 'curriculum.php')
ON DUPLICATE KEY UPDATE title=title;

INSERT INTO sports_items (name, icon_emoji, icon_color, order_index) VALUES
('Football', '🏆', '#f59e0b', 1),
('Basketball', '🏀', '#ec4899', 2),
('Athletics', '🏃', '#06b6d4', 3),
('Swimming', '🏊', '#3b82f6', 4)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO vision_mission_content (vision_title, vision_text, mission_title, mission_text) VALUES
('OUR VISION', 'To build a developmental generation.', 'OUR MISSION', 'To develop God-fearing, holistic, positive-oriented, and self-reliant citizens.')
ON DUPLICATE KEY UPDATE vision_title=vision_title;

INSERT INTO core_values_items (value_name, order_index) VALUES
('Faith', 1),
('Discipline', 2),
('Integrity', 3),
('Self-Reliance', 4),
('Patriotism', 5),
('Holistic Development', 6)
ON DUPLICATE KEY UPDATE value_name=value_name;

INSERT INTO why_choose_content (title) VALUES
('Why Choose SJS')
ON DUPLICATE KEY UPDATE title=title;

INSERT INTO why_choose_points (point_text, order_index) VALUES
('Strong spiritual foundation and values', 1),
('Academic excellence with experienced teaching staff', 2),
('Both day and boarding options', 3),
('A nurturing, safe, and inclusive environment', 4),
('Commitment to holistic growth and ICT development', 5)
ON DUPLICATE KEY UPDATE point_text=point_text;

INSERT INTO join_section_content (title, description, contact_button_text, contact_button_link, learn_more_button_text, learn_more_button_link) VALUES
('Join SJS Academy', 'Be part of a community where faith, discipline, and education come together to build a brighter future.', 'Contact Us', 'contact.php', 'Learn More', 'about.php')
ON DUPLICATE KEY UPDATE title=title;
