#!/bin/bash

# HR4 Compensation & Intelligence System - GitHub Initialization Script

echo "🚀 Initializing HR4 Compensation & Intelligence System for GitHub..."

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "📁 Initializing Git repository..."
    git init
else
    echo "✅ Git repository already initialized"
fi

# Add all files
echo "📝 Adding files to Git..."
git add .

# Create initial commit
echo "💾 Creating initial commit..."
git commit -m "Initial commit: HR4 Compensation & Intelligence System

- Complete HR management system for healthcare organizations
- Role-based access control with 7 user roles
- Payroll processing with Philippine labor law compliance
- Benefits administration and HMO management
- Analytics and reporting capabilities
- RESTful API with modern SPA frontend
- MySQL database with comprehensive schema
- Security features and audit logging"

echo "✅ Initial commit created successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Create a new repository on GitHub"
echo "2. Add the remote origin:"
echo "   git remote add origin https://github.com/YOUR_USERNAME/HR4.git"
echo "3. Push to GitHub:"
echo "   git branch -M main"
echo "   git push -u origin main"
echo ""
echo "🎉 Your HR4 Compensation & Intelligence System is ready for GitHub!"
