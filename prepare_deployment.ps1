$targetDir = "deployment_build"
$zipName = "q-game-deployment.zip"

Write-Host "Preparing deployment package..."

# Cleanup previous
if (Test-Path $targetDir) { Remove-Item -Recurse -Force $targetDir }
if (Test-Path $zipName) { Remove-Item -Force $zipName }

New-Item -ItemType Directory -Force -Path $targetDir

# Directories to copy
$dirs = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage", "tests", "vendor")

foreach ($dir in $dirs) {
    if (Test-Path $dir) {
        Write-Host "Copying $dir..."
        Copy-Item -Recurse $dir "$targetDir\$dir"
    }
}

# Files to copy
$files = @("artisan", "composer.json", "composer.lock", "package.json", "vite.config.js", ".env.production")

foreach ($file in $files) {
    if (Test-Path $file) {
        Copy-Item $file "$targetDir\$file"
    }
}

# Cleanups
Write-Host "Cleaning up..."

# Remove local sqlite file from deployment to avoid confusion, they should use server DB or upload manually
if (Test-Path "$targetDir\database\database.sqlite") { Remove-Item "$targetDir\database\database.sqlite" -Force }
# Remove public/storage symlink (must be recreated on server)
if (Test-Path "$targetDir\public\storage") { Remove-Item "$targetDir\public\storage" -Force }
# Clear bootstrap cache (paths differ on server)
Get-ChildItem "$targetDir\bootstrap\cache\*.php" | Remove-Item -Force
# Remove hot file if exists
if (Test-Path "$targetDir\public\hot") { Remove-Item "$targetDir\public\hot" -Force }

# Zip it
Write-Host "Zipping to $zipName..."
Compress-Archive -Path "$targetDir\*" -DestinationPath $zipName

# Cleanup temp dir
Remove-Item -Recurse -Force $targetDir

Write-Host "Deployment package created: $zipName"
