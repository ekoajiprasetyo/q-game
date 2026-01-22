---
description: Setup Q-Game project with Phaser.js and Laravel
---

# Setup Q-Game Project

// turbo-all

## Step 1: Install Phaser.js
```bash
npm install phaser
```

## Step 2: Create game directory structure
```bash
mkdir -p resources/js/game/scenes
mkdir -p resources/js/game/objects
mkdir -p resources/js/game/managers
mkdir -p resources/js/game/utils
mkdir -p public/assets/game/images
mkdir -p public/assets/game/audio/sfx
mkdir -p public/assets/game/audio/music
```

## Step 3: Run migrations
```bash
php artisan migrate
```

## Step 4: Seed sample data
```bash
php artisan db:seed
```

## Step 5: Start development servers
```bash
composer dev
```
