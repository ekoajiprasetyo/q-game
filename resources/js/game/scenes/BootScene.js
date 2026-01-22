import Phaser from 'phaser';
import { SCENES, COLORS } from '../utils/constants.js';

/**
 * BootScene - Initial scene for loading essential assets
 */
export default class BootScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.BOOT });
    }

    preload() {
        // Load minimal assets for preloader
        // Logo will be shown during preload scene
    }

    create() {
        // Set background color
        this.cameras.main.setBackgroundColor(COLORS.BACKGROUND);

        // Proceed to preload scene
        this.scene.start(SCENES.PRELOAD);
    }
}
