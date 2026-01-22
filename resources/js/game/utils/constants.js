// Q-Game Constants
export const GAME_CONFIG = {
    WIDTH: 1280,
    HEIGHT: 720,
    TITLE: 'Q-Game: Tarik Tambang',
};

/**
 * Color Palette - Cheerful, Fun, Elegant & Modern
 * Inspired by candy colors with professional touch
 */
export const COLORS = {
    // Primary Backgrounds
    BACKGROUND: 0xFFF8F0,       // Warm Cream White
    BACKGROUND_ALT: 0xFFF1E6,    // Peach Cream
    BACKGROUND_DARK: 0x2D3142,   // Elegant Dark Navy

    // Panel Colors
    PANEL_BG: 0xFFFFFF,          // Pure White
    PANEL_BORDER: 0xF0E5DB,      // Soft Beige Border
    PANEL_DARK: 0x3D405B,        // Muted Navy

    // Team Colors - Vibrant but balanced
    TEAM_BLUE: 0x6C63FF,         // Royal Purple Blue
    TEAM_BLUE_LIGHT: 0x9D97FF,   // Light Purple Blue
    TEAM_RED: 0xFF6B6B,          // Coral Red
    TEAM_RED_LIGHT: 0xFF9999,    // Light Coral

    // Accent Colors - Cheerful
    ACCENT: 0xFFB347,            // Mango Orange
    ACCENT_SECONDARY: 0x4ECDC4,  // Mint Teal
    ACCENT_TERTIARY: 0xFFE66D,   // Sunny Yellow

    // Semantic Colors
    SUCCESS: 0x2ECC71,           // Emerald Green
    ERROR: 0xE74C3C,             // Vibrant Red
    WARNING: 0xF39C12,           // Golden Orange
    INFO: 0x3498DB,              // Sky Blue

    // Text Colors
    TEXT_MAIN: 0x2D3142,         // Dark Navy
    TEXT_LIGHT: 0x6B7280,        // Medium Gray
    TEXT_MUTED: 0x9CA3AF,        // Light Gray

    // UI Elements
    WHITE: 0xFFFFFF,
    BUTTON_HOVER: 0xF7F4F0,      // Warm White Hover
    GRADIENT_START: 0xFFE5D9,   // Peach
    GRADIENT_END: 0xFFCDD2,     // Rose
};

// Hex colors for CSS
export const HEX_COLORS = {
    // Primary Backgrounds
    BACKGROUND: '#FFF8F0',
    BACKGROUND_ALT: '#FFF1E6',
    BACKGROUND_DARK: '#2D3142',

    // Panel Colors
    PANEL_BG: '#FFFFFF',
    PANEL_BORDER: '#F0E5DB',
    PANEL_DARK: '#3D405B',

    // Team Colors
    TEAM_BLUE: '#6C63FF',
    TEAM_BLUE_LIGHT: '#9D97FF',
    TEAM_RED: '#FF6B6B',
    TEAM_RED_LIGHT: '#FF9999',

    // Accent Colors
    ACCENT: '#FFB347',
    ACCENT_SECONDARY: '#4ECDC4',
    ACCENT_TERTIARY: '#FFE66D',

    // Semantic Colors
    SUCCESS: '#2ECC71',
    ERROR: '#E74C3C',
    WARNING: '#F39C12',
    INFO: '#3498DB',

    // Text Colors
    TEXT_MAIN: '#2D3142',
    TEXT_LIGHT: '#6B7280',
    TEXT_MUTED: '#9CA3AF',
};

// Keyboard mappings
export const KEYS = {
    // Tim Biru (Left side keyboard)
    BLUE_A: 'A',
    BLUE_B: 'S',
    BLUE_C: 'D',
    BLUE_D: 'F',

    // Tim Merah (Number keys)
    RED_A: 'ONE',
    RED_B: 'TWO',
    RED_C: 'THREE',
    RED_D: 'FOUR',

    // Game controls
    PAUSE: 'SPACE',
    MENU: 'ESC',
    NEXT: 'N',
    MUTE: 'M',
    FULLSCREEN: 'F11',
};

// Rope settings
export const ROPE = {
    MAX_POSITION: 5,  // Posisi maksimal tali (-5 sampai +5)
    PULL_DURATION: 500, // Durasi animasi tarik (ms)
    CENTER_X: 640,
    Y_POSITION: 280,
    LENGTH: 600,
};

// Game settings
export const GAME_SETTINGS = {
    DEFAULT_TIME_LIMIT: 30,
    DEFAULT_QUESTIONS: 10,
    DEFAULT_PULL_STRENGTH: 1,
};

// Scene keys
export const SCENES = {
    BOOT: 'BootScene',
    PRELOAD: 'PreloadScene',
    MENU: 'MenuScene',
    SETUP: 'SetupScene',
    GAME: 'GameScene',
    RESULT: 'ResultScene',
};

// API endpoints
export const API = {
    BASE_URL: '/api',
    TOPICS: '/api/topics',
    QUESTIONS: '/api/questions',
    GAME_START: '/api/game/start',
    GAME_ROUND: '/api/game/:id/round',
    GAME_END: '/api/game/:id/end',
    GAME_UPDATE: '/game/api/update-session',
};

export default {
    GAME_CONFIG,
    COLORS,
    HEX_COLORS,
    KEYS,
    ROPE,
    GAME_SETTINGS,
    SCENES,
    API,
};
