import Phaser from 'phaser';
import { SCENES, COLORS, HEX_COLORS } from '../utils/constants.js';

/**
 * PreloadScene - Elegant modern loading animation with cheerful design
 * Fully responsive to viewport size
 */
export default class PreloadScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.PRELOAD });
    }

    preload() {
        const { width, height } = this.cameras.main;

        // Create elegant loading UI
        this.createLoadingUI(width, height);

        // Load game assets
        this.loadAssets();

        // Loading progress events
        this.load.on('progress', (value) => {
            this.updateProgress(value);
        });

        this.load.on('complete', () => {
            this.onLoadComplete();
        });
    }

    createLoadingUI(width, height) {
        // Full background
        const bg = this.add.graphics();
        bg.fillStyle(COLORS.BACKGROUND, 1);
        bg.fillRect(0, 0, width, height);

        // Decorative gradient overlay
        const gradientBg = this.add.graphics();
        gradientBg.fillGradientStyle(COLORS.BACKGROUND, COLORS.BACKGROUND, COLORS.GRADIENT_START, COLORS.GRADIENT_END, 0.3);
        gradientBg.fillRect(0, 0, width, height);

        // Animated floating shapes - cheerful bubbles
        this.floatingShapes = [];
        const shapeColors = [COLORS.TEAM_BLUE, COLORS.TEAM_RED, COLORS.ACCENT, COLORS.ACCENT_SECONDARY, COLORS.ACCENT_TERTIARY];

        for (let i = 0; i < 12; i++) {
            const shape = this.add.graphics();
            const size = Phaser.Math.Between(15, 50);
            const color = shapeColors[Phaser.Math.Between(0, shapeColors.length - 1)];
            const alpha = Phaser.Math.FloatBetween(0.08, 0.2);

            shape.fillStyle(color, alpha);
            shape.fillCircle(0, 0, size);
            shape.x = Phaser.Math.Between(0, width);
            shape.y = Phaser.Math.Between(0, height);
            shape.setData('speed', Phaser.Math.FloatBetween(0.3, 0.8));
            shape.setData('wobble', Phaser.Math.FloatBetween(0, Math.PI * 2));
            this.floatingShapes.push(shape);
        }

        // Logo container with elegant glow - responsive positioning
        const logoY = height * 0.4;
        this.logoContainer = this.add.container(width / 2, logoY);

        // Outer glow ring - responsive size
        const glowSize = Math.min(width, height) * 0.12;
        this.glowRing = this.add.graphics();
        this.glowRing.lineStyle(2, COLORS.ACCENT, 0.4);
        this.glowRing.strokeCircle(0, 0, glowSize);
        this.logoContainer.add(this.glowRing);

        // Inner glow effect
        this.innerGlow = this.add.graphics();
        this.innerGlow.fillStyle(COLORS.ACCENT_TERTIARY, 0.2);
        this.innerGlow.fillCircle(0, 0, glowSize * 0.8);
        this.logoContainer.add(this.innerGlow);

        // Main logo "Q" with gradient-like effect - responsive font
        const logoFontSize = Math.min(width * 0.12, 100);
        this.logoShadow = this.add.text(3, 3, 'Q', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: logoFontSize + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEAM_BLUE,
        }).setOrigin(0.5).setAlpha(0.3);
        this.logoContainer.add(this.logoShadow);

        this.logoText = this.add.text(0, 0, 'Q', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: logoFontSize + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT,
        }).setOrigin(0.5);
        this.logoContainer.add(this.logoText);

        // "GAME" text with accent color - responsive
        this.gameText = this.add.text(0, logoFontSize * 0.55, 'GAME', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.03, 24) + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
            letterSpacing: 12,
        }).setOrigin(0.5);
        this.logoContainer.add(this.gameText);

        // Subtitle with cheerful color
        this.subtitleText = this.add.text(width / 2, logoY + logoFontSize * 0.9, 'Tarik Tambang Pembelajaran', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.02, 16) + 'px',
            color: HEX_COLORS.ACCENT_SECONDARY,
        }).setOrigin(0.5).setAlpha(0);

        // Modern progress bar container - responsive
        const progressY = height * 0.7;
        this.progressContainer = this.add.container(width / 2, progressY);

        // Progress bar background - pill shape
        const progressBarWidth = Math.min(width * 0.35, 260);
        const progressBarHeight = 10;

        this.progressBg = this.add.graphics();
        this.progressBg.fillStyle(COLORS.PANEL_BORDER, 1);
        this.progressBg.fillRoundedRect(-progressBarWidth / 2, -progressBarHeight / 2, progressBarWidth, progressBarHeight, 5);
        this.progressContainer.add(this.progressBg);

        // Progress bar fill
        this.progressFill = this.add.graphics();
        this.progressContainer.add(this.progressFill);

        // Progress percentage text
        this.percentText = this.add.text(0, 25, '0%', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.015, 13) + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_LIGHT,
        }).setOrigin(0.5);
        this.progressContainer.add(this.percentText);

        // Store progress bar dimensions
        this.progressBarWidth = progressBarWidth;
        this.progressBarHeight = progressBarHeight;
        this.progressY = progressY;

        // Start animations
        this.startAnimations();
    }

    startAnimations() {
        const { width, height } = this.cameras.main;
        const logoY = height * 0.4;

        // Logo floating animation
        this.tweens.add({
            targets: this.logoContainer,
            y: logoY - 8,
            duration: 2000,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });

        // Glow ring rotation
        this.tweens.add({
            targets: this.glowRing,
            rotation: Math.PI * 2,
            duration: 8000,
            repeat: -1,
            ease: 'Linear',
        });

        // Inner glow pulse
        this.tweens.add({
            targets: this.innerGlow,
            alpha: 0.35,
            scaleX: 1.1,
            scaleY: 1.1,
            duration: 1500,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });

        // Subtitle fade in
        this.tweens.add({
            targets: this.subtitleText,
            alpha: 1,
            duration: 800,
            delay: 300,
            ease: 'Power2',
        });

        // Floating shapes animation
        this.time.addEvent({
            delay: 16,
            callback: this.updateFloatingShapes,
            callbackScope: this,
            loop: true,
        });
    }

    updateFloatingShapes() {
        const { width, height } = this.cameras.main;
        const time = this.time.now * 0.001;

        this.floatingShapes.forEach(shape => {
            shape.y -= shape.getData('speed');
            shape.x += Math.sin(time + shape.getData('wobble')) * 0.4;

            if (shape.y < -60) {
                shape.y = height + 60;
                shape.x = Phaser.Math.Between(0, width);
            }
        });
    }

    updateProgress(value) {
        // Update progress bar fill with gradient effect
        this.progressFill.clear();

        const fillWidth = this.progressBarWidth * value;
        if (fillWidth > 0) {
            // Create colorful gradient progress
            this.progressFill.fillGradientStyle(
                COLORS.TEAM_BLUE, COLORS.ACCENT, COLORS.TEAM_BLUE, COLORS.ACCENT, 1
            );
            this.progressFill.fillRoundedRect(
                -this.progressBarWidth / 2,
                -this.progressBarHeight / 2,
                fillWidth,
                this.progressBarHeight,
                5
            );
        }

        // Update percent text
        this.percentText.setText(`${Math.floor(value * 100)}%`);
        this.percentText.setColor(HEX_COLORS.ACCENT);
    }

    onLoadComplete() {
        const { width, height } = this.cameras.main;

        // Show completion with success color
        this.progressFill.clear();
        this.progressFill.fillStyle(COLORS.SUCCESS, 1);
        this.progressFill.fillRoundedRect(
            -this.progressBarWidth / 2,
            -this.progressBarHeight / 2,
            this.progressBarWidth,
            this.progressBarHeight,
            5
        );

        // Checkmark with celebration
        const checkContainer = this.add.container(width / 2, this.progressY + 50);

        const checkBg = this.add.graphics();
        checkBg.fillStyle(COLORS.SUCCESS, 0.2);
        checkBg.fillCircle(0, 0, 18);
        checkContainer.add(checkBg);

        const checkmark = this.add.text(0, 0, '✓', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '18px',
            fontStyle: 'bold',
            color: HEX_COLORS.SUCCESS,
        }).setOrigin(0.5);
        checkContainer.add(checkmark);

        // Celebrate animation
        this.tweens.add({
            targets: checkContainer,
            scaleX: 1.2,
            scaleY: 1.2,
            duration: 200,
            yoyo: true,
        });

        this.percentText.setText('Siap!');
        this.percentText.setColor(HEX_COLORS.SUCCESS);
    }

    loadAssets() {
        this.load.setBaseURL('/');

        // Load game assets
        this.load.image('char_blue', 'assets/images/char_blue.png');
        this.load.image('char_red', 'assets/images/char_red.png');
        this.load.image('rope_texture', 'assets/images/rope_texture.png');
        this.load.image('game_icon_tw', 'assets/images/tug_of_war_icon.png');
    }

    create() {
        // Elegant transition to menu/game
        this.cameras.main.fadeOut(500);

        this.cameras.main.once('camerafadeoutcomplete', () => {
            // If MenuScene is available, go there. Otherwise go directly to Game (Play Mode)
            if (this.scene.manager.keys[SCENES.MENU]) {
                this.scene.start(SCENES.MENU);
            } else {
                this.scene.start(SCENES.GAME);
            }
        });
    }
}
