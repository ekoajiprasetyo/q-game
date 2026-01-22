import Phaser from 'phaser';
import { SCENES, COLORS, HEX_COLORS } from '../utils/constants.js';

/**
 * MenuScene - Clean modern menu with cheerful and elegant design
 * Full viewport responsive design with login button for teachers
 */
export default class MenuScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.MENU });
    }

    create() {
        const { width, height } = this.cameras.main;

        // Safety: Ensure Game UI Overlay is hidden
        const gameUI = document.getElementById('game-ui');
        if (gameUI) gameUI.style.display = 'none';

        this.cameras.main.fadeIn(500);
        this.cameras.main.setBackgroundColor(COLORS.BACKGROUND);

        this.createBackground(width, height);
        this.createDecorations(width, height);
        this.createTitle(width, height);
        this.createMainButton(width, height);
        this.createFeatureChips(width, height);
        this.createLoginButton(width, height);
        this.createTouchHint(width, height);

        // Handle resize
        this.scale.on('resize', this.handleResize, this);
    }

    handleResize(gameSize) {
        const width = gameSize.width;
        const height = gameSize.height;

        // Refresh the scene on resize
        this.scene.restart();
    }

    createBackground(width, height) {
        // Full background fill
        const bg = this.add.graphics();
        bg.fillStyle(COLORS.BACKGROUND, 1);
        bg.fillRect(0, 0, width, height);

        // Subtle gradient overlay
        const gradient = this.add.graphics();
        gradient.fillGradientStyle(
            COLORS.BACKGROUND, COLORS.BACKGROUND,
            COLORS.GRADIENT_START, COLORS.GRADIENT_END,
            0.4
        );
        gradient.fillRect(0, 0, width, height);
    }

    createDecorations(width, height) {
        // Cheerful floating circles - decorative bubbles
        const decorColors = [COLORS.TEAM_BLUE, COLORS.TEAM_RED, COLORS.ACCENT, COLORS.ACCENT_SECONDARY];

        // Top-left large bubble
        const bubble1 = this.add.graphics();
        bubble1.fillStyle(COLORS.TEAM_BLUE, 0.08);
        bubble1.fillCircle(0, 0, Math.min(width, height) * 0.25);

        // Bottom-right large bubble
        const bubble2 = this.add.graphics();
        bubble2.fillStyle(COLORS.TEAM_RED, 0.08);
        bubble2.fillCircle(width, height, Math.min(width, height) * 0.25);

        // Small decorative bubbles
        for (let i = 0; i < 10; i++) {
            const bubble = this.add.graphics();
            const color = decorColors[i % decorColors.length];
            const size = Phaser.Math.Between(20, 60);
            const x = Phaser.Math.Between(50, width - 50);
            const y = Phaser.Math.Between(50, height - 50);

            bubble.fillStyle(color, 0.06);
            bubble.fillCircle(x, y, size);

            // Gentle floating animation
            this.tweens.add({
                targets: bubble,
                y: y - 15,
                duration: Phaser.Math.Between(2000, 3500),
                ease: 'Sine.easeInOut',
                yoyo: true,
                repeat: -1,
                delay: Phaser.Math.Between(0, 1000),
            });
        }
    }

    createTitle(width, height) {
        const titleY = height * 0.22;
        const logoContainer = this.add.container(width / 2, titleY);

        // Glow effect behind logo
        const glow = this.add.graphics();
        glow.fillStyle(COLORS.ACCENT_TERTIARY, 0.15);
        glow.fillCircle(0, 0, 90);
        logoContainer.add(glow);

        // Title shadow for depth
        const titleShadow = this.add.text(3, 3, 'Q-GAME', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.08, 72) + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEAM_BLUE,
        }).setOrigin(0.5).setAlpha(0.2);
        logoContainer.add(titleShadow);

        // Main title with accent color
        const title = this.add.text(0, 0, 'Q-GAME', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.08, 72) + 'px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT,
        }).setOrigin(0.5);
        logoContainer.add(title);



        // Floating animation for logo
        this.tweens.add({
            targets: logoContainer,
            y: titleY - 5,
            duration: 2500,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });

        // Glow pulse animation
        this.tweens.add({
            targets: glow,
            alpha: 0.25,
            scaleX: 1.1,
            scaleY: 1.1,
            duration: 2000,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });



        // Description
        this.add.text(width / 2, titleY + 70, 'Game edukatif interaktif untuk kompetisi tim di kelas', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.018, 15) + 'px',
            color: HEX_COLORS.TEXT_LIGHT,
        }).setOrigin(0.5);
    }

    createMainButton(width, height) {
        const buttonY = height * 0.52;
        const buttonContainer = this.add.container(width / 2, buttonY);

        const btnWidth = Math.min(width * 0.45, 360);
        const btnHeight = 75;

        // Button shadow for depth
        const shadow = this.add.graphics();
        shadow.fillStyle(COLORS.ACCENT, 0.3);
        shadow.fillRoundedRect(-btnWidth / 2 + 4, -btnHeight / 2 + 4, btnWidth, btnHeight, 35);
        buttonContainer.add(shadow);

        // Main button with cheerful gradient
        const button = this.add.graphics();
        button.fillGradientStyle(COLORS.ACCENT, COLORS.ACCENT, COLORS.WARNING, COLORS.ACCENT, 1);
        button.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 35);
        buttonContainer.add(button);

        const iconX = -btnWidth / 2 + 55;

        // Icon - Clean transparent image
        const icon = this.add.image(iconX, 0, 'game_icon_tw')
            .setDisplaySize(70, 70);
        buttonContainer.add(icon);



        // Button text
        const buttonText = this.add.text(45, -8, 'TARIK TAMBANG', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: '#ffffff',
        }).setOrigin(0.5);
        buttonContainer.add(buttonText);

        // Sub text
        const subText = this.add.text(45, 16, 'Sentuh untuk bermain', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '11px',
            color: 'rgba(255,255,255,0.85)',
        }).setOrigin(0.5);
        buttonContainer.add(subText);

        // Interactive zone
        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight)
            .setInteractive({ useHandCursor: true });
        buttonContainer.add(hitArea);

        hitArea.on('pointerover', () => {
            this.tweens.add({
                targets: buttonContainer,
                scaleX: 1.06,
                scaleY: 1.06,
                duration: 150,
                ease: 'Power2',
            });
        });

        hitArea.on('pointerout', () => {
            this.tweens.add({
                targets: buttonContainer,
                scaleX: 1,
                scaleY: 1,
                duration: 150,
                ease: 'Power2',
            });
        });

        hitArea.on('pointerdown', () => {
            // Removed auto-fullscreen - let user control via button in Setup/Game scene
            // This fixes double-click issue and browser inconsistency

            this.tweens.add({
                targets: buttonContainer,
                scaleX: 0.95,
                scaleY: 0.95,
                duration: 100,
                ease: 'Power2',
                yoyo: true,
                onComplete: () => this.startGame()
            });
        });

        // Gentle pulse animation
        this.tweens.add({
            targets: buttonContainer,
            scaleX: 1.03,
            scaleY: 1.03,
            duration: 1800,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });
    }

    createFeatureChips(width, height) {
        const chipY = height * 0.65;
        const chips = [
            { icon: '🎯', text: 'Kompetitif', color: COLORS.TEAM_BLUE },
            { icon: '🎓', text: 'Edukatif', color: COLORS.SUCCESS },
            { icon: '🎉', text: 'Menyenangkan', color: COLORS.TEAM_RED },
        ];

        const chipSpacing = Math.min(width * 0.2, 160);
        const startX = width / 2 - chipSpacing;

        chips.forEach((chip, index) => {
            const chipX = startX + index * chipSpacing;
            const container = this.add.container(chipX, chipY);

            const chipWidth = Math.min(width * 0.18, 140);

            // Chip background
            const bg = this.add.graphics();
            bg.fillStyle(chip.color, 0.15);
            bg.fillRoundedRect(-chipWidth / 2, -16, chipWidth, 32, 16);
            container.add(bg);

            // Chip border
            const border = this.add.graphics();
            border.lineStyle(2, chip.color, 0.4);
            border.strokeRoundedRect(-chipWidth / 2, -16, chipWidth, 32, 16);
            container.add(border);

            // Chip content
            const content = this.add.text(0, 0, `${chip.icon} ${chip.text}`, {
                fontFamily: 'Poppins, sans-serif',
                fontSize: Math.min(width * 0.014, 12) + 'px',
                fontStyle: 'bold',
                color: HEX_COLORS.TEXT_MAIN,
            }).setOrigin(0.5);
            container.add(content);

            // Hover effect
            const hitArea = this.add.rectangle(0, 0, chipWidth, 32)
                .setInteractive({ useHandCursor: true });
            container.add(hitArea);

            hitArea.on('pointerover', () => {
                this.tweens.add({
                    targets: container,
                    scaleX: 1.1,
                    scaleY: 1.1,
                    duration: 150,
                });
            });

            hitArea.on('pointerout', () => {
                this.tweens.add({
                    targets: container,
                    scaleX: 1,
                    scaleY: 1,
                    duration: 150,
                });
            });
        });
    }

    createLoginButton(width, height) {
        // Login button for teachers - positioned at top right
        const loginContainer = this.add.container(width - 70, 35);

        const btnWidth = 100;
        const btnHeight = 36;

        // Button background - Green color like admin play button
        const bg = this.add.graphics();
        bg.fillStyle(0x7DCEA0, 1); // Accent green
        bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 18);
        loginContainer.add(bg);

        // Login icon (door with arrow) - white
        const icon = this.add.text(-25, 0, '🔐', {
            fontFamily: 'Arial',
            fontSize: '16px',
        }).setOrigin(0.5);
        loginContainer.add(icon);

        // Text - just "Login"
        const loginText = this.add.text(12, 0, 'Login', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px',
            fontStyle: 'bold',
            color: '#ffffff',
        }).setOrigin(0.5);
        loginContainer.add(loginText);

        // Interactive area
        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight)
            .setInteractive({ useHandCursor: true });
        loginContainer.add(hitArea);

        hitArea.on('pointerover', () => {
            this.tweens.add({
                targets: loginContainer,
                scaleX: 1.08,
                scaleY: 1.08,
                duration: 150,
            });
            bg.clear();
            bg.fillStyle(0x5AB890, 1); // Darker green on hover
            bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 18);
        });

        hitArea.on('pointerout', () => {
            this.tweens.add({
                targets: loginContainer,
                scaleX: 1,
                scaleY: 1,
                duration: 150,
            });
            bg.clear();
            bg.fillStyle(0x7DCEA0, 1);
            bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 18);
        });

        hitArea.on('pointerdown', () => {
            // Navigate to login page
            if (window.loginUrl) {
                window.location.href = window.loginUrl;
            } else {
                window.location.href = '/login';
            }
        });
    }

    createTouchHint(width, height) {
        const hintY = height - 50;
        const touchContainer = this.add.container(width / 2, hintY - 15);

        const handIcon = this.add.text(0, 0, '👆', {
            fontFamily: 'Arial',
            fontSize: '22px',
        }).setOrigin(0.5);
        touchContainer.add(handIcon);

        this.add.text(width / 2, hintY + 10, 'Gunakan sentuhan layar untuk bermain', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: Math.min(width * 0.014, 13) + 'px',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5);

        // Bouncing hand animation
        this.tweens.add({
            targets: touchContainer,
            y: hintY - 20,
            duration: 900,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });

        this.tweens.add({
            targets: touchContainer,
            alpha: 0.5,
            duration: 1400,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });
    }

    startGame() {
        this.cameras.main.fadeOut(300);

        this.cameras.main.once('camerafadeoutcomplete', () => {
            this.scene.start(SCENES.SETUP);
        });
    }

    shutdown() {
        this.scale.off('resize', this.handleResize, this);
    }
}
