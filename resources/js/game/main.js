import Phaser from 'phaser';
import config from './config.js';

/**
 * Q-Game: Tarik Tambang Pembelajaran
 * 
 * A split-screen educational tug-of-war game for classroom use.
 * Built with Phaser.js 3 and Laravel.
 * 
 * @author Q-Game Team
 * @version 1.0.0
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if game container exists
    const container = document.getElementById('game-container');
    if (!container) {
        console.error('Game container not found! Make sure #game-container exists in your HTML.');
        return;
    }

    // Add loading class
    container.classList.add('loading');

    // Create the game
    const game = new Phaser.Game(config);

    // Store game instance globally for debugging
    window.qgame = game;

    // Remove loading class when game starts
    game.events.on('ready', () => {
        container.classList.remove('loading');
        console.log('🎮 Q-Game started successfully!');
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        game.scale.refresh();
    });

    // Handle fullscreen changes - Fix for canvas not resizing properly
    document.addEventListener('fullscreenchange', () => {
        // Multiple refresh calls to ensure proper resizing after fullscreen transition
        const refreshCanvas = () => {
            game.scale.refresh();
            if (game.scale.parentSize) {
                game.scale.updateBounds();
            }
            // Emit resize event to let scenes know
            game.scale.emit('resize', game.scale.gameSize, game.scale.baseSize, game.scale.displaySize, game.scale.resolution);
        };

        // Staggered refreshes for browser stability and slow transitions
        setTimeout(refreshCanvas, 50);
        setTimeout(refreshCanvas, 200);
        setTimeout(refreshCanvas, 400);
        setTimeout(refreshCanvas, 600);
        setTimeout(refreshCanvas, 1000);
    });

    // Also handle webkit prefix (Safari) and MS prefix (Edge legacy)
    document.addEventListener('webkitfullscreenchange', () => {
        const refreshCanvas = () => {
            game.scale.refresh();
            if (game.scale.parentSize) {
                game.scale.updateBounds();
            }
            game.scale.emit('resize', game.scale.gameSize, game.scale.baseSize, game.scale.displaySize, game.scale.resolution);
        };
        setTimeout(refreshCanvas, 50);
        setTimeout(refreshCanvas, 200);
        setTimeout(refreshCanvas, 500);
        setTimeout(refreshCanvas, 1000);
    });

    document.addEventListener('MSFullscreenChange', () => {
        const refreshCanvas = () => {
            game.scale.refresh();
            if (game.scale.parentSize) {
                game.scale.updateBounds();
            }
            game.scale.emit('resize', game.scale.gameSize, game.scale.baseSize, game.scale.displaySize, game.scale.resolution);
        };
        setTimeout(refreshCanvas, 50);
        setTimeout(refreshCanvas, 200);
        setTimeout(refreshCanvas, 500);
        setTimeout(refreshCanvas, 1000);
    });

    // Handle fullscreen toggle with F11
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F11') {
            e.preventDefault();
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                container.requestFullscreen();
            }
        }
    });

    console.log('🎮 Q-Game: Tarik Tambang Pembelajaran');
    console.log('📖 Controls:');
    console.log('   Tim Biru: A, S, D, F');
    console.log('   Tim Merah: 1, 2, 3, 4');
    console.log('   Pause: SPACE');
    console.log('   Fullscreen: F11');
});
