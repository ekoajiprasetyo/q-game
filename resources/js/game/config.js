import Phaser from 'phaser';
import { GAME_CONFIG, SCENES } from './utils/constants.js';

// Import scenes
import BootScene from './scenes/BootScene.js';
import PreloadScene from './scenes/PreloadScene.js';
import MenuScene from './scenes/MenuScene.js';
import SetupScene from './scenes/SetupScene.js';
import GameScene from './scenes/GameScene.js';
import ResultScene from './scenes/ResultScene.js';

/**
 * Phaser Game Configuration
 * Using RESIZE mode to fill entire viewport without blank spaces
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
        PreloadScene,
        MenuScene,
        SetupScene,
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
