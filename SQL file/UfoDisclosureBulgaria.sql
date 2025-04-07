-- Drop and create the database
DROP DATABASE IF EXISTS UfoDisclosureBulgaria;
CREATE DATABASE UfoDisclosureBulgaria;
USE UfoDisclosureBulgaria;

-- USERS Table (unchanged)
CREATE TABLE USERS (
  userID                INT(4)         NOT NULL   AUTO_INCREMENT,
  userEmailAddress      VARCHAR(100)   NOT NULL,
  userPassword          VARCHAR(100)   NOT NULL,
  userFirstName         VARCHAR(25)    NOT NULL,
  userLastName          VARCHAR(25)    NOT NULL,
  userType              VARCHAR(20)    NOT NULL,
  userProfilePhoto      VARCHAR(255)   DEFAULT NULL,
  email_verified        TINYINT(1)     DEFAULT 0,
  verification_token    VARCHAR(255)   DEFAULT NULL,
  token_expiry          DATETIME       DEFAULT NULL,
  account_status        ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
  login_attempts        INT            DEFAULT 0,
  last_login_attempt    DATETIME       DEFAULT NULL,
  account_locked_until  DATETIME       DEFAULT NULL,
  password_reset_token  VARCHAR(255)   DEFAULT NULL,
  password_reset_expires DATETIME    DEFAULT NULL,
  created_at            DATETIME       DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (userID),
  UNIQUE INDEX userEmailAddress (userEmailAddress)
);

-- USER_SESSIONS Table
CREATE TABLE USER_SESSIONS (
  sessionID            VARCHAR(255)   NOT NULL,
  userID               INT            NOT NULL,
  ip_address           VARCHAR(45)    NOT NULL,
  user_agent           VARCHAR(255)   NOT NULL,
  created_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at           DATETIME       NOT NULL,
  PRIMARY KEY (sessionID),
  FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
  INDEX idx_expires (expires_at)
);

-- UFO Sighting Table
CREATE TABLE UFO_SIGHTINGS (
    sightingID          INT             NOT NULL AUTO_INCREMENT,
    userID              INT             NOT NULL,
    sightingDate        DATETIME        NOT NULL,
    latitude            DECIMAL(10,8)   NOT NULL,
    longitude           DECIMAL(11,8)   NOT NULL,
    sightingTitle       VARCHAR(255)    NOT NULL,
    sightingDescription TEXT            NOT NULL,
    sightingTitleBG     VARCHAR(255)    NOT NULL,
    sightingDescriptionBG TEXT          NOT NULL,
    sightingType        VARCHAR(50)     NOT NULL,
    mediaPaths          TEXT            DEFAULT NULL,
    created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (sightingID),
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    INDEX idx_sighting_date (sightingDate),
    INDEX idx_location (latitude, longitude)
);

-- DONATIONS Table
CREATE TABLE DONATIONS (
  donationID            INT            NOT NULL AUTO_INCREMENT,
  userID                INT            NOT NULL,
  donationDate          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  donationAmount        DECIMAL(10,2)  NOT NULL,
  donationStatus        ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
  isAnonymous           TINYINT(1)     DEFAULT 0,
  FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
  PRIMARY KEY (donationID),
  INDEX idx_userID (userID)
);

-- FORUM_CATEGORIES Table
CREATE TABLE FORUM_CATEGORIES (
    categoryID          INT             NOT NULL AUTO_INCREMENT,
    displayOrder        INT             NOT NULL DEFAULT 0,
    isPinned            TINYINT(1)      DEFAULT 0,
    created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (categoryID)
);

-- FORUM_CATEGORY_TRANSLATIONS Table
CREATE TABLE FORUM_CATEGORY_TRANSLATIONS (
    translationID       INT             NOT NULL AUTO_INCREMENT,
    categoryID          INT             NOT NULL,
    languageCode        CHAR(2)         NOT NULL, -- 'en' or 'bg'
    categoryName        VARCHAR(100)    NOT NULL,
    categoryDescription TEXT            NULL,
    PRIMARY KEY (translationID),
    FOREIGN KEY (categoryID) REFERENCES FORUM_CATEGORIES(categoryID) ON DELETE CASCADE,
    UNIQUE KEY unique_translation (categoryID, languageCode),
    INDEX idx_language (languageCode)
);

-- FORUM_TOPICS Table
CREATE TABLE FORUM_TOPICS (
    topicID             INT             NOT NULL AUTO_INCREMENT,
    categoryID          INT             NOT NULL,
    userID              INT             NOT NULL,
    topicTitle          VARCHAR(255)    NOT NULL,
    topicContent        TEXT            NOT NULL,
    views               INT             DEFAULT 0,
    isPinned            BOOLEAN         DEFAULT FALSE,
    isLocked            BOOLEAN         DEFAULT FALSE,
    created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (topicID),
    FOREIGN KEY (categoryID) REFERENCES FORUM_CATEGORIES(categoryID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    INDEX idx_category (categoryID),
    INDEX idx_pinned (isPinned),
    INDEX idx_locked (isLocked)
);

-- FORUM_COMMENTS Table 
CREATE TABLE FORUM_COMMENTS (
    commentID           INT             NOT NULL AUTO_INCREMENT,
    topicID             INT             NOT NULL,
    userID              INT             NOT NULL,
    parentCommentID     INT             NULL,
    commentContent      TEXT            NOT NULL,
    isEdited            BOOLEAN         DEFAULT FALSE,
    created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (commentID),
    FOREIGN KEY (topicID) REFERENCES FORUM_TOPICS(topicID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    FOREIGN KEY (parentCommentID) REFERENCES FORUM_COMMENTS(commentID) ON DELETE SET NULL,
    INDEX idx_topic (topicID),
    INDEX idx_parent (parentCommentID)
);

-- EVENTS Table 
CREATE TABLE EVENTS (
    eventID             INT             NOT NULL AUTO_INCREMENT,
    userID              INT             NOT NULL,
    eventTitle          VARCHAR(100)    NOT NULL,
    eventDescription    TEXT            NOT NULL,
    eventLocation       VARCHAR(255)    NOT NULL,
    eventStartDate      DATETIME        NOT NULL,
    eventEndDate        DATETIME        NOT NULL,
    eventType           ENUM('meeting', 'webinar', 'conference', 'observation', 'other') NOT NULL,
    maxAttendees        INT             DEFAULT NULL,
    isPublic            BOOLEAN         DEFAULT TRUE,
    created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (eventID),
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    INDEX idx_event_dates (eventStartDate, eventEndDate),
    INDEX idx_event_type (eventType)
);

-- EVENT_REGISTRATIONS Table
CREATE TABLE EVENT_REGISTRATIONS (
    registrationID      INT             NOT NULL AUTO_INCREMENT,
    eventID             INT             NOT NULL,
    userID              INT             NOT NULL,
    registrationStatus  ENUM('pending', 'confirmed', 'cancelled', 'waitlisted') DEFAULT 'pending',
    registrationDate    DATETIME        DEFAULT CURRENT_TIMESTAMP,
    notes               TEXT            DEFAULT NULL,
    PRIMARY KEY (registrationID),
    FOREIGN KEY (eventID) REFERENCES EVENTS(eventID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (eventID, userID),
    INDEX idx_status (registrationStatus)
);

CREATE TABLE stream_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL,
    setting_value VARCHAR(10) NOT NULL
);

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(userID)
);

-- Insert categories into FORUM_CATEGORIES with isPinned
INSERT INTO FORUM_CATEGORIES (displayOrder, isPinned) VALUES
(1, 0), -- Introduction Thread
(2, 0), -- General Discussion
(3, 0), -- UFO Sightings
(4, 0), -- Research & Evidence
(5, 0), -- Disclosure News
(6, 0); -- Events & Meetups

-- Insert English translations into FORUM_CATEGORY_TRANSLATIONS
INSERT INTO FORUM_CATEGORY_TRANSLATIONS (categoryID, languageCode, categoryName, categoryDescription) VALUES
(1, 'en', 'Introduction Thread', 'Welcome to the forum! Introduce yourself here and tell us about your interest in UFO research.'),
(2, 'en', 'General Discussion', 'General topics related to UFOs and extraterrestrial life.'),
(3, 'en', 'UFO Sightings', 'Report and discuss UFO sightings in Bulgaria and worldwide.'),
(4, 'en', 'Research & Evidence', 'Scientific research, evidence analysis, and methodology discussions.'),
(5, 'en', 'Disclosure News', 'Updates on governmental disclosure and declassified documents.'),
(6, 'en', 'Events & Meetups', 'Discussions about upcoming and past events.');

-- Insert Bulgarian translations into FORUM_CATEGORY_TRANSLATIONS
INSERT INTO FORUM_CATEGORY_TRANSLATIONS (categoryID, languageCode, categoryName, categoryDescription) VALUES
(1, 'bg', 'Тема за Представяне', 'Добре дошли във форума! Представете се тук и споделете своя интерес към изследването на НЛО.'),
(2, 'bg', 'Обща Дискусия', 'Общи теми, свързани с НЛО и извънземен живот.'),
(3, 'bg', 'Наблюдения на НЛО', 'Докладвайте и обсъждайте наблюдения на НЛО в България и по света.'),
(4, 'bg', 'Изследвания и Доказателства', 'Научни изследвания, анализ на доказателства и дискусии за методологията.'),
(5, 'bg', 'Новини за Разкрития', 'Актуална информация за правителствени разкрития и разсекретени документи.'),
(6, 'bg', 'Събития и Срещи', 'Дискусии за предстоящи и минали събития.');

INSERT INTO stream_settings (setting_name, setting_value) VALUES ('is_stream_live', '1');

ALTER TABLE EVENTS
ADD COLUMN eventTitleBG VARCHAR(255) AFTER eventTitle,
ADD COLUMN eventDescriptionBG TEXT AFTER eventDescription,
ADD COLUMN eventLocationBG VARCHAR(255) AFTER eventLocation;