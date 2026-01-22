import Phaser from 'phaser';
import { SCENES, COLORS, HEX_COLORS } from '../utils/constants.js';

/**
 * ResultScene - Show game results with cheerful celebration design
 */
export default class ResultScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.RESULT });
    }

    init(data) {
        this.config = data.config || {};
        this.result = data.result || {
            winner: 'draw',
            teamBlueScore: 0,
            teamRedScore: 0,
            totalRounds: 0,
            ropePosition: 0,
        };
    }

    create() {
        const { width, height } = this.cameras.main;

        this.cameras.main.fadeIn(400);
        this.cameras.main.setBackgroundColor(COLORS.BACKGROUND);

        this.createBackground(width, height);

        if (this.result.winner !== 'draw') {
            this.createConfetti(width, height);
        }

        this.createHeader(width);
        this.createWinnerAnnouncement(width, height);
        this.createScoreComparison(width, height);
        this.createStatistics(width, height);
        this.createActionButtons(width, height);
    }

    createBackground(width, height) {
        // Gradient overlay
        const gradient = this.add.graphics();
        gradient.fillGradientStyle(
            COLORS.BACKGROUND, COLORS.BACKGROUND,
            COLORS.GRADIENT_START, COLORS.GRADIENT_END,
            0.4
        );
        gradient.fillRect(0, 0, width, height);

        // Celebration bubbles
        const winnerColor = this.result.winner === 'blue' ? COLORS.TEAM_BLUE :
            this.result.winner === 'red' ? COLORS.TEAM_RED : COLORS.ACCENT;

        for (let i = 0; i < 8; i++) {
            const bubble = this.add.graphics();
            bubble.fillStyle(winnerColor, 0.06);
            bubble.fillCircle(
                Phaser.Math.Between(50, width - 50),
                Phaser.Math.Between(50, height - 50),
                Phaser.Math.Between(40, 120)
            );

            // Float animation
            this.tweens.add({
                targets: bubble,
                y: bubble.y - 20,
                duration: Phaser.Math.Between(2000, 3500),
                ease: 'Sine.easeInOut',
                yoyo: true,
                repeat: -1,
            });
        }
    }

    createConfetti(width, height) {
        const colors = [COLORS.ACCENT, COLORS.ACCENT_SECONDARY, COLORS.ACCENT_TERTIARY,
        COLORS.TEAM_BLUE, COLORS.TEAM_RED, COLORS.SUCCESS];

        for (let i = 0; i < 60; i++) {
            const x = Phaser.Math.Between(0, width);
            const color = colors[Phaser.Math.Between(0, colors.length - 1)];
            const size = Phaser.Math.Between(8, 16);

            const particle = this.add.graphics();
            particle.fillStyle(color, 0.9);

            // Random shapes
            if (i % 3 === 0) {
                particle.fillRect(-size / 2, -size / 2, size, size);
            } else if (i % 3 === 1) {
                particle.fillCircle(0, 0, size / 2);
            } else {
                particle.fillTriangle(-size / 2, size / 2, size / 2, size / 2, 0, -size / 2);
            }

            particle.setPosition(x, -30);

            this.tweens.add({
                targets: particle,
                y: height + 50,
                x: x + Phaser.Math.Between(-150, 150),
                rotation: Phaser.Math.DegToRad(Phaser.Math.Between(0, 720)),
                duration: Phaser.Math.Between(2500, 5000),
                delay: Phaser.Math.Between(0, 2500),
                ease: 'Power1',
                repeat: -1,
            });
        }
    }

    createHeader(width) {
        // Trophy icon with glow
        const trophyContainer = this.add.container(width / 2, 55);

        const glow = this.add.graphics();
        glow.fillStyle(COLORS.ACCENT_TERTIARY, 0.2);
        glow.fillCircle(0, 0, 50);
        trophyContainer.add(glow);

        const trophy = this.add.text(0, 0, '🏆', {
            fontSize: '48px',
        }).setOrigin(0.5);
        trophyContainer.add(trophy);

        // Glow pulse
        this.tweens.add({
            targets: glow,
            scaleX: 1.2,
            scaleY: 1.2,
            alpha: 0.3,
            duration: 1200,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });

        // Title
        this.add.text(width / 2, 110, 'HASIL PERTANDINGAN', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '32px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);

        // Topic subtitle
        this.add.text(width / 2, 145, `Topik: ${this.config.topicName || 'Demo'}`, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '16px',
            color: HEX_COLORS.TEXT_LIGHT,
        }).setOrigin(0.5);
    }

    createWinnerAnnouncement(width, height) {
        const { winner } = this.result;

        let winnerText = '';
        let winnerColor, hexWinnerColor;
        let emoji = '';

        if (winner === 'blue') {
            winnerText = `${this.config.teamBlueName || 'Tim Biru'} MENANG!`;
            winnerColor = COLORS.TEAM_BLUE;
            hexWinnerColor = HEX_COLORS.TEAM_BLUE;
            emoji = '🔵 🎉';
        } else if (winner === 'red') {
            winnerText = `${this.config.teamRedName || 'Tim Merah'} MENANG!`;
            winnerColor = COLORS.TEAM_RED;
            hexWinnerColor = HEX_COLORS.TEAM_RED;
            emoji = '🔴 🎉';
        } else {
            winnerText = 'PERTANDINGAN SERI!';
            winnerColor = COLORS.ACCENT;
            hexWinnerColor = HEX_COLORS.ACCENT;
            emoji = '🤝';
        }

        const boxWidth = 550;
        const boxHeight = 110;
        const boxX = width / 2 - boxWidth / 2;
        const boxY = 175;

        // Winner announcement card
        const winnerCard = this.add.graphics();
        winnerCard.fillStyle(COLORS.PANEL_BG, 0.95);
        winnerCard.lineStyle(4, winnerColor, 0.8);
        winnerCard.fillRoundedRect(boxX, boxY, boxWidth, boxHeight, 24);
        winnerCard.strokeRoundedRect(boxX, boxY, boxWidth, boxHeight, 24);

        // Accent strip at top
        const accentStrip = this.add.graphics();
        accentStrip.fillStyle(winnerColor, 0.15);
        accentStrip.fillRoundedRect(boxX + 10, boxY + 10, boxWidth - 20, 35, 12);

        // Emoji
        this.add.text(width / 2, boxY + 28, emoji, {
            fontSize: '24px',
        }).setOrigin(0.5);

        // Winner text
        const winnerTextObj = this.add.text(width / 2, boxY + 72, winnerText, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '30px',
            fontStyle: 'bold',
            color: hexWinnerColor,
        }).setOrigin(0.5);

        // Celebration animation
        this.tweens.add({
            targets: winnerTextObj,
            scaleX: 1.03,
            scaleY: 1.03,
            duration: 600,
            ease: 'Sine.easeInOut',
            yoyo: true,
            repeat: -1,
        });
    }

    createScoreComparison(width, height) {
        const y = 320;
        const cardWidth = 200;
        const cardHeight = 130;

        // Team Blue Score Card
        this.createScoreCard(width / 4 - 20, y, cardWidth, cardHeight,
            this.config.teamBlueName || 'Tim Biru',
            this.result.teamBlueScore,
            COLORS.TEAM_BLUE,
            HEX_COLORS.TEAM_BLUE,
            '🔵'
        );

        // VS text
        const vsContainer = this.add.container(width / 2, y + cardHeight / 2);

        const vsBg = this.add.graphics();
        vsBg.fillStyle(COLORS.ACCENT, 0.15);
        vsBg.fillCircle(0, 0, 30);
        vsContainer.add(vsBg);

        this.add.text(width / 2, y + cardHeight / 2, 'VS', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '22px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5);

        // Team Red Score Card
        this.createScoreCard(width * 3 / 4 - cardWidth + 20, y, cardWidth, cardHeight,
            this.config.teamRedName || 'Tim Merah',
            this.result.teamRedScore,
            COLORS.TEAM_RED,
            HEX_COLORS.TEAM_RED,
            '🔴'
        );

        // Rope visualization
        this.createRopeVisualization(width, y + cardHeight + 40);
    }

    createScoreCard(x, y, width, height, teamName, score, color, hexColor, emoji) {
        const card = this.add.graphics();
        card.fillStyle(COLORS.PANEL_BG, 0.95);
        card.lineStyle(2, color, 0.5);
        card.fillRoundedRect(x, y, width, height, 20);
        card.strokeRoundedRect(x, y, width, height, 20);

        // Team name
        this.add.text(x + width / 2, y + 30, `${emoji} ${teamName}`, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '16px',
            fontStyle: 'bold',
            color: hexColor,
        }).setOrigin(0.5);

        // Score with animation
        const scoreText = this.add.text(x + width / 2, y + 80, score.toString(), {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '52px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);

        // Score pulse if winner
        if ((this.result.winner === 'blue' && color === COLORS.TEAM_BLUE) ||
            (this.result.winner === 'red' && color === COLORS.TEAM_RED)) {
            this.tweens.add({
                targets: scoreText,
                scaleX: 1.1,
                scaleY: 1.1,
                duration: 500,
                ease: 'Sine.easeInOut',
                yoyo: true,
                repeat: -1,
            });
        }
    }

    createRopeVisualization(width, y) {
        const ropeWidth = 450;
        const centerX = width / 2;

        // Rope background
        const ropeBg = this.add.graphics();
        ropeBg.fillStyle(COLORS.PANEL_BORDER, 1);
        ropeBg.fillRoundedRect(centerX - ropeWidth / 2, y, ropeWidth, 16, 8);

        // Rope gradient based on position
        const positionX = centerX + (this.result.ropePosition / 5) * (ropeWidth / 2 - 15);

        // Winner side highlight
        if (this.result.ropePosition < 0) {
            const highlight = this.add.graphics();
            highlight.fillStyle(COLORS.TEAM_BLUE, 0.3);
            highlight.fillRoundedRect(centerX - ropeWidth / 2, y, ropeWidth / 2, 16, 8);
        } else if (this.result.ropePosition > 0) {
            const highlight = this.add.graphics();
            highlight.fillStyle(COLORS.TEAM_RED, 0.3);
            highlight.fillRoundedRect(centerX, y, ropeWidth / 2, 16, 8);
        }

        // Position indicator
        const indicator = this.add.graphics();
        indicator.fillStyle(COLORS.ACCENT, 1);
        indicator.fillCircle(positionX, y + 8, 12);

        // Indicator shadow
        const indicatorShadow = this.add.graphics();
        indicatorShadow.fillStyle(COLORS.ACCENT, 0.3);
        indicatorShadow.fillCircle(positionX, y + 10, 12);

        // Team markers
        this.add.text(centerX - ropeWidth / 2 - 25, y + 8, '🔵', {
            fontSize: '20px',
        }).setOrigin(0.5);

        this.add.text(centerX + ropeWidth / 2 + 25, y + 8, '🔴', {
            fontSize: '20px',
        }).setOrigin(0.5);
    }

    createStatistics(width, height) {
        const y = 530;

        // Stats card
        const statsCard = this.add.graphics();
        statsCard.fillStyle(COLORS.PANEL_BG, 0.9);
        statsCard.lineStyle(2, COLORS.PANEL_BORDER, 1);
        statsCard.fillRoundedRect(width / 2 - 220, y, 440, 75, 18);
        statsCard.strokeRoundedRect(width / 2 - 220, y, 440, 75, 18);

        const stats = [
            { icon: '🎯', label: 'Total Ronde', value: this.result.totalRounds.toString() },
            { icon: '⚡', label: 'Mode', value: 'Cepat-cepatan' },
        ];

        stats.forEach((stat, index) => {
            const x = width / 2 - 110 + index * 220;

            // Icon badge
            const iconBadge = this.add.graphics();
            iconBadge.fillStyle(COLORS.ACCENT_SECONDARY, 0.15);
            iconBadge.fillCircle(x - 50, y + 37, 18);

            this.add.text(x - 50, y + 37, stat.icon, {
                fontSize: '18px',
            }).setOrigin(0.5);

            this.add.text(x + 10, y + 25, stat.label, {
                fontFamily: 'Poppins, sans-serif',
                fontSize: '12px',
                color: HEX_COLORS.TEXT_LIGHT,
            }).setOrigin(0, 0.5);

            this.add.text(x + 10, y + 50, stat.value, {
                fontFamily: 'Poppins, sans-serif',
                fontSize: '20px',
                fontStyle: 'bold',
                color: HEX_COLORS.TEXT_MAIN,
            }).setOrigin(0, 0.5);
        });
    }

    createActionButtons(width, height) {
        const y = height - 65;
        const spacing = 150;

        // Play Again button
        this.createActionButton(width / 2 - spacing, y, '🔄 MAIN LAGI', COLORS.SUCCESS, () => {
            this.cameras.main.fadeOut(300);
            this.cameras.main.once('camerafadeoutcomplete', () => {
                this.scene.start(SCENES.SETUP);
            });
        }, 170);

        // Menu button
        this.createActionButton(width / 2 + spacing, y, '🏠 MENU', COLORS.TEAM_BLUE, () => {
            this.cameras.main.fadeOut(300);
            this.cameras.main.once('camerafadeoutcomplete', () => {
                this.scene.start(SCENES.MENU);
            });
        }, 150);

        // Touch hint
        this.add.text(width / 2, height - 20, '👆 Sentuh tombol untuk melanjutkan', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '12px',
            color: HEX_COLORS.TEXT_MUTED,
        }).setOrigin(0.5);
    }

    createActionButton(x, y, text, color, callback, btnWidth = 160) {
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
                scaleX: 1.06,
                scaleY: 1.06,
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
                onComplete: callback
            });
        });
    }
}
