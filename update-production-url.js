/**
 * Script to update production URL in constants.js
 * Usage: node update-production-url.js https://your-app-name.herokuapp.com
 */

const fs = require('fs');
const path = require('path');

// Get URL from command line argument
const newUrl = process.argv[2];

if (!newUrl) {
    console.error('Error: Please provide the production URL');
    console.log('Usage: node update-production-url.js https://your-app-name.herokuapp.com');
    process.exit(1);
}

// Ensure URL ends with /api/
const apiUrl = newUrl.endsWith('/api/') ? newUrl : newUrl.replace(/\/$/, '') + '/api/';

// Path to constants.js
const constantsPath = path.join(__dirname, 'frontend', 'utils', 'constants.js');

try {
    // Read the file
    let content = fs.readFileSync(constantsPath, 'utf8');
    
    // Replace the PROJECT_BASE_URL
    content = content.replace(
        /PROJECT_BASE_URL:\s*"[^"]*"/,
        `PROJECT_BASE_URL: "${apiUrl}"`
    );
    
    // Write back
    fs.writeFileSync(constantsPath, content, 'utf8');
    
    console.log('✅ Successfully updated PROJECT_BASE_URL to:', apiUrl);
    console.log('📝 Don\'t forget to commit this change:');
    console.log('   git add frontend/utils/constants.js');
    console.log('   git commit -m "Update API URL for production"');
    console.log('   git push');
} catch (error) {
    console.error('Error updating file:', error.message);
    process.exit(1);
}

