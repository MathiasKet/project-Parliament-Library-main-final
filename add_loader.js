const fs = require('fs');
const path = require('path');

// Function to add loader to HTML files
function addLoaderToHtmlFiles(directory) {
    const files = fs.readdirSync(directory);
    
    files.forEach(file => {
        const filePath = path.join(directory, file);
        const stat = fs.statSync(filePath);
        
        if (stat.isDirectory()) {
            // Skip node_modules and other directories if needed
            if (file === 'node_modules' || file.startsWith('.')) return;
            addLoaderToHtmlFiles(filePath);
        } else if (file.endsWith('.html') && file !== 'index.html') {
            try {
                let content = fs.readFileSync(filePath, 'utf8');
                
                // Add loader CSS
                if (!content.includes('loader.css')) {
                    content = content.replace(
                        /<link[^>]*href=["']css\/green-library\.css["'][^>]*>/,
                        match => `${match}
    <link rel="stylesheet" href="css/loader.css">`
                    );
                }
                
                // Add loader HTML
                if (!content.includes('loader-container')) {
                    content = content.replace(
                        /(<body[^>]*>)/,
                        `$1
    <!-- Loader -->
    <div class="loader-container">
        <span class="loader"></span>
    </div>`
                    );
                }
                
                // Add loader JS
                if (!content.includes('loader.js') && content.includes('</body>')) {
                    content = content.replace(
                        /<\/body>/,
                        `    <!-- Loader Script -->
    <script src="js/loader.js"></script>
</body>`
                    );
                }
                
                fs.writeFileSync(filePath, content, 'utf8');
                console.log(`Updated: ${filePath}`);
            } catch (error) {
                console.error(`Error processing ${filePath}:`, error.message);
            }
        }
    });
}

// Start processing from the current directory
const rootDir = __dirname;
addLoaderToHtmlFiles(rootDir);

console.log('Loader has been added to all HTML files.');
