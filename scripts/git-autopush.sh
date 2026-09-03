#!/usr/bin/env bash

# ==============================================================================
# MEMA ERP - Automated Git Push & Continuous Deployment Script
# Usage:
#   ./scripts/git-autopush.sh ["Commit Message"]
# ==============================================================================

set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$DIR"

COMMIT_MSG="${1:-chore: automated update [$(date '+%Y-%m-%d %H:%M:%S')]}"
BRANCH="$(git rev-parse --abbrev-ref HEAD)"

echo "========================================================"
echo " 🚀 MEMA ERP Automated Git Push Engine"
echo " 📂 Repository: $DIR"
echo " 🌿 Branch:     $BRANCH"
echo " 📝 Message:    $COMMIT_MSG"
echo "========================================================"

# Step 1: Compile Assets
echo "📦 Step 1/4: Compiling frontend assets with Vite..."
cd "$DIR/laravel_erp"
npm run build
cd "$DIR"

# Step 2: Run Tests
echo "🧪 Step 2/4: Running full test suite..."
cd "$DIR/laravel_erp"
php artisan test
cd "$DIR"

# Step 3: Stage Changes
echo "📥 Step 3/4: Staging files with git add ...."
git add .

if git diff-index --quiet HEAD --; then
    echo "ℹ️  No new local uncommitted changes detected. Checking remote sync..."
else
    echo "📝 Creating commit: \"$COMMIT_MSG\"..."
    git commit -m "$COMMIT_MSG"
fi

# Step 4: Push to Remote
echo "🚀 Step 4/4: Pushing to origin/$BRANCH..."
git push origin "$BRANCH"

echo ""
echo "========================================================"
echo " ✨ SUCCESS! Everything is committed, tested, and live."
echo "========================================================"
