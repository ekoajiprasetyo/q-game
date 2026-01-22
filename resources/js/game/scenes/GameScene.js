import Phaser from 'phaser';
import { SCENES, COLORS, HEX_COLORS, ROPE } from '../utils/constants.js';
import api from '../utils/api.js';


/**
 * GameScene - Main game with split-screen layout and cheerful elegant design
 */
export default class GameScene extends Phaser.Scene {
    constructor() {
        super({ key: SCENES.GAME });
    }

    init(data) {
        this.config = data.config || {
            topicId: 1,
            topicName: 'Demo',
            teamBlueName: 'Tim Biru',
            teamRedName: 'Tim Merah',
            totalQuestions: 10,
            timePerQuestion: 30,
            gameMode: 'race',
        };

        this.state = {
            isPaused: false,
            isMuted: false,
            currentRound: 1,
            teamBlueScore: 0,
            teamRedScore: 0,
            ropePosition: 0,
            timeRemaining: this.config.timePerQuestion,
            teamBlueAnswered: false,
            teamRedAnswered: false,
            teamBlueAnswer: null,
            teamRedAnswer: null,
            teamBlueTime: null,
            teamRedTime: null,
            roundStartTime: 0,
            questions: [],
            currentQuestion: null,
            sessionId: null,
            teamBlueInput: '',
            teamRedInput: '',
        };
    }

    async create() {
        const { width, height } = this.cameras.main;
        this.width = width;
        this.height = height;

        this.cameras.main.fadeIn(400);
        this.cameras.main.setBackgroundColor(COLORS.BACKGROUND);

        await this.loadQuestions();

        this.createBackground();
        this.createRopeSection();

        // Initialize HTML Interface
        this.initDOMInterface();

        this.startRound();
        this.createTimer();
    }

    createBackground() {
        // Subtle gradient
        const gradient = this.add.graphics();
        gradient.fillGradientStyle(
            COLORS.BACKGROUND, COLORS.BACKGROUND,
            COLORS.GRADIENT_START, COLORS.GRADIENT_END,
            0.2
        );
        gradient.fillRect(0, 0, this.width, this.height);

        // Decorative bubbles (subtle)
        const colors = [COLORS.TEAM_BLUE, COLORS.TEAM_RED];
        colors.forEach((color, i) => {
            const bubble = this.add.graphics();
            bubble.fillStyle(color, 0.04);
            bubble.fillCircle(i === 0 ? 100 : this.width - 100, this.height / 2, 200);
        });
    }

    async loadQuestions() {
        try {
            // Use PIN to fetch questions if available
            if (this.config.pinCode) {
                const result = await api.getQuestionsByPin(
                    this.config.pinCode,
                    this.config.totalQuestions
                );

                if (result.success && result.questions && result.questions.length > 0) {
                    this.state.questions = result.questions;
                    // Update topic name and SESSION ID from session if available
                    if (result.session) {
                        this.config.topicName = result.session.title;
                        this.config.sessionId = result.session.id; // CRITICAL FIX: Save Session ID
                    }
                    console.log(`Loaded ${result.questions.length} questions for PIN: ${this.config.pinCode}, SessionID: ${this.config.sessionId}`);
                    return;
                }
            }

            // Fallback to topic-based questions
            const questions = await api.getRandomQuestions(
                this.config.topicId,
                this.config.totalQuestions
            );
            this.state.questions = questions.data || questions || [];

            if (this.state.questions.length === 0) {
                this.state.questions = this.getDemoQuestions();
            }
        } catch (error) {
            console.error('Error loading questions:', error);
            this.state.questions = this.getDemoQuestions();
        }
    }

    getDemoQuestions() {
        return [
            {
                id: 1,
                question_text: 'Berapa hasil dari 15 × 12?',
                options: [
                    { key: 'A', text: '170' },
                    { key: 'B', text: '180' },
                    { key: 'C', text: '190' },
                    { key: 'D', text: '200' },
                ],
                correct_answer: 'B',
                points: 10,
            },
            {
                id: 2,
                question_text: 'Apa ibukota Indonesia?',
                options: [
                    { key: 'A', text: 'Surabaya' },
                    { key: 'B', text: 'Bandung' },
                    { key: 'C', text: 'Jakarta' },
                    { key: 'D', text: 'Yogyakarta' },
                ],
                correct_answer: 'C',
                points: 10,
            },
            {
                id: 3,
                question_text: 'Berapa hasil dari √144?',
                options: [
                    { key: 'A', text: '10' },
                    { key: 'B', text: '11' },
                    { key: 'C', text: '12' },
                    { key: 'D', text: '13' },
                ],
                correct_answer: 'C',
                points: 10,
            },
        ];
    }

    initDOMInterface() {
        // Show UI Layer
        const ui = document.getElementById('game-ui');
        if (ui) ui.style.display = 'flex';

        // Bind Control Buttons
        const fsBtn = document.getElementById('btn-fullscreen');
        if (fsBtn) {
            fsBtn.onclick = () => {
                if (this.scale.isFullscreen) this.scale.stopFullscreen();
                else this.scale.startFullscreen();
            };

            // Update button text on fullscreen change
            this.scale.on('enterfullscreen', () => {
                fsBtn.innerHTML = '⛶ Exit';
                fsBtn.classList.add('active');
            });

            this.scale.on('leavefullscreen', () => {
                fsBtn.innerHTML = '⛶ Fullscreen';
                fsBtn.classList.remove('active');
            });
        }

        const btnSound = document.getElementById('btn-sound');
        btnSound.onclick = () => this.toggleMute();
        this.domBtnSound = btnSound;

        document.getElementById('btn-exit').onclick = () => this.confirmExit();

        // Bind Session Info
        document.getElementById('session-title').textContent = `Sesi: ${this.config.topicName}`;

        // Initial Score Update
        this.updateScoreUI();
    }

    updateScoreUI() {
        const blueScore = document.getElementById('score-blue');
        const redScore = document.getElementById('score-red');
        if (blueScore) blueScore.textContent = this.state.teamBlueScore;
        if (redScore) redScore.textContent = this.state.teamRedScore;
    }

    updateTimerUI() {
        const timerEl = document.getElementById('game-timer');
        if (timerEl) {
            timerEl.textContent = this.formatTime(this.state.timeRemaining);
            // Change color if low time
            timerEl.style.backgroundColor = this.state.timeRemaining <= 10 ? 'rgba(231, 76, 60, 0.2)' : 'rgba(255, 155, 80, 0.15)';
            timerEl.style.color = this.state.timeRemaining <= 10 ? '#E74C3C' : '#FF9B50';
        }
    }

    createRopeSection() {
        const sectionY = 70;
        const sectionHeight = 170;

        // Team score cards
        this.createTeamScoreCard(30, sectionY + 10, this.config.teamBlueName, 'blue');
        this.createTeamScoreCard(this.width - 180, sectionY + 10, this.config.teamRedName, 'red');

        // Timer in center - Wider capsule
        const timerContainer = this.add.container(this.width / 2, sectionY + 45);

        const timerBg = this.add.graphics();
        timerBg.fillStyle(COLORS.ACCENT, 0.15);
        timerBg.fillRoundedRect(-60, -25, 120, 50, 25); // Increased width from 100 to 120
        timerContainer.add(timerBg);

        this.timerText = this.add.text(0, 0, this.config.gameMode === 'race' ? '⏱️ --:--' : this.formatTime(this.state.timeRemaining), {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '22px',
            fontStyle: 'bold',
            color: HEX_COLORS.ACCENT,
        }).setOrigin(0.5);
        timerContainer.add(this.timerText);

        // Center divider line (dashed)
        const divider = this.add.graphics();
        divider.lineStyle(3, COLORS.PANEL_BORDER, 0.6);
        for (let y = sectionY + 75; y < sectionY + sectionHeight; y += 15) {
            divider.moveTo(this.width / 2, y);
            divider.lineTo(this.width / 2, y + 8);
        }
        divider.strokePath();

        // Create rope visualization
        this.createRope(sectionY + 130);
    }

    createTeamScoreCard(x, y, teamName, team) {
        const isBlue = team === 'blue';
        const color = isBlue ? COLORS.TEAM_BLUE : COLORS.TEAM_RED;
        const hexColor = isBlue ? HEX_COLORS.TEAM_BLUE : HEX_COLORS.TEAM_RED;
        const emoji = isBlue ? '🔵' : '🔴';

        const cardWidth = 150;
        const cardHeight = 80;

        // Card background
        const card = this.add.graphics();
        card.fillStyle(COLORS.PANEL_BG, 0.95);
        card.lineStyle(2, color, 0.4);
        card.fillRoundedRect(x, y, cardWidth, cardHeight, 16);
        card.strokeRoundedRect(x, y, cardWidth, cardHeight, 16);

        // Team name
        this.add.text(x + cardWidth / 2, y + 22, `${emoji} ${teamName}`, {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '14px',
            fontStyle: 'bold',
            color: hexColor,
        }).setOrigin(0.5);

        // Score
        const scoreText = this.add.text(x + cardWidth / 2, y + 55, '0', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '28px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);

        if (isBlue) {
            this.blueScoreText = scoreText;
        } else {
            this.redScoreText = scoreText;
        }
    }

    createRope(y) {
        const centerX = this.width / 2;
        // Shift rope Y slightly up to match hands
        const adjustedY = y - 10;
        this.ropeGroup = this.add.container(centerX, adjustedY);

        const charDistance = 450;
        const charScale = 0.13;

        // Character offsets to align hands with rope
        const handOffsetY = 15;

        // Blue character (left)
        const blueCharX = -charDistance / 2;
        this.blueChar = this.add.sprite(blueCharX, 0, 'char_blue');
        this.blueChar.setScale(charScale);
        this.blueChar.setOrigin(0.5);
        this.ropeGroup.add(this.blueChar);

        // Red character (right)
        const redCharX = charDistance / 2;
        this.redChar = this.add.sprite(redCharX, 0, 'char_red');
        this.redChar.setScale(charScale);
        this.redChar.setOrigin(0.5);
        this.redChar.setFlipX(true); // Flip red character to face left (tug of war)
        this.ropeGroup.add(this.redChar);

        // Draw rope connecting characters
        const ropeGraphics = this.add.graphics();

        // Rope shadow
        ropeGraphics.lineStyle(12, 0x000000, 0.15);
        ropeGraphics.beginPath();
        // Connect from center of body roughly to center
        ropeGraphics.moveTo(blueCharX + 30, handOffsetY);
        ropeGraphics.lineTo(redCharX - 30, handOffsetY);
        ropeGraphics.strokePath();

        // Main rope - brown gradient effect
        ropeGraphics.lineStyle(10, 0x8B4513, 1);
        ropeGraphics.beginPath();
        ropeGraphics.moveTo(blueCharX + 30, handOffsetY);
        ropeGraphics.lineTo(redCharX - 30, handOffsetY);
        ropeGraphics.strokePath();

        // Rope highlight
        ropeGraphics.lineStyle(4, 0xD2691E, 0.7);
        ropeGraphics.beginPath();
        ropeGraphics.moveTo(blueCharX + 30, handOffsetY - 3);
        ropeGraphics.lineTo(redCharX - 30, handOffsetY - 3);
        ropeGraphics.strokePath();

        // Add rope twist texture details
        ropeGraphics.lineStyle(2, 0x6B3E0A, 0.5);
        for (let i = blueCharX + 40; i < redCharX - 40; i += 20) {
            ropeGraphics.beginPath();
            ropeGraphics.moveTo(i, handOffsetY - 5);
            ropeGraphics.lineTo(i + 10, handOffsetY + 5);
            ropeGraphics.strokePath();
        }

        // Ensure rope is behind characters? No, usually held in hands in front.
        // But for simple sprites, behind might look cleaner if hands aren't defined.
        // Let's put rope BEHIND characters for now to avoid crossing faces.
        this.ropeGroup.sendToBack(ropeGraphics);
        this.ropeGroup.add(ropeGraphics); // Re-add to ensure in container

        // Center marker
        const centerMarker = this.add.graphics();
        centerMarker.fillStyle(COLORS.ERROR, 1);
        centerMarker.beginPath();
        centerMarker.moveTo(0, handOffsetY - 15);
        centerMarker.lineTo(12, handOffsetY - 5);
        centerMarker.lineTo(0, handOffsetY + 5);
        centerMarker.lineTo(-12, handOffsetY - 5);
        centerMarker.closePath();
        centerMarker.fillPath();

        this.ropeGroup.add(centerMarker);

        this.updateRopeIndicator(centerX, adjustedY);
    }

    updateRopeIndicator(baseX, y) {
        const stepSize = 55;
        const displacement = this.state.ropePosition * stepSize;
        const targetX = (this.width / 2) + displacement;

        if (this.ropeGroup) {
            this.tweens.add({
                targets: this.ropeGroup,
                x: targetX,
                duration: 500,
                ease: 'Power2',
            });
        }
    }

    createPauseOverlay() {
        this.pauseOverlay = this.add.graphics();
        this.pauseOverlay.setVisible(false);

        this.pauseContainer = this.add.container(this.width / 2, this.height / 2);
        this.pauseContainer.setVisible(false);

        // Modal background
        const modalBg = this.add.graphics();
        modalBg.fillStyle(COLORS.PANEL_BG, 0.98);
        modalBg.lineStyle(3, COLORS.ACCENT, 0.8);
        modalBg.fillRoundedRect(-180, -120, 360, 240, 24);
        modalBg.strokeRoundedRect(-180, -120, 360, 240, 24);
        this.pauseContainer.add(modalBg);

        // Pause icon
        const pauseIcon = this.add.text(0, -55, '⏸️', {
            fontFamily: 'Arial',
            fontSize: '56px',
        }).setOrigin(0.5);
        this.pauseContainer.add(pauseIcon);

        // Pause text
        const pauseText = this.add.text(0, 15, 'PERMAINAN DIJEDA', {
            fontFamily: 'Poppins, sans-serif',
            fontSize: '24px',
            fontStyle: 'bold',
            color: HEX_COLORS.TEXT_MAIN,
        }).setOrigin(0.5);
        this.pauseContainer.add(pauseText);

        // Resume button
        this.createPauseButton(0, 75, '▶️ LANJUTKAN', COLORS.SUCCESS, () => this.togglePause());
    }

    createPauseButton(x, y, text, color, callback) {
        const container = this.add.container(x, y);
        this.pauseContainer.add(container);

        const btnWidth = 180;
        const btnHeight = 48;

        const bg = this.add.graphics();
        bg.fillStyle(color, 1);
        bg.fillRoundedRect(-btnWidth / 2, -btnHeight / 2, btnWidth, btnHeight, 24);
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

        hitArea.on('pointerdown', () => {
            this.tweens.add({
                targets: container,
                scaleX: 0.95,
                scaleY: 0.95,
                duration: 100,
                yoyo: true,
                onComplete: callback
            });
        });
    }

    displayQuestion() {
        const question = this.state.questions[this.state.currentRound - 1];
        if (!question) {
            this.endGame();
            return;
        }

        this.state.currentQuestion = question;
        this.state.teamBlueAnswered = false;
        this.state.teamRedAnswered = false;
        this.state.teamBlueAnswer = null;
        this.state.teamRedAnswer = null;

        // Render UI via DOM
        this.renderQuestionParams(question);

        // Update Progress
        const progressEl = document.getElementById('game-progress');
        if (progressEl) progressEl.textContent = `Soal ${this.state.currentRound}/${this.config.totalQuestions}`;

        // Timer reset
        this.state.timeRemaining = this.config.timePerQuestion;
        this.updateTimerUI();
    }

    renderQuestionParams(question) {
        const isEssay = question.question_type === 'short_answer';

        // Update Question Text (using innerHTML for rich text support)
        document.getElementById('q-text-blue').innerHTML = question.question_text;
        document.getElementById('q-text-red').innerHTML = question.question_text;

        // Render KaTeX math formulas if available
        if (typeof window.renderKaTeX === 'function') {
            window.renderKaTeX(document.getElementById('q-text-blue'));
            window.renderKaTeX(document.getElementById('q-text-red'));
        }

        // Reset Input Fields if exists
        const blueInput = document.getElementById('input-blue');
        const redInput = document.getElementById('input-red');
        if (blueInput) blueInput.value = '';
        if (redInput) redInput.value = '';
        this.state.teamBlueInput = '';
        this.state.teamRedInput = '';

        // Toggle UI modes
        const blueOptions = document.getElementById('options-blue');
        const redOptions = document.getElementById('options-red');
        const blueEssay = document.getElementById('essay-blue');
        const redEssay = document.getElementById('essay-red');

        if (isEssay) {
            blueOptions.hidden = true;
            redOptions.hidden = true;
            blueEssay.hidden = false;
            redEssay.hidden = false;

            // Decide Keyboard Layout (Numeric or Full)
            const isNumeric = /^[\d\s.,+-]+$/.test(question.correct_answer);
            this.renderVirtualKeyboard('blue', isNumeric);
            this.renderVirtualKeyboard('red', isNumeric);

        } else {
            blueOptions.hidden = false;
            redOptions.hidden = false;
            blueEssay.hidden = true;
            redEssay.hidden = true;

            // Render Options Buttons
            this.renderOptionButtons('blue', question.options);
            this.renderOptionButtons('red', question.options);
        }
    }

    renderOptionButtons(team, options) {
        const container = document.getElementById(`options-${team}`);
        if (!container) return;

        container.innerHTML = ''; // Clear previous

        options.forEach(opt => {
            const btn = document.createElement('div');
            btn.className = 'option-btn';
            btn.dataset.key = opt.key;
            btn.onclick = () => this.submitAnswer(team, opt.key);

            btn.innerHTML = `
                <div class="option-key">${opt.key}</div>
                <div class="option-text">${opt.text}</div>
            `;
            container.appendChild(btn);
        });

        // Render KaTeX math formulas in options
        if (typeof window.renderKaTeX === 'function') {
            window.renderKaTeX(container);
        }
    }

    renderVirtualKeyboard(team, isNumeric) {
        const container = document.getElementById(`kb-${team}`);
        if (!container) return;
        container.innerHTML = '';

        const rows = isNumeric
            ? [['1', '2', '3'], ['4', '5', '6'], ['7', '8', '9'], ['⌫', '0', 'OK']]
            : [
                ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
                ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
                ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
                ['Z', 'X', 'C', 'V', 'B', 'N', 'M', '⌫'],
                ['SPACE', 'OK']
            ];

        rows.forEach(rowKeys => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'kb-row';

            rowKeys.forEach(char => {
                const keyBtn = document.createElement('div');
                keyBtn.className = `kb-key ${char === 'SPACE' ? 'space' : ''} ${char === 'OK' ? 'enter' : ''}`;
                keyBtn.textContent = char;
                keyBtn.onclick = () => this.handleVirtualKeyPress(team, char);
                rowDiv.appendChild(keyBtn);
            });
            container.appendChild(rowDiv);
        });
    }

    handleVirtualKeyPress(team, char) {
        if (this.state.isPaused) return;

        // Prevent typing if already answered
        if (team === 'blue' && this.state.teamBlueAnswered) return;
        if (team === 'red' && this.state.teamRedAnswered) return;

        let currentInput = team === 'blue' ? this.state.teamBlueInput : this.state.teamRedInput;
        const inputMaxLength = 20;

        if (char === 'OK') {
            if (currentInput.trim().length > 0) {
                this.submitAnswer(team, currentInput);
            }
            return;
        }

        if (char === '⌫') {
            currentInput = currentInput.slice(0, -1);
        } else if (char === 'SPACE') {
            if (currentInput.length < inputMaxLength) currentInput += ' ';
        } else {
            if (currentInput.length < inputMaxLength) currentInput += char;
        }

        // Update State & UI
        if (team === 'blue') {
            this.state.teamBlueInput = currentInput;
            document.getElementById('input-blue').value = currentInput;
        } else {
            this.state.teamRedInput = currentInput;
            document.getElementById('input-red').value = currentInput;
        }
    }

    submitAnswer(team, answer) {
        if (this.state.isPaused) return;

        const currentTime = Date.now() - this.state.roundStartTime;

        if (team === 'blue' && !this.state.teamBlueAnswered) {
            this.state.teamBlueAnswered = true;
            this.state.teamBlueAnswer = answer;
            this.state.teamBlueTime = currentTime;
            this.highlightAnswerUI('blue', answer);
        } else if (team === 'red' && !this.state.teamRedAnswered) {
            this.state.teamRedAnswered = true;
            this.state.teamRedAnswer = answer;
            this.state.teamRedTime = currentTime;
            this.highlightAnswerUI('red', answer);
        }

        if (this.config.gameMode === 'race') {
            this.endRound();
        } else if (this.state.teamBlueAnswered && this.state.teamRedAnswered) {
            this.endRound();
        }
    }

    highlightAnswerUI(team, answer) {
        const isEssay = this.state.currentQuestion.question_type === 'short_answer';

        if (isEssay) {
            // Highlight Input Box
            const inputEl = document.getElementById(`input-${team}`);
            if (inputEl) {
                inputEl.style.borderColor = team === 'blue' ? COLORS.TEAM_BLUE : COLORS.TEAM_RED;
                inputEl.style.backgroundColor = team === 'blue' ? '#EBF0FF' : '#FFEFF7';
            }
        } else {
            // Find and highlight option button
            const container = document.getElementById(`options-${team}`);
            if (!container) return;

            const buttons = container.getElementsByClassName('option-btn');
            for (let btn of buttons) {
                if (btn.dataset.key === answer) {
                    btn.classList.add('selected');
                } else {
                    btn.style.opacity = '0.5'; // Dim others
                    btn.style.pointerEvents = 'none'; // Disable clicks
                }
            }
        }
    }

    createTimer() {
        this.timerEvent = this.time.addEvent({
            delay: 1000,
            callback: this.updateTimer,
            callbackScope: this,
            loop: true,
        });
    }

    updateTimer() {
        if (this.state.isPaused) return;

        this.state.timeRemaining--;
        this.updateTimerUI();

        if (this.state.timeRemaining <= 0) {
            this.endRound();
        }
    }

    // -- End Cleaned up methods --
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `⏱️ ${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    startRound() {
        this.state.teamBlueAnswered = false;
        this.state.teamRedAnswered = false;
        this.state.teamBlueAnswer = null;
        this.state.teamRedAnswer = null;
        this.state.teamBlueTime = null;
        this.state.teamRedTime = null;
        this.state.timeRemaining = this.config.timePerQuestion;
        this.state.roundStartTime = Date.now();

        // if (this.blueIndicator) this.blueIndicator.setText('');
        // if (this.redIndicator) this.redIndicator.setText('');

        // if (this.timerText) this.timerText.setColor(HEX_COLORS.ACCENT);

        const questionIndex = this.state.currentRound - 1;
        if (questionIndex < this.state.questions.length) {
            this.state.currentQuestion = this.state.questions[questionIndex];
            this.displayQuestion(); // Call the new DOM-based displayQuestion
        } else {
            this.endGame();
        }

        // if (this.progressText) {
        //     this.progressText.setText(`Soal ${this.state.currentRound}/${this.config.totalQuestions}`);
        // }

        // this.resetButtonHighlights(); // No longer needed for DOM buttons
    }

    // Removed old displayQuestion, handleKeyboardPress, resetButtonHighlights

    endRound() {
        const question = this.state.currentQuestion;
        if (!question) return;

        const blueCorrect = this.isAnswerCorrect(this.state.teamBlueAnswer, question);
        const redCorrect = this.isAnswerCorrect(this.state.teamRedAnswer, question);

        this.showRoundResult(blueCorrect, redCorrect, question.correct_answer);
        this.updateScores(blueCorrect, redCorrect, question.points || 10);

        if (Math.abs(this.state.ropePosition) >= ROPE.MAX_POSITION) {
            this.time.delayedCall(2000, () => this.endGame());
            return;
        }

        this.state.currentRound++;
        if (this.state.currentRound <= this.config.totalQuestions) {
            this.time.delayedCall(2000, () => this.startRound());
        } else {
            this.time.delayedCall(2000, () => this.endGame());
        }
    }

    showRoundResult(blueCorrect, redCorrect, correctAnswer) {
        // Update DOM indicators
        const blueIndicatorEl = document.getElementById('blue-indicator');
        const redIndicatorEl = document.getElementById('red-indicator');

        if (this.state.teamBlueAnswered) {
            blueIndicatorEl.textContent = blueCorrect ? '✅ BENAR!' : '❌ SALAH';
            blueIndicatorEl.style.color = blueCorrect ? HEX_COLORS.SUCCESS : HEX_COLORS.ERROR;
        } else {
            blueIndicatorEl.textContent = '⏱️ Waktu Habis';
            blueIndicatorEl.style.color = HEX_COLORS.TEXT_MUTED;
        }

        if (this.state.teamRedAnswered) {
            redIndicatorEl.textContent = redCorrect ? '✅ BENAR!' : '❌ SALAH';
            redIndicatorEl.style.color = redCorrect ? HEX_COLORS.SUCCESS : HEX_COLORS.ERROR;
        } else {
            redIndicatorEl.textContent = '⏱️ Waktu Habis';
            redIndicatorEl.style.color = HEX_COLORS.TEXT_MUTED;
        }

        this.highlightCorrectAnswer(correctAnswer);
    }

    highlightCorrectAnswer(correctAnswer) {
        const isEssay = this.state.currentQuestion.question_type === 'short_answer';
        if (isEssay) return; // No specific highlight for essay yet, handled in indicator

        ['blue', 'red'].forEach(team => {
            const container = document.getElementById(`options-${team}`);
            if (!container) return;
            const buttons = container.getElementsByClassName('option-btn');

            for (let btn of buttons) {
                if (btn.dataset.key === correctAnswer) {
                    btn.classList.add('correct');
                } else if (btn.classList.contains('selected')) {
                    btn.classList.add('wrong');
                }
            }
        });
    }

    isAnswerCorrect(answer, question) {
        if (!answer) return false;

        if (question.question_type === 'short_answer') {
            return answer.toString().trim().toUpperCase() === question.correct_answer.toString().trim().toUpperCase();
        }

        return answer === question.correct_answer;
    }

    updateScores(blueCorrect, redCorrect, points) {
        if (this.config.gameMode === 'race') {
            if (blueCorrect && (!redCorrect || this.state.teamBlueTime < this.state.teamRedTime)) {
                this.state.teamBlueScore += points;
                this.state.ropePosition--;
            } else if (redCorrect && (!blueCorrect || this.state.teamRedTime < this.state.teamBlueTime)) {
                this.state.teamRedScore += points;
                this.state.ropePosition++;
            }
        } else {
            if (blueCorrect && !redCorrect) {
                this.state.teamBlueScore += points;
                this.state.ropePosition--;
            } else if (redCorrect && !blueCorrect) {
                this.state.teamRedScore += points;
                this.state.ropePosition++;
            }
        }

        this.state.ropePosition = Math.max(-ROPE.MAX_POSITION,
            Math.min(ROPE.MAX_POSITION, this.state.ropePosition));

        this.updateScoreUI();
        this.animateRope();
    }

    animateRope() {
        // Rope animation handled by Canvas Rope Section (createRopeSection)
        // We just verify it reads from this.state.ropePosition
        const baseX = this.width / 2;
        const y = 80; // Adjusted Y relative to new rope pos
        this.updateRopeIndicator(baseX, y);
    }

    toggleMute() {
        this.state.isMuted = !this.state.isMuted;

        if (this.domBtnSound) {
            this.domBtnSound.innerHTML = this.state.isMuted
                ? '<span>🔇</span> Mati'
                : '<span>🔊</span> Suara';
            this.domBtnSound.style.opacity = this.state.isMuted ? '0.5' : '1';
        }

        if (this.sound) {
            this.sound.mute = this.state.isMuted;
        }
    }

    confirmExit() {
        this.cameras.main.fadeOut(400);
        this.scene.start(SCENES.MENU);
    }

    async endGame() {
        let winner = 'draw';
        if (this.state.ropePosition <= -ROPE.MAX_POSITION ||
            this.state.teamBlueScore > this.state.teamRedScore) {
            winner = 'blue';
        } else if (this.state.ropePosition >= ROPE.MAX_POSITION ||
            this.state.teamRedScore > this.state.teamBlueScore) {
            winner = 'red';
        }

        // Save Result to Database
        console.log('endGame called. SessionId:', this.config.sessionId, 'Scores:', this.state.teamRedScore, '-', this.state.teamBlueScore);
        if (this.config.sessionId) {
            try {
                console.log('Calling api.updateSession...');
                const result = await api.updateSession({
                    session_id: this.config.sessionId,
                    status: 'finished',
                    red_score: this.state.teamRedScore,
                    blue_score: this.state.teamBlueScore,
                    winner: winner
                });
                console.log('Session saved successfully. Result:', result);
            } catch (err) {
                console.error("Failed to save session score:", err);
            }
        } else {
            console.warn('SessionId is not set! Cannot save score.');
        }

        this.shutdown(); // Clean up UI immediately

        this.cameras.main.fadeOut(400);
        this.cameras.main.once('camerafadeoutcomplete', () => {
            this.scene.start(SCENES.RESULT, {
                config: this.config,
                result: {
                    winner,
                    teamBlueScore: this.state.teamBlueScore,
                    teamRedScore: this.state.teamRedScore,
                    totalRounds: this.state.currentRound - 1,
                    ropePosition: this.state.ropePosition,
                },
            });
        });
    }

    cleanupDOM() {
        const ui = document.getElementById('game-ui');
        if (ui) ui.style.display = 'none';

        // Clear dynamic content
        const ids = ['options-blue', 'options-red', 'kb-blue', 'kb-red'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = '';
        });
    }

    shutdown() {
        this.cleanupDOM();
        if (this.timerEvent) this.timerEvent.remove();
        this.input.keyboard.removeAllListeners();
    }
}
