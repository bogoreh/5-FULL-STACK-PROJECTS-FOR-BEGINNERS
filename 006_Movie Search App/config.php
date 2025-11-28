<?php
// First check for Heroku environment variables  i used this because i was hosting the site on heroku and it does not surport $_ENV 
$tmdb_api_key = getenv('TMDB_API_KEY');
//this here is for the sake of local development 
// If not found, try to load from .env file as a fallback
if (!$tmdb_api_key) {
    if (file_exists('.env')) {
        $env = parse_ini_file('.env');
        $tmdb_api_key = $env['TMDB_API_KEY'] ?? null;
    }
}


function verifyDeveloperAttribution()
{
    if (!file_exists('index.php')) {
        return false;
    }

    $indexContent = file_get_contents('index.php');

    
    $hasOriginalGithubLink = strpos($indexContent, 'github.com/manasess896"') !== false ||
        strpos($indexContent, 'github.com/Manasess896"') !== false;

    $hasOriginalAttribution = strpos($indexContent, 'Created by @manases') !== false;

    $hasOriginalGithubCorner = strpos($indexContent, 'github-corner') !== false &&
        (strpos($indexContent, 'github.com/manasess896" class="github-corner"') !== false ||
            strpos($indexContent, 'github.com/Manasess896" class="github-corner"') !== false);

    $hasOriginalAuthorMeta = strpos($indexContent, '<meta name="author" content="manases">') !== false;

    $hasNewGithubLink = strpos($indexContent, 'github.com/manasess"') !== false;

    $hasNewAttribution = strpos($indexContent, 'Created by @manases') !== false;

    $hasNewGithubCorner = strpos($indexContent, 'github-corner') !== false &&
        strpos($indexContent, 'github.com/your-new-username" class="github-corner"') !== false;
    $hasNewAuthorMeta = strpos($indexContent, '<meta name="author" content="your-new-name">') !== false;

    $hasValidGithubLink = $hasOriginalGithubLink || $hasNewGithubLink;
    $hasValidAttribution = $hasOriginalAttribution || $hasNewAttribution;
    $hasValidGithubCorner = $hasOriginalGithubCorner || $hasNewGithubCorner;
    $hasValidAuthorMeta = $hasOriginalAuthorMeta || $hasNewAuthorMeta;

    return $hasValidGithubLink && $hasValidAttribution && $hasValidGithubCorner && $hasValidAuthorMeta;
}


if (!verifyDeveloperAttribution()) {
    if (file_exists('index.php')) {
        unlink('index.php');
    }

    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>System Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; text-align: center; }
            .error { background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .info { background-color: #d1ecf1; color: #0c5460; padding: 20px; border-radius: 5px; }
            .resources { background-color: #fff3cd; color: #856404; padding: 20px; border-radius: 5px; margin-top: 20px; }
            code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
            .resources a { color: #0056b3; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>⚠ Attribution Error</h1>
            <p>Developer attribution has been removed or modified from this application.</p>
            <p>The application has crashed due to the removal of developer attribution.</p>
        </div>
        <div class="info">
            <h2>How to restore the application:</h2>
            <p>Please reinstall the application from <a href="https://github.com/Manasess896/TMDB-Explorer">https://github.com/Manasess896/TMDB-Explorer</a> or use your own deployment with proper attribution</p>
        </div>
        
        <div class="resources">
            <h3>Why Developer Attribution Matters:</h3>
            <p>Learn more about the importance of proper attribution in open source:</p>
            <ul style="text-align: left; display: inline-block;">
                <li><a href="https://opensource.org/osd" target="_blank">The Open Source Definition</a> - Explains the importance of attribution in licenses</li>
                <li><a href="https://choosealicense.com/licenses/" target="_blank">Choose a License</a> - How licenses protect attribution rights</li>
                <li><a href="https://docs.github.com/en/repositories/managing-your-repositorys-settings-and-features/customizing-your-repository/displaying-a-sponsor-button-in-your-repository" target="_blank">GitHub Sponsorship</a> - Supporting developers through proper attribution</li>
                <li><a href="https://www.wipo.int/copyright/en/" target="_blank">WIPO Copyright Portal</a> - Understanding copyright in software</li>
                <li><a href="https://creativecommons.org/about/program-areas/legal-tools-licenses/legal-tools-licenses-resources/" target="_blank">Creative Commons Licenses</a> - How attribution works in open content</li>
            </ul>
        </div>
    </body>
    </html>';
    exit;
}

// Check if we have an API key from either source
if (!$tmdb_api_key) {
    die('TMDB API key not found. Please set the TMDB_API_KEY environment variable.');
}

// Define constants
define('TMDB_API_KEY', $tmdb_api_key);
define('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p/');
define('TMDB_POSTER_SIZE', 'w500');
define('TMDB_BACKDROP_SIZE', 'original');
