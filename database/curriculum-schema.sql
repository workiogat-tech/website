-- Curriculum Content Database Schema
-- MySQL schema for curriculum content management

-- Curriculum basic content table
CREATE TABLE IF NOT EXISTS curriculum_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_title VARCHAR(200) DEFAULT 'Curriculum',
    page_subtitle VARCHAR(500) DEFAULT 'Comprehensive Educational Programs',
    intro_text TEXT,
    hero_image VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Educational programs table
CREATE TABLE IF NOT EXISTS curriculum_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_title VARCHAR(200) NOT NULL,
    description TEXT,
    subjects TEXT,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Why choose curriculum section
CREATE TABLE IF NOT EXISTS curriculum_why_choose (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(200) DEFAULT 'Why Choose Our Curriculum?',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Why choose curriculum items
CREATE TABLE IF NOT EXISTS curriculum_why_choose_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon_name VARCHAR(100),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
