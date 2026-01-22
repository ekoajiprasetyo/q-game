import Phaser from 'phaser';

// Import scenes needed for Gameplay only
import BootScene from './scenes/BootScene.js';
import PreloadScene from './scenes/PreloadScene.js';
import GameScene from './scenes/GameScene.js';
import ResultScene from './scenes/ResultScene.js';

/**
 * Phaser Game Config for Play Mode
 * Skips MenuScene and SetupScene completely
 */
const config = {
    type: Phaser.AUTO,
    parent: 'game-container',
    backgroundColor: '#FFF8F0',
    scale: {
        mode: Phaser.Scale.RESIZE,
        width: '100%',
        height: '100%',
        autoCenter: Phaser.Scale.CENTER_BOTH,
    },
    scene: [
        BootScene,
        PreloadScene, // Modified to jump to GameScene
        // No MenuScene
        // No SetupScene
        GameScene,
        ResultScene,
    ],
    physics: {
        default: 'arcade',
        arcade: {
            debug: false,
        },
    },
    render: {
        antialias: true,
        pixelArt: false,
        roundPixels: true,
    },
    dom: {
        createContainer: true,
    },
    input: {
        keyboard: true,
        mouse: true,
        touch: true,
    },
};

export default config;
