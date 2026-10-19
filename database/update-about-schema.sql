-- Update about_content table to match React version
ALTER TABLE about_content 
ADD COLUMN IF NOT EXISTS hero_image VARCHAR(500),
ADD COLUMN IF NOT EXISTS mission_title VARCHAR(200) DEFAULT 'Our Mission',
ADD COLUMN IF NOT EXISTS mission_content TEXT,
ADD COLUMN IF NOT EXISTS mission_paragraph2 TEXT,
ADD COLUMN IF NOT EXISTS core_values JSON,
ADD COLUMN IF NOT EXISTS statistics JSON;
