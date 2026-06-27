#!/bin/bash

# Setup script for Tracker application

echo "Setting up Tracker application..."

# Copy .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file. Please configure database settings."
fi

# Create database
echo "Creating database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS tracker DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
echo "Importing schema..."
mysql -u root tracker < migrations/001_initial.sql

echo "Setup complete! You can now access the application."
echo "Default login: admin@tracker.local / admin123"
