#!/bin/bash
set -e

echo "========================================="
echo "AI Recipe Book - Environment Setup"
echo "========================================="

echo ""
echo "Step 1: Installing PHP dependencies..."
composer install

echo ""
echo "Step 2: Installing NPM dependencies..."
npm install

echo ""
echo "Step 3: Building frontend assets..."
npm run build

echo ""
echo "Step 4: Running database migrations..."
php artisan migrate

echo ""
echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo ""
echo "To start the development server:"
echo "  php artisan serve --host=0.0.0.0"
echo ""
echo "Then access the application at:"
echo "  http://localhost:8000"
echo ""
