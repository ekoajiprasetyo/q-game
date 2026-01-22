import Phaser from 'phaser';
import { SCENES, COLORS, HEX_COLORS, GAME_SETTINGS } from '../utils/constants.js';
import api from '../utils/api.js';

/**
 * SetupScene - Modern and elegant game configuration screen
 * Features: PIN code entry, question count, and duration selection
 */
export default class SetupScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.SETUP });

        this.gameType = 'tarik_tambang';
        this.config = {
            pinCode: '',
            totalQuestions: GAME_SETTINGS.DEFAULT_QUESTIONS,
            gameDuration: 5 * 60, // 5 minutes default
            teamBlueName: 'Tim Biru',
            teamRedName: 'Tim Merah',
        };

        this.questionOptions = [5, 10, 15, 20, 'custom'];
        this.durationOptions = [
            { label: '1m', value: 1 * 60 },
            { label: '3m', value: 3 * 60 },
            { label: '5m', value: 5 * 60 },
            { label: '10m', value: 10 * 60 },
            { label: 'Custom', value: 'custom' },
        ];
    }

    init(data) {
        // Reset config for fresh start
        this.config = {
            pinCode: '',
            totalQuestions: GAME_SETTINGS.DEFAULT_QUESTIONS,
            gameDuration: 5 * 60,
            teamBlueName: 'Tim Biru',
            teamRedName: 'Tim Merah',
        };
        this.gameType = data?.gameType || 'tarik_tambang';
        this.customQuestionCount = '';
        this.customDuration = '';
        this.isCustomQuestion = false;
        this.isCustomDuration = false;
    }

    create() {
        const { width, height } = this.cameras.main;

        // Safety: Ensure Game UI Overlay is hidden
        const gameUI = document.getElementById('game-ui');
        if (gameUI) gameUI.style.display = 'none';

        this.cameras.main.fadeIn(400);
        this.cameras.main.setBackgroundColor(COLORS.BACKGROUND);

        this.createBackground(width, height);
        this.createHeader(width, height);
        this.createGameIcon(width, height); // Icon above the card
        this.createMainCard(width, height);
        this.createActionButtons(width, height);
        this.createFullscreenButton(width, height);

        // Handle resize
        this.scale.on('resize', this.handleResize, this);
    }

    handleResize(gameSize) {
        this.scene.restart({ gameType: this.gameType });
    }

    createBackground(width, height) {
        // Gradient background
        const gradient = this.add.graphics();
        gradient.fillGradientStyle(
            COLORS.BACKGROUND, COLORS.BACKGROUND,
            COLORS.GRADIENT_START, COLORS.GRADIENT_END,
            0.4
        );
        gradient.fillRect(0, 0, width, height);

        // Decorative floating orbs
        const orbColors = [COLORS.TEAM_BLUE, COLORS.TEAM_RED, COLORS.ACCENT, COLORS.ACCENT_SECONDARY];

        for (let i = 0; i < 8; i++) {
            const orb = this.add.graphics();
            const color = orbColors[i % orbColors.length];
            const size = Phaser.Math.Between(30, 80);
            const x = Phaser.Math.Between(50, width - 50);
            const y = Phaser.Math.Between(50, height - 50);

            orb.fillStyle(color, 0.06);
            orb.fillCircle(x, y, size);

            // Floating animation
            this.tweens.add({
                targets: orb,
                y: y - 12,
                duration: Phaser.Math.Between(2500, 4000),
                ease: 'Sine.easeInOut',
                yoyo: true,
                repeat: -1,
                delay: Phaser.Math.Between(0, 1500),
            });
        }

        // Large decorative circles at corners
        const cornerOrb1 = this.add.graphics();
        cornerOrb1.fillStyle(COLORS.ACCENT, 0.08);
        cornerOrb1.fillCircle(0, height, Math.min(width, height) * 0.3);

        const cornerOrb2 = this.add.graphics();
        cornerOrb2.fillStyle(COLORS.ACCENT_SECONDARY, 0.08);
        cornerOrb2.fillCircle(width, 0, Math.min(width, height) * 0.25);
    }

    createHeader(width, height) {
        // Layout Kiri: Title di sisi kiri atas dengan style yang lebih modern
        const leftCenterX = width * 0.3; // Pusat area kiri
        const headerY = height * 0.2;

        // Title text - Lebih Besar dan Fancy
        const title = this.add.text(leftCenterX, headerY, 'TARIK TAMBANG', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '42px',
            fontStyle: '900', // Extra Bold
            color: HEX_COLORS.TEXT_MAIN,
            stroke: '#ffffff',
            strokeThickness: 6,
        }).setOrigin(0.5);

        // Subtitle/Tagline
        const subtitle = this.add.text(leftCenterX, headerY + 40, 'Kompetisi Tim Klasik', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '18px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT,
            backgroundColor: '#FFF8F0',
            padding: { x: 10, y: 4 }
        }).setOrigin(0.5);

        // Floating animation for title similar to before but simpler
        this.tweens.add({
            targets: [title, subtitle],
            y: '-=5',
            duration: 3000,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });
    }

    createGameIcon(width, height) {
        // Layout Kiri: Icon Game Besar di bawah judul
        const leftCenterX = width * 0.3;
        const iconY = height * 0.55; // Posisi vertikal di tengah area kiri

        const iconContainer = this.add.container(leftCenterX, iconY);

        // Glow effect belakang icon - ORANGE sesuai permintaan
        const glow = this.add.graphics();
        glow.fillStyle(COLORS.ACCENT, 0.6); // Orange solid, transparan dikit
        glow.fillCircle(0, 0, 110);
        // glow.setBlendMode(Phaser.BlendModes.ADD); // Remove blend mode to keep true orange color
        iconContainer.add(glow);

        this.tweens.add({
            targets: glow,
            alpha: 0.2,
            scaleX: 1.2,
            scaleY: 1.2,
            duration: 2000,
            yoyo: true,
            repeat: -1
        });

        // Game icon - Besar dan Jelas (Transparent)
        const icon = this.add.image(0, 0, 'game_icon_tw')
            .setDisplaySize(200, 200);
        iconContainer.add(icon);

        // Gentle floating animation
        this.tweens.add({
            targets: iconContainer,
            y: iconY - 10,
            duration: 2500,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });
    }

    createMainCard(width, height) {
        // Layout Kanan: Panel Settings Vertikal
        const panelWidth = Math.min(width * 0.45, 500); // 45% lebar layar
        const panelHeight = height * 0.85; // Tinggi hampir penuh
        const panelX = width * 0.7; // Pusat area kanan
        const panelY = height / 2;

        // Container Panel
        const cardContainer = this.add.container(panelX, panelY);

        // Glassmorphism background
        const bg = this.add.graphics();
        bg.fillStyle(COLORS.PANEL_BG, 0.9); // Semi-transparent white
        bg.fillRoundedRect(-panelWidth / 2, -panelHeight / 2, panelWidth, panelHeight, 30);
        cardContainer.add(bg);

        // Stroke border
        const border = this.add.graphics();
        border.lineStyle(2, COLORS.PANEL_BORDER, 0.5);
        border.strokeRoundedRect(-panelWidth / 2, -panelHeight / 2, panelWidth, panelHeight, 30);
        cardContainer.add(border);

        // Header Panel "PENGATURAN" - Background Orange
        const headerBg = this.add.graphics();
        headerBg.fillStyle(COLORS.ACCENT, 1); // Orange Color
        headerBg.fillRoundedRect(-panelWidth / 2 + 10, -panelHeight / 2 + 10, panelWidth - 20, 60, 20);
        cardContainer.add(headerBg);

        const headerText = this.add.text(0, -panelHeight / 2 + 40, '⚙️ PENGATURAN', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '20px',
            fontStyle: 'bold',
            color: '#ffffff', // White text
            letterSpacing: 2
        }).setOrigin(0.5);
        cardContainer.add(headerText);

        // Content Position Calculations
        const startY = -panelHeight / 2 + 100;
        const spacing = 150; // Jarak antar section

        // Section 1: PIN Code
        this.createPinSection(cardContainer, 0, startY, panelWidth - 80);

        // Divider 1
        this.createDivider(cardContainer, 0, startY + 100, panelWidth - 120);

        // Section 2: Question Count
        this.createQuestionSection(cardContainer, 0, startY + spacing, panelWidth - 80);

        // Divider 2
        this.createDivider(cardContainer, 0, startY + spacing + 100, panelWidth - 120);

        // Section 3: Duration
        this.createDurationSection(cardContainer, 0, startY + spacing * 2, panelWidth - 80);

        // Entrance animation
        cardContainer.x = width + panelWidth; // Start from outside right
        this.tweens.add({
            targets: cardContainer,
            x: panelX,
            duration: 600,
            ease: 'Power2', // Smooth slide in
            delay: 100
        });
    }

    createDivider(container, x, y, width) {
        const divider = this.add.graphics();

        // Modern Dashed Line Style
        const dashLength = 5;
        const gapLength = 5;
        const color = COLORS.PANEL_BORDER;
        const alpha = 0.4;

        divider.lineStyle(2, color, alpha);

        const startX = x - width / 2;
        const endX = x + width / 2;
        let currentX = startX;

        // Draw dashes manually
        while (currentX < endX) {
            const drawLength = Math.min(dashLength, endX - currentX);
            divider.moveTo(currentX, y);
            divider.lineTo(currentX + drawLength, y);
            currentX += dashLength + gapLength;
        }

        divider.strokePath();
        container.add(divider);
    }

    createPinSection(container, x, y, width) {
        // Section label - CENTER ALIGNED
        const label = this.add.text(0, y, '🔑 KODE PIN', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px', // Slightly larger
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_LIGHT,
            align: 'center'
        }).setOrigin(0.5);
        container.add(label);

        // PIN display box
        const pinBoxWidth = Math.min(width * 0.65, 280);
        const pinBoxHeight = 48;
        const pinBoxY = y + 40; // Jarak Label-Field LEBIH RAPAT (sebelumnya 55)

        // PIN box background
        const pinBg = this.add.graphics();
        pinBg.fillStyle(COLORS.BACKGROUND, 1);
        pinBg.lineStyle(2, COLORS.ACCENT, 0.6);
        pinBg.fillRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
        pinBg.strokeRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
        container.add(pinBg);

        // PIN text display
        this.pinText = this.add.text(x, pinBoxY, '_ _ _ _ _ _', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MUTED,
            letterSpacing: 6,
        }).setOrigin(0.5);
        container.add(this.pinText);

        // Interactive area
        const hitArea = this.add.rectangle(x, pinBoxY, pinBoxWidth, pinBoxHeight)
            .setInteractive({ useHandCursor: true });
        container.add(hitArea);

        hitArea.on('pointerover', () => {
            pinBg.clear();
            pinBg.fillStyle(COLORS.BACKGROUND, 1);
            pinBg.lineStyle(3, COLORS.ACCENT, 1);
            pinBg.fillRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
            pinBg.strokeRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
        });

        hitArea.on('pointerout', () => {
            pinBg.clear();
            pinBg.fillStyle(COLORS.BACKGROUND, 1);
            pinBg.lineStyle(2, COLORS.ACCENT, 0.6);
            pinBg.fillRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
            pinBg.strokeRoundedRect(x - pinBoxWidth / 2, pinBoxY - pinBoxHeight / 2, pinBoxWidth, pinBoxHeight, 24);
        });
        hitArea.on('pointerdown', () => this.showPinInputModal());
    }

    createQuestionSection(container, x, y, width) {
        // Label - CENTER ALIGNED
        const label = this.add.text(0, y, '❓ JUMLAH SOAL', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_LIGHT,
            align: 'center'
        }).setOrigin(0.5);
        container.add(label);

        const buttonsY = y + 40; // Jarak Label-Field LEBIH RAPAT (sebelumnya 60)
        const buttonWidth = 48;
        const customButtonWidth = 70; // Wider for "Custom" text
        const buttonGap = 10;
        const options = [5, 10, 15, 20];
        const totalWidth = (buttonWidth + buttonGap) * options.length + customButtonWidth;
        const startX = x - totalWidth / 2 + buttonWidth / 2;

        this.questionButtons = [];

        options.forEach((count, index) => {
            const btnX = startX + index * (buttonWidth + buttonGap);
            const isSelected = !this.isCustomQuestion && this.config.totalQuestions === count;

            const btn = this.createOptionButton(
                container,
                btnX,
                buttonsY,
                count.toString(),
                isSelected,
                COLORS.TEAM_BLUE,
                () => {
                    this.isCustomQuestion = false;
                    this.config.totalQuestions = count;
                    this.updateQuestionButtons();
                },
                buttonWidth
            );

            this.questionButtons.push({ btn, value: count, isCustom: false });
        });

        // Custom button - wider
        const customBtnX = startX + options.length * (buttonWidth + buttonGap) + (customButtonWidth - buttonWidth) / 2;
        const customBtn = this.createOptionButton(
            container,
            customBtnX,
            buttonsY,
            'Custom',
            this.isCustomQuestion,
            COLORS.TEAM_BLUE,
            () => this.showCustomQuestionModal(),
            customButtonWidth
        );
        this.questionButtons.push({ btn: customBtn, value: 'custom', isCustom: true });
    }

    createDurationSection(container, x, y, width) {
        // Label - CENTER ALIGNED
        const label = this.add.text(0, y, '⏱️ DURASI PERMAINAN', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_LIGHT,
            align: 'center'
        }).setOrigin(0.5);
        container.add(label);

        const buttonsY = y + 40; // Jarak Label-Field LEBIH RAPAT (sebelumnya 60)
        const buttonWidth = 50;
        const customButtonWidth = 70; // Wider for "Custom" text
        const buttonGap = 8;
        const regularOptions = this.durationOptions.slice(0, -1); // All except Custom
        const totalWidth = (buttonWidth + buttonGap) * regularOptions.length + customButtonWidth;
        const startX = x - totalWidth / 2 + buttonWidth / 2;

        this.durationButtons = [];

        regularOptions.forEach((option, index) => {
            const btnX = startX + index * (buttonWidth + buttonGap);
            const isSelected = !this.isCustomDuration && this.config.gameDuration === option.value;

            const btn = this.createOptionButton(
                container,
                btnX,
                buttonsY,
                option.label,
                isSelected,
                COLORS.ACCENT_SECONDARY,
                () => {
                    this.isCustomDuration = false;
                    this.config.gameDuration = option.value;
                    this.updateDurationButtons();
                },
                buttonWidth
            );

            this.durationButtons.push({ btn, value: option.value, isCustom: false });
        });

        // Custom button - wider
        const customBtnX = startX + regularOptions.length * (buttonWidth + buttonGap) + (customButtonWidth - buttonWidth) / 2;
        const customBtn = this.createOptionButton(
            container,
            customBtnX,
            buttonsY,
            'Custom',
            this.isCustomDuration,
            COLORS.ACCENT_SECONDARY,
            () => this.showCustomDurationModal(),
            customButtonWidth
        );
        this.durationButtons.push({ btn: customBtn, value: 'custom', isCustom: true });
    }

    createOptionButton(container, x, y, text, isSelected, accentColor, callback, btnWidth = 50) {
        const btnContainer = this.add.container(x, y);
        container.add(btnContainer);

        const btnHeight = 38;

        // Button background
        const bg = this.add.graphics();
        this.drawOptionButtonBg(bg, btnWidth, btnHeight, isSelected, accentColor);
        btnContainer.add(bg);
        btnContainer.setData('bg', bg);
        btnContainer.setData('btnWidth', btnWidth);
        btnContainer.setData('btnHeight', btnHeight);
        btnContainer.setData('accentColor', accentColor);

        // Button text
        const label = this.add.text(0, 0, text, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '12px',
            fontStyle: 'bold',
            color: isSelected ? '#ffffff' : HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);
        btnContainer.add(label);
        btnContainer.setData('label', label);
        btnContainer.setData('originalText', text);

        // Interactive area
        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight)
            .setInteractive({ useHandCursor: true });
        btnContainer.add(hitArea);

        hitArea.on('pointerover', () => {
            if (!btnContainer.getData('isSelected')) {
                this.tweens.add({
                    targets: btnContainer,
                    scaleX: 1.05,
                    scaleY: 1.05,
                    duration: 100,
                });
            }
        });

        hitArea.on('pointerout', () => {
            this.tweens.add({
                targets: btnContainer,
                scaleX: 1,
                scaleY: 1,
                duration: 100,
            });
        });

        hitArea.on('pointerdown', () => {
            this.tweens.add({
                targets: btnContainer,
                scaleX: 0.95,
                scaleY: 0.95,
                duration: 80,
                yoyo: true,
                onComplete: callback,
            });
        });

        btnContainer.setData('isSelected', isSelected);
        return btnContainer;
    }

    drawOptionButtonBg(graphics, width, height, isSelected, accentColor) {
        graphics.clear();
        if (isSelected) {
            // Selected state - filled with accent color
            graphics.fillStyle(accentColor, 1);
            graphics.fillRoundedRect(-width / 2, -height / 2, width, height, 19);

            // Subtle inner glow
            graphics.lineStyle(2, 0xffffff, 0.3);
            graphics.strokeRoundedRect(-width / 2 + 2, -height / 2 + 2, width - 4, height - 4, 17);
        } else {
            // Unselected state - outlined
            graphics.fillStyle(COLORS.PANEL_BG, 1);
            graphics.lineStyle(2, accentColor, 0.4);
            graphics.fillRoundedRect(-width / 2, -height / 2, width, height, 19);
            graphics.strokeRoundedRect(-width / 2, -height / 2, width, height, 19);
        }
    }

    updateQuestionButtons() {
        this.questionButtons.forEach(({ btn, value, isCustom }) => {
            const bg = btn.getData('bg');
            const label = btn.getData('label');
            const w = btn.getData('btnWidth');
            const h = btn.getData('btnHeight');
            const color = btn.getData('accentColor');

            let isSelected;
            if (isCustom) {
                isSelected = this.isCustomQuestion;
                if (this.isCustomQuestion && this.customQuestionCount) {
                    label.setText(this.customQuestionCount);
                } else {
                    label.setText('Custom');
                }
            } else {
                isSelected = !this.isCustomQuestion && this.config.totalQuestions === value;
            }

            this.drawOptionButtonBg(bg, w, h, isSelected, color);
            label.setColor(isSelected ? '#ffffff' : HEX_COLORS.TEXT_MAIN);
            btn.setData('isSelected', isSelected);
        });
    }

    updateDurationButtons() {
        this.durationButtons.forEach(({ btn, value, isCustom }) => {
            const bg = btn.getData('bg');
            const label = btn.getData('label');
            const w = btn.getData('btnWidth');
            const h = btn.getData('btnHeight');
            const color = btn.getData('accentColor');

            let isSelected;
            if (isCustom) {
                isSelected = this.isCustomDuration;
                if (this.isCustomDuration && this.customDuration) {
                    label.setText(this.customDuration + 'm');
                } else {
                    label.setText('Custom');
                }
            } else {
                isSelected = !this.isCustomDuration && this.config.gameDuration === value;
            }

            this.drawOptionButtonBg(bg, w, h, isSelected, color);
            label.setColor(isSelected ? '#ffffff' : HEX_COLORS.TEXT_MAIN);
            btn.setData('isSelected', isSelected);
        });
    }

    showPinInputModal() {
        const { width, height } = this.cameras.main;

        // Overlay
        this.modalOverlay = this.add.graphics();
        this.modalOverlay.fillStyle(0x000000, 0.7);
        this.modalOverlay.fillRect(0, 0, width, height);

        // Modal container
        this.modalContainer = this.add.container(width / 2, height / 2);

        const modalWidth = 300;
        const modalHeight = 480; // Increased height to fit OK button inside

        // Modal background
        const modalShadow = this.add.graphics();
        modalShadow.fillStyle(0x000000, 0.15);
        modalShadow.fillRoundedRect(-modalWidth / 2 + 6, -modalHeight / 2 + 6, modalWidth, modalHeight, 24);
        this.modalContainer.add(modalShadow);

        const modalBg = this.add.graphics();
        modalBg.fillStyle(COLORS.PANEL_BG, 0.98);
        modalBg.lineStyle(2, COLORS.ACCENT, 0.5);
        modalBg.fillRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        modalBg.strokeRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        this.modalContainer.add(modalBg);

        // Modal title
        const titleY = -modalHeight / 2 + 35;
        const modalTitle = this.add.text(0, titleY, '🔑 Masukkan Kode PIN', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '15px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);
        this.modalContainer.add(modalTitle);

        // PIN display
        this.tempPin = this.config.pinCode || '';
        const displayY = -modalHeight / 2 + 85;

        const pinDisplayBg = this.add.graphics();
        pinDisplayBg.fillStyle(COLORS.BACKGROUND, 1);
        pinDisplayBg.lineStyle(2, COLORS.ACCENT, 0.4);
        pinDisplayBg.fillRoundedRect(-105, displayY - 22, 210, 44, 14);
        pinDisplayBg.strokeRoundedRect(-105, displayY - 22, 210, 44, 14);
        this.modalContainer.add(pinDisplayBg);

        this.pinDisplayText = this.add.text(0, displayY, this.formatPinDisplay(this.tempPin), {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT,
            letterSpacing: 4,
        }).setOrigin(0.5);
        this.modalContainer.add(this.pinDisplayText);

        // Number pad - with more spacing from field
        const numPadLayout = [
            ['1', '2', '3'],
            ['4', '5', '6'],
            ['7', '8', '9'],
            ['C', '0', '⌫'],
        ];

        const btnSize = 55;
        const btnGap = 10;
        const keypadStartY = -modalHeight / 2 + 150; // More spacing from field

        numPadLayout.forEach((row, rowIndex) => {
            row.forEach((key, colIndex) => {
                const btnX = (colIndex - 1) * (btnSize + btnGap);
                const btnY = keypadStartY + rowIndex * (btnSize + btnGap);
                this.createNumPadButton(btnX, btnY, key, btnSize, () => {
                    if (key === 'C') {
                        this.tempPin = '';
                    } else if (key === '⌫') {
                        this.tempPin = this.tempPin.slice(0, -1);
                    } else {
                        if (this.tempPin.length < 6) {
                            this.tempPin += key;
                        }
                    }
                    this.pinDisplayText.setText(this.formatPinDisplay(this.tempPin));
                });
            });
        });

        // Confirm button - positioned below numpad, inside modal
        const confirmY = keypadStartY + 4 * (btnSize + btnGap) + 25;
        this.createModalActionButton(0, confirmY, '✓ OK', COLORS.SUCCESS, () => {
            if (this.tempPin.length > 0) {
                this.config.pinCode = this.tempPin;
                this.updatePinDisplay();
            }
            this.closeModal();
        }, 120);

        // Close button - moved more inward from corner
        const closeBtn = this.add.text(modalWidth / 2 - 30, -modalHeight / 2 + 25, '✕', {
            fontFamily: 'Arial',
            fontSize: '20px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5).setInteractive({ useHandCursor: true });
        this.modalContainer.add(closeBtn);

        closeBtn.on('pointerover', () => closeBtn.setColor(HEX_COLORS.ERROR));
        closeBtn.on('pointerout', () => closeBtn.setColor(HEX_COLORS.TEXT_MUTED));
        closeBtn.on('pointerdown', () => this.closeModal());

        // Entrance animation
        this.modalContainer.setScale(0.8);
        this.modalContainer.setAlpha(0);
        this.tweens.add({
            targets: this.modalContainer,
            scaleX: 1,
            scaleY: 1,
            alpha: 1,
            duration: 250,
            ease: 'Back.easeOut',
        });
    }

    showCustomQuestionModal() {
        const { width, height } = this.cameras.main;

        this.modalOverlay = this.add.graphics();
        this.modalOverlay.fillStyle(0x000000, 0.7);
        this.modalOverlay.fillRect(0, 0, width, height);

        this.modalContainer = this.add.container(width / 2, height / 2);

        const modalWidth = 280;
        const modalHeight = 450; // Increased height to fit OK button inside

        const modalShadow = this.add.graphics();
        modalShadow.fillStyle(0x000000, 0.15);
        modalShadow.fillRoundedRect(-modalWidth / 2 + 6, -modalHeight / 2 + 6, modalWidth, modalHeight, 24);
        this.modalContainer.add(modalShadow);

        const modalBg = this.add.graphics();
        modalBg.fillStyle(COLORS.PANEL_BG, 0.98);
        modalBg.lineStyle(2, COLORS.TEAM_BLUE, 0.5);
        modalBg.fillRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        modalBg.strokeRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        this.modalContainer.add(modalBg);

        // Title
        const titleY = -modalHeight / 2 + 35;
        const modalTitle = this.add.text(0, titleY, '❓ Jumlah Soal', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '15px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);
        this.modalContainer.add(modalTitle);

        // Number display
        this.tempNumber = this.customQuestionCount || '';
        const displayY = -modalHeight / 2 + 85;

        const displayBg = this.add.graphics();
        displayBg.fillStyle(COLORS.BACKGROUND, 1);
        displayBg.lineStyle(2, COLORS.TEAM_BLUE, 0.4);
        displayBg.fillRoundedRect(-55, displayY - 22, 110, 44, 12);
        displayBg.strokeRoundedRect(-55, displayY - 22, 110, 44, 12);
        this.modalContainer.add(displayBg);

        this.numberDisplay = this.add.text(0, displayY, this.tempNumber || '0', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEAM_BLUE,
        }).setOrigin(0.5);
        this.modalContainer.add(this.numberDisplay);

        // Number pad - with more spacing from field
        const numPadLayout = [
            ['1', '2', '3'],
            ['4', '5', '6'],
            ['7', '8', '9'],
            ['C', '0', '⌫'],
        ];

        const btnSize = 52;
        const btnGap = 8;
        const keypadStartY = -modalHeight / 2 + 145; // More spacing from field

        numPadLayout.forEach((row, rowIndex) => {
            row.forEach((key, colIndex) => {
                const btnX = (colIndex - 1) * (btnSize + btnGap);
                const btnY = keypadStartY + rowIndex * (btnSize + btnGap);
                this.createNumPadButton(btnX, btnY, key, btnSize, () => {
                    if (key === 'C') {
                        this.tempNumber = '';
                    } else if (key === '⌫') {
                        this.tempNumber = this.tempNumber.slice(0, -1);
                    } else {
                        if (this.tempNumber.length < 3) {
                            this.tempNumber += key;
                        }
                    }
                    this.numberDisplay.setText(this.tempNumber || '0');
                });
            });
        });

        // Confirm button - positioned inside modal
        const confirmY = keypadStartY + 4 * (btnSize + btnGap) + 25;
        this.createModalActionButton(0, confirmY, '✓ OK', COLORS.SUCCESS, () => {
            const num = parseInt(this.tempNumber);
            if (num > 0 && num <= 100) {
                this.customQuestionCount = this.tempNumber;
                this.config.totalQuestions = num;
                this.isCustomQuestion = true;
                this.updateQuestionButtons();
            }
            this.closeModal();
        }, 120);

        // Close button - moved more inward from corner
        const closeBtn = this.add.text(modalWidth / 2 - 28, -modalHeight / 2 + 24, '✕', {
            fontFamily: 'Arial',
            fontSize: '18px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5).setInteractive({ useHandCursor: true });
        this.modalContainer.add(closeBtn);

        closeBtn.on('pointerover', () => closeBtn.setColor(HEX_COLORS.ERROR));
        closeBtn.on('pointerout', () => closeBtn.setColor(HEX_COLORS.TEXT_MUTED));
        closeBtn.on('pointerdown', () => this.closeModal());

        // Entrance animation
        this.modalContainer.setScale(0.8);
        this.modalContainer.setAlpha(0);
        this.tweens.add({
            targets: this.modalContainer,
            scaleX: 1,
            scaleY: 1,
            alpha: 1,
            duration: 250,
            ease: 'Back.easeOut',
        });
    }

    showCustomDurationModal() {
        const { width, height } = this.cameras.main;

        this.modalOverlay = this.add.graphics();
        this.modalOverlay.fillStyle(0x000000, 0.7);
        this.modalOverlay.fillRect(0, 0, width, height);

        this.modalContainer = this.add.container(width / 2, height / 2);

        const modalWidth = 280;
        const modalHeight = 475; // Increased height to fit OK button inside

        const modalShadow = this.add.graphics();
        modalShadow.fillStyle(0x000000, 0.15);
        modalShadow.fillRoundedRect(-modalWidth / 2 + 6, -modalHeight / 2 + 6, modalWidth, modalHeight, 24);
        this.modalContainer.add(modalShadow);

        const modalBg = this.add.graphics();
        modalBg.fillStyle(COLORS.PANEL_BG, 0.98);
        modalBg.lineStyle(2, COLORS.ACCENT_SECONDARY, 0.5);
        modalBg.fillRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        modalBg.strokeRoundedRect(-modalWidth / 2, -modalHeight / 2, modalWidth, modalHeight, 20);
        this.modalContainer.add(modalBg);

        // Title
        const titleY = -modalHeight / 2 + 35;
        const modalTitle = this.add.text(0, titleY, '⏱️ Durasi (Menit)', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '15px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);
        this.modalContainer.add(modalTitle);

        // Number display
        this.tempNumber = this.customDuration || '';
        const displayY = -modalHeight / 2 + 85;

        const displayBg = this.add.graphics();
        displayBg.fillStyle(COLORS.BACKGROUND, 1);
        displayBg.lineStyle(2, COLORS.ACCENT_SECONDARY, 0.4);
        displayBg.fillRoundedRect(-55, displayY - 22, 110, 44, 12);
        displayBg.strokeRoundedRect(-55, displayY - 22, 110, 44, 12);
        this.modalContainer.add(displayBg);

        this.numberDisplay = this.add.text(0, displayY, this.tempNumber || '0', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT_SECONDARY,
        }).setOrigin(0.5);
        this.modalContainer.add(this.numberDisplay);

        // Minutes label
        const minLabel = this.add.text(0, displayY + 28, 'menit', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '11px',
            color: HEX_COLORS.TEXT_LIGHT,
        }).setOrigin(0.5);
        this.modalContainer.add(minLabel);

        // Number pad - with more spacing from field
        const numPadLayout = [
            ['1', '2', '3'],
            ['4', '5', '6'],
            ['7', '8', '9'],
            ['C', '0', '⌫'],
        ];

        const btnSize = 52;
        const btnGap = 8;
        const keypadStartY = -modalHeight / 2 + 160; // More spacing from field

        numPadLayout.forEach((row, rowIndex) => {
            row.forEach((key, colIndex) => {
                const btnX = (colIndex - 1) * (btnSize + btnGap);
                const btnY = keypadStartY + rowIndex * (btnSize + btnGap);
                this.createNumPadButton(btnX, btnY, key, btnSize, () => {
                    if (key === 'C') {
                        this.tempNumber = '';
                    } else if (key === '⌫') {
                        this.tempNumber = this.tempNumber.slice(0, -1);
                    } else {
                        if (this.tempNumber.length < 3) {
                            this.tempNumber += key;
                        }
                    }
                    this.numberDisplay.setText(this.tempNumber || '0');
                });
            });
        });

        // Confirm button - positioned inside modal
        const confirmY = keypadStartY + 4 * (btnSize + btnGap) + 25;
        this.createModalActionButton(0, confirmY, '✓ OK', COLORS.SUCCESS, () => {
            const num = parseInt(this.tempNumber);
            if (num > 0 && num <= 180) {
                this.customDuration = this.tempNumber;
                this.config.gameDuration = num * 60;
                this.isCustomDuration = true;
                this.updateDurationButtons();
            }
            this.closeModal();
        }, 120);

        // Close button - moved more inward from corner
        const closeBtn = this.add.text(modalWidth / 2 - 28, -modalHeight / 2 + 24, '✕', {
            fontFamily: 'Arial',
            fontSize: '18px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5).setInteractive({ useHandCursor: true });
        this.modalContainer.add(closeBtn);

        closeBtn.on('pointerover', () => closeBtn.setColor(HEX_COLORS.ERROR));
        closeBtn.on('pointerout', () => closeBtn.setColor(HEX_COLORS.TEXT_MUTED));
        closeBtn.on('pointerdown', () => this.closeModal());

        // Entrance animation
        this.modalContainer.setScale(0.8);
        this.modalContainer.setAlpha(0);
        this.tweens.add({
            targets: this.modalContainer,
            scaleX: 1,
            scaleY: 1,
            alpha: 1,
            duration: 250,
            ease: 'Back.easeOut',
        });
    }

    formatPinDisplay(pin) {
        const display = pin.padEnd(6, '_').split('').join(' ');
        return display;
    }

    updatePinDisplay() {
        if (this.pinText) {
            if (this.config.pinCode) {
                this.pinText.setText(this.config.pinCode.split('').join(' '));
                this.pinText.setColor(HEX_COLORS.ACCENT);
            } else {
                this.pinText.setText('_ _ _ _ _ _');
                this.pinText.setColor(HEX_COLORS.TEXT_MUTED);
            }
        }
    }

    createNumPadButton(x, y, label, size, callback) {
        const container = this.add.container(x, y);
        this.modalContainer.add(container);

        const bg = this.add.graphics();
        let bgColor, textColor;

        if (label === 'C') {
            bgColor = COLORS.ERROR;
            textColor = '#ffffff';
        } else if (label === '⌫') {
            bgColor = COLORS.TEXT_MUTED;
            textColor = '#ffffff';
        } else {
            bgColor = COLORS.BACKGROUND;
            textColor = HEX_COLORS.TEXT_MAIN;
        }

        bg.fillStyle(bgColor, 1);
        if (label !== 'C' && label !== '⌫') {
            bg.lineStyle(2, COLORS.PANEL_BORDER, 1);
        }
        bg.fillRoundedRect(-size / 2, -size / 2, size, size, 14);
        if (label !== 'C' && label !== '⌫') {
            bg.strokeRoundedRect(-size / 2, -size / 2, size, size, 14);
        }
        container.add(bg);

        const text = this.add.text(0, 0, label, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '20px',
            fontStyle: 'bold',
            color: textColor,
        }).setOrigin(0.5);
        container.add(text);

        const hitArea = this.add.rectangle(0, 0, size, size).setInteractive({ useHandCursor: true });
        container.add(hitArea);

        hitArea.on('pointerdown', () => {
            this.tweens.add({
                targets: container,
                scaleX: 0.9,
                scaleY: 0.9,
                duration: 60,
                yoyo: true,
                onComplete: callback,
            });
        });
    }

    createModalActionButton(x, y, text, color, callback, btnWidth = 140) {
        const container = this.add.container(x, y);
        this.modalContainer.add(container);

        const btnHeight = 44;

        const shadow = this.add.graphics();
        shadow.fillStyle(color, 0.3);
        shadow.fillRoundedRect(-btnWidth / 2 + 2, -btnHeight / 2 + 2, btnWidth, btnHeight, 22);
        container.add(shadow);

        const bg = this.add.graphics();
        bg.fillStyle(color, 1);
        bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 22);
        container.add(bg);

        const label = this.add.text(0, 0, text, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '15px',
            fontStyle: 'bold',
            color: '#ffffff',
        }).setOrigin(0.5);
        container.add(label);

        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight).setInteractive({ useHandCursor: true });
        container.add(hitArea);

        hitArea.on('pointerover', () => {
            this.tweens.add({
                targets: container,
                scaleX: 1.05,
                scaleY: 1.05,
                duration: 100,
            });
        });

        hitArea.on('pointerout', () => {
            this.tweens.add({
                targets: container,
                scaleX: 1,
                scaleY: 1,
                duration: 100,
            });
        });

        hitArea.on('pointerdown', () => {
            this.tweens.add({
                targets: container,
                scaleX: 0.95,
                scaleY: 0.95,
                duration: 80,
                yoyo: true,
                onComplete: callback,
            });
        });

        return container;
    }

    closeModal() {
        if (this.modalOverlay) {
            this.tweens.add({
                targets: this.modalOverlay,
                alpha: 0,
                duration: 200,
                onComplete: () => {
                    this.modalOverlay.destroy();
                    this.modalOverlay = null;
                }
            });
        }

        if (this.modalContainer) {
            this.tweens.add({
                targets: this.modalContainer,
                scaleX: 0.8,
                scaleY: 0.8,
                alpha: 0,
                duration: 200,
                ease: 'Power2',
                onComplete: () => {
                    this.modalContainer.destroy();
                    this.modalContainer = null;
                }
            });
        }
    }

    createActionButtons(width, height) {
        // Tombol disesuaikan dengan layout baru

        // 1. Back Button global di pojok kiri atas
        this.createActionButton(
            100, 50, // Top Left Corner
            '← KEMBALI',
            COLORS.TEXT_LIGHT,
            () => {
                this.cameras.main.fadeOut(300);
                this.cameras.main.once('camerafadeoutcomplete', () => {
                    this.scene.start(SCENES.MENU);
                });
            },
            140
        );

        // 2. Start Button di dalam area panel kanan
        const panelX = width * 0.7;
        const startBtnY = height * 0.85;

        // Simpan referensi tombol untuk animasi
        const startBtn = this.createActionButton(
            panelX,
            startBtnY,
            '🚀 MULAI PERMAINAN',
            COLORS.SUCCESS,
            () => this.startGame(),
            280,
            true
        );

        // Animasi Masuk: Slide dari Kanan (seperti panel)
        startBtn.x = width + 280; // Start outside right
        this.tweens.add({
            targets: startBtn,
            x: panelX,
            duration: 600,
            ease: 'Power2', // Smooth slide in
            delay: 200 // Sedikit delay setelah panel masuk
        });
    }

    createActionButton(x, y, text, color, callback, btnWidth = 150, pulse = false) {
        const container = this.add.container(x, y);
        const btnHeight = 50;

        // Button shadow
        const shadow = this.add.graphics();
        shadow.fillStyle(color, 0.3);
        shadow.fillRoundedRect(-btnWidth / 2 + 3, -btnHeight / 2 + 3, btnWidth, btnHeight, 25);
        container.add(shadow);

        // Main button
        const bg = this.add.graphics();
        bg.fillStyle(color, 1);
        bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 25);
        container.add(bg);

        const label = this.add.text(0, 0, text, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '16px',
            fontStyle: 'bold',
            color: '#ffffff',
        }).setOrigin(0.5);
        container.add(label);

        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight).setInteractive({ useHandCursor: true });
        container.add(hitArea);

        hitArea.on('pointerover', () => {
            this.tweens.add({
                targets: container,
                scaleX: 1.05,
                scaleY: 1.05,
                duration: 100,
            });
        });

        hitArea.on('pointerout', () => {
            this.tweens.add({
                targets: container,
                scaleX: 1,
                scaleY: 1,
                duration: 100,
            });
        });

        hitArea.on('pointerdown', () => {
            this.tweens.add({
                targets: container,
                scaleX: 0.95,
                scaleY: 0.95,
                duration: 80,
                yoyo: true,
                onComplete: callback,
            });
        });

        // Pulse animation for primary button
        if (pulse) {
            this.tweens.add({
                targets: container,
                scaleX: 1.03,
                scaleY: 1.03,
                duration: 1500,
                ease: 'Sine.easeInOut',
                yoyo: true,
                repeat: -1,
            });
        }

        return container;
    }

    startGame() {
        // Validate PIN
        if (!this.config.pinCode || this.config.pinCode.length < 1) {
            this.showToast('⚠️ Masukkan kode PIN terlebih dahulu!', COLORS.WARNING);
            return;
        }

        if (this.config.totalQuestions <= 0) {
            this.showToast('⚠️ Pilih jumlah soal!', COLORS.WARNING);
            return;
        }

        if (this.config.gameDuration <= 0) {
            this.showToast('⚠️ Pilih durasi permainan!', COLORS.WARNING);
            return;
        }

        // Prepare game config with proper mapping
        const gameConfig = {
            ...this.config,
            timePerQuestion: this.config.gameDuration, // Map gameDuration to timePerQuestion
            gameMode: 'race', // Default game mode
            topicId: 1, // Default topic (will be overridden by PIN-based loading)
            topicName: 'Permainan', // Default topic name
        };

        // Proceed to game
        this.cameras.main.fadeOut(400);

        this.cameras.main.once('camerafadeoutcomplete', () => {
            this.scene.start(SCENES.GAME, { config: gameConfig });
        });
    }

    showToast(message, color = COLORS.ERROR) {
        const { width, height } = this.cameras.main;

        const toastContainer = this.add.container(width / 2, height);

        const bg = this.add.graphics();
        bg.fillStyle(color, 0.95);
        bg.fillRoundedRect(-180, -22, 360, 44, 22);
        toastContainer.add(bg);

        const text = this.add.text(0, 0, message, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px',
            fontStyle: 'bold',
            color: '#ffffff',
        }).setOrigin(0.5);
        toastContainer.add(text);

        // Slide in animation
        this.tweens.add({
            targets: toastContainer,
            y: height - 75,
            duration: 300,
            ease: 'Back.easeOut',
            onComplete: () => {
                this.time.delayedCall(2500, () => {
                    this.tweens.add({
                        targets: toastContainer,
                        y: height + 50,
                        alpha: 0,
                        duration: 300,
                        onComplete: () => toastContainer.destroy(),
                    });
                });
            },
        });
    }

    createFullscreenButton(width, height) {
        const isFullscreen = this.scale.isFullscreen;
        const text = isFullscreen ? 'Keluar Fullscreen' : 'Masuk Fullscreen';

        // Position button at top right with comfortable margin
        const btnWidth = 145;
        const btnHeight = 34;
        const marginRight = 25;
        const marginTop = 25;

        // Container positioned at button center
        this.fsButtonContainer = this.add.container(width - marginRight - btnWidth / 2, marginTop);

        // Background
        const bg = this.add.graphics();
        this.fsButtonBg = bg;
        this.fsBtnWidth = btnWidth;
        this.fsBtnHeight = btnHeight;

        // Draw initial background
        this.drawFullscreenButtonBg(isFullscreen);

        this.fsButtonContainer.add(bg);

        // Label - centered in container
        const label = this.add.text(0, 0, text, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '12px',
            fontStyle: 'bold',
            color: '#ffffff'
        }).setOrigin(0.5, 0.5);
        this.fsLabel = label;
        this.fsButtonContainer.add(label);

        // Interactive Area - centered
        const hitArea = this.add.rectangle(0, 0, btnWidth, btnHeight)
            .setInteractive({ useHandCursor: true });
        this.fsButtonContainer.add(hitArea);

        hitArea.on('pointerdown', () => {
            if (this.scale.isFullscreen) {
                this.scale.stopFullscreen();
            } else {
                this.scale.startFullscreen();
            }
        });

        // Listen for fullscreen changes
        if (!this.fullscreenListenerAdded) {
            this.scale.on('enterfullscreen', () => this.updateFullscreenButtonState(true));
            this.scale.on('leavefullscreen', () => this.updateFullscreenButtonState(false));
            this.fullscreenListenerAdded = true;
        }
    }

    drawFullscreenButtonBg(isFullscreen) {
        if (!this.fsButtonBg) return;
        this.fsButtonBg.clear();

        const color = isFullscreen ? COLORS.ERROR : COLORS.ACCENT_SECONDARY;
        const w = this.fsBtnWidth;
        const h = this.fsBtnHeight;

        this.fsButtonBg.fillStyle(color, 0.95);
        this.fsButtonBg.fillRoundedRect(-w / 2, -h / 2, w, h, 17);
    }

    updateFullscreenButtonState(isFullscreen) {
        if (!this.fsLabel || !this.fsButtonContainer) return;

        const text = isFullscreen ? 'Keluar Fullscreen' : 'Masuk Fullscreen';
        this.fsLabel.setText(text);
        this.drawFullscreenButtonBg(isFullscreen);
    }

    shutdown() {
        this.scale.off('resize', this.handleResize, this);
    }
}
