-- 数据库初始化脚本

-- 创建用户表
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  password_hash varchar(255) NOT NULL,
  nickname varchar(50) DEFAULT NULL,
  avatar varchar(255) DEFAULT NULL,
  bio text DEFAULT NULL,
  role varchar(20) DEFAULT 'user',
  status tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_username (username),
  KEY idx_email_password (email, password_hash),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建博客表
CREATE TABLE IF NOT EXISTS blogs (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(200) NOT NULL,
  content longtext NOT NULL,
  summary varchar(500) DEFAULT NULL,
  cover_image varchar(255) DEFAULT NULL,
  category_id int(11) DEFAULT NULL,
  author_id int(11) NOT NULL,
  view_count int(11) DEFAULT 0,
  comment_count int(11) DEFAULT 0,
  status varchar(20) DEFAULT 'draft',
  is_featured tinyint(1) DEFAULT 0,
  published_at timestamp NULL DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_author_id (author_id),
  KEY idx_category_id (category_id),
  KEY idx_status_created_at (status, created_at),
  FULLTEXT KEY idx_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建作品表
CREATE TABLE IF NOT EXISTS works (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(200) NOT NULL,
  description text NOT NULL,
  images json DEFAULT NULL,
  project_link varchar(255) DEFAULT NULL,
  github_link varchar(255) DEFAULT NULL,
  category varchar(50) DEFAULT NULL,
  author_id int(11) NOT NULL,
  is_public tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_author_id (author_id),
  KEY idx_is_public_created_at (is_public, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建笔记表
CREATE TABLE IF NOT EXISTS notes (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(200) NOT NULL,
  content longtext NOT NULL,
  status varchar(20) DEFAULT 'draft',
  is_public tinyint(1) DEFAULT 0,
  creator_id int(11) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_creator_id (creator_id),
  KEY idx_status_is_public_created_at (status, is_public, created_at),
  FULLTEXT KEY idx_note_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建笔记版本历史表
CREATE TABLE IF NOT EXISTS note_versions (
  id int(11) NOT NULL AUTO_INCREMENT,
  note_id int(11) NOT NULL,
  title varchar(200) NOT NULL,
  content longtext NOT NULL,
  version_number int(11) NOT NULL,
  created_by int(11) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_note_id_version_number (note_id, version_number),
  KEY idx_created_by (created_by),
  CONSTRAINT fk_note_versions_note_id FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE,
  CONSTRAINT fk_note_versions_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建评论表
CREATE TABLE IF NOT EXISTS comments (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  post_id int(11) NOT NULL,
  post_type varchar(20) NOT NULL,
  parent_id int(11) DEFAULT NULL,
  content text NOT NULL,
  status varchar(20) DEFAULT 'pending',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_post_id_post_type (post_id, post_type),
  KEY idx_parent_id (parent_id),
  KEY idx_status_created_at (status, created_at),
  CONSTRAINT fk_comments_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_parent_id FOREIGN KEY (parent_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建订阅表
CREATE TABLE IF NOT EXISTS subscriptions (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(100) NOT NULL,
  type varchar(20) DEFAULT 'blog',
  token varchar(255) NOT NULL,
  status varchar(20) DEFAULT 'pending',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_email (email),
  KEY idx_token_status (token, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建用户分析表
CREATE TABLE IF NOT EXISTS user_analytics (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  session_id varchar(255) NOT NULL,
  event_type varchar(50) NOT NULL,
  event_data json DEFAULT NULL,
  url varchar(500) NOT NULL,
  referrer varchar(500) DEFAULT NULL,
  user_agent varchar(255) NOT NULL,
  ip_address varchar(45) NOT NULL,
  browser varchar(50) DEFAULT NULL,
  device varchar(50) DEFAULT NULL,
  os varchar(50) DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_session_id_created_at (session_id, created_at),
  KEY idx_event_type_created_at (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建联系表单表
CREATE TABLE IF NOT EXISTS contact_forms (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  subject varchar(200) NOT NULL,
  message text NOT NULL,
  status tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  processed_at timestamp DEFAULT NULL,
  processed_by int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_email (email),
  KEY idx_status_created_at (status, created_at),
  CONSTRAINT fk_contact_forms_processed_by FOREIGN KEY (processed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建系统配置表
CREATE TABLE IF NOT EXISTS system_configs (
  id int(11) NOT NULL AUTO_INCREMENT,
  config_key varchar(100) NOT NULL,
  config_value text NOT NULL,
  description varchar(255) DEFAULT NULL,
  type varchar(50) DEFAULT 'string',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_config_key_value (config_key, config_value(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建博客分类表
CREATE TABLE IF NOT EXISTS blog_categories (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  slug varchar(50) NOT NULL,
  parent_id int(11) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  sort_order int(11) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_name (name),
  UNIQUE KEY idx_slug (slug),
  KEY idx_parent_id_sort_order (parent_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建博客标签表
CREATE TABLE IF NOT EXISTS blog_tags (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  slug varchar(50) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_name (name),
  UNIQUE KEY idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建博客标签关联表
CREATE TABLE IF NOT EXISTS blog_tag_relations (
  id int(11) NOT NULL AUTO_INCREMENT,
  blog_id int(11) NOT NULL,
  tag_id int(11) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_blog_tag (blog_id, tag_id),
  KEY idx_tag_id (tag_id),
  CONSTRAINT fk_blog_tag_relations_blog_id FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
  CONSTRAINT fk_blog_tag_relations_tag_id FOREIGN KEY (tag_id) REFERENCES blog_tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建脑图根节点表
CREATE TABLE IF NOT EXISTS mindmap_roots (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(200) NOT NULL,
  description text DEFAULT NULL,
  screenshot_path varchar(255) DEFAULT NULL,
  creator_id int(11) NOT NULL,
  is_public tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_is_public_created_at (is_public, created_at),
  KEY idx_creator_id_created_at (creator_id, created_at),
  KEY idx_title (title),
  CONSTRAINT fk_mindmap_roots_creator_id FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建脑图节点表
CREATE TABLE IF NOT EXISTS mindmap_nodes (
  id int(11) NOT NULL AUTO_INCREMENT,
  root_id int(11) NOT NULL,
  parent_id int(11) DEFAULT NULL,
  title varchar(200) NOT NULL,
  node_type varchar(20) DEFAULT 'node',
  note_id int(11) DEFAULT NULL,
  position_x float DEFAULT 0,
  position_y float DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_root_id_parent_id (root_id, parent_id),
  KEY idx_node_type_note_id (node_type, note_id),
  CONSTRAINT fk_mindmap_nodes_root_id FOREIGN KEY (root_id) REFERENCES mindmap_roots (id) ON DELETE CASCADE,
  CONSTRAINT fk_mindmap_nodes_parent_id FOREIGN KEY (parent_id) REFERENCES mindmap_nodes (id) ON DELETE CASCADE,
  CONSTRAINT fk_mindmap_nodes_note_id FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建节点链接表
CREATE TABLE IF NOT EXISTS node_links (
  id int(11) NOT NULL AUTO_INCREMENT,
  source_node_id int(11) NOT NULL,
  target_node_id int(11) NOT NULL,
  link_type varchar(20) DEFAULT 'bidirectional',
  label varchar(100) DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_node_pair (source_node_id,target_node_id),
  KEY idx_target_node_id (target_node_id),
  CONSTRAINT fk_node_links_source_node_id FOREIGN KEY (source_node_id) REFERENCES mindmap_nodes (id) ON DELETE CASCADE,
  CONSTRAINT fk_node_links_target_node_id FOREIGN KEY (target_node_id) REFERENCES mindmap_nodes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 添加外键约束
ALTER TABLE blogs ADD CONSTRAINT fk_blogs_author_id FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE blogs ADD CONSTRAINT fk_blogs_category_id FOREIGN KEY (category_id) REFERENCES blog_categories (id) ON DELETE SET NULL;
ALTER TABLE works ADD CONSTRAINT fk_works_author_id FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE notes ADD CONSTRAINT fk_notes_creator_id FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE;

-- 插入默认系统配置
INSERT INTO system_configs (config_key, config_value, type, description) VALUES
('site_name', '个人网站', 'string', '网站名称'),
('site_description', '我的个人博客和作品集', 'string', '网站描述'),
('contact_email', 'admin@example.com', 'string', '联系邮箱'),
('social_links', '{"github":"https://github.com","twitter":"https://twitter.com"}', 'json', '社交媒体链接'),
('blog_per_page', '10', 'number', '博客每页显示数量'),
('work_per_page', '8', 'number', '作品每页显示数量'),
('enable_comments', '1', 'boolean', '是否启用评论'),
('enable_subscription', '1', 'boolean', '是否启用订阅');

-- 创建默认管理员用户（密码：admin123）
INSERT INTO users (username, email, password_hash, nickname, role, status) 
VALUES ('admin', 'admin@example.com', '$2y$10$QV7eXz5Z6h7g8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6A7B8C9D', '管理员', 'admin', 1);

-- 创建默认博客分类
INSERT INTO blog_categories (name, slug, description) VALUES
('技术', 'tech', '技术相关文章'),
('生活', 'life', '生活随笔'),
('学习', 'study', '学习笔记');

-- 创建默认博客标签
INSERT INTO blog_tags (name, slug) VALUES
('前端', 'frontend'),
('后端', 'backend'),
('数据库', 'database'),
('算法', 'algorithm'),
('架构', 'architecture');

-- 创建示例博客文章
INSERT INTO blogs (title, content, summary, category_id, author_id, status, is_featured, published_at) 
VALUES ('欢迎来到我的个人网站', '这是我的个人网站首页，记录我的学习和生活。', '欢迎来到我的个人网站', 1, 1, 'published', 1, NOW());

-- 关联博客标签
INSERT INTO blog_tag_relations (blog_id, tag_id) VALUES (1, 1), (1, 2);

-- 初始化完成
SELECT '数据库初始化完成' AS message;