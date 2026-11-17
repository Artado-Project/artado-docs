<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/lang.php';

try {
    $site_settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $site_settings = [];
}

// Check maintenance mode
$maintenance_mode = ($site_settings['maintenance_mode'] ?? '0') == '1';
// Check if current page is admin or login (works with both .php and clean URLs)
$current_script = basename($_SERVER['PHP_SELF']);
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_prefix = '';

$is_admin_page = in_array($current_script, ['admin.php', 'login.php', 'logout.php', 'setup.php']) || 
                 strpos($current_uri, '/admin') !== false || 
                 strpos($current_uri, '/login') !== false ||
                 strpos($current_uri, '/logout') !== false;
$is_logged_in = isset($_SESSION['user_id']);

if ($maintenance_mode && !$is_admin_page && !$is_logged_in) {
    $maintenance_message = $site_settings['maintenance_message'] ?? 'Site is under maintenance. Please check back later.';
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo t('maintenance_mode', 'Maintenance Mode'); ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></title>
        <link rel="icon" href="<?php echo htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/favicon.svg'); ?>" type="image/svg+xml">
        <link rel="stylesheet" href="assets/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body data-theme="dark">
        <div class="maintenance-layout">
            <div>
                <div class="maintenance-logo">
                    <img src="assets/img/logo.svg" alt="Artado" class="logo-img--small">
                </div>
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-main);"><?php echo t('maintenance_mode', 'Maintenance Mode'); ?></h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto;"><?php echo htmlspecialchars($maintenance_message); ?></p>
                <?php if (!$is_logged_in): ?>
                <a href="login.php" class="btn btn-primary mt-2">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('admin_login', 'Admin Login'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Global data needed by all pages
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // If tables don't exist, redirect to setup
    if (strpos($e->getMessage(), "doesn't exist") !== false && basename($_SERVER['PHP_SELF']) != 'setup.php') {
        header('Location: setup.php');
        exit;
    }
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">
    <meta name="author" content="Artado Ecosystem">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:title" content="<?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/logo.svg'); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <title><?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></title>
    <link rel="icon" href="<?php echo $path_prefix . htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/favicon.svg'); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/style.css?v=<?php echo filemtime(dirname(__FILE__) . '/../assets/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $path_prefix . $page_css; ?>?v=<?php echo filemtime(dirname(__FILE__) . '/../' . $page_css); ?>">
    <?php endif; ?>
</head>
<body data-theme="dark">
    <?php if (isset($hide_sidebar) && $hide_sidebar): ?>
    <header class="top-nav">
        <div class="top-nav-container">
            <a href="<?php echo $path_prefix; ?>index.php" class="top-nav-brand">
                <img src="<?php echo $path_prefix . htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>" alt="Artado" class="logo-img">
                <span><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></span>
            </a>
            
            <div class="top-nav-search">
                <form action="<?php echo $path_prefix; ?>search.php" method="GET">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" placeholder="<?php echo t('search_docs', 'Search docs...'); ?>" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </form>
            </div>

            <nav class="top-nav-links" id="topNavLinks">
                <a href="<?php echo $path_prefix; ?>index.php" class="top-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> <span><?php echo t('home', 'Ana Sayfa'); ?></span>
                </a>
                <a href="<?php echo $path_prefix; ?>docs.php?slug=getting-started" class="top-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'docs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> <span><?php echo t('documentation', 'Dökümantasyon'); ?></span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $path_prefix; ?>admin.php" class="top-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i> <span><?php echo t('dashboard', 'Yönetim Paneli'); ?></span>
                </a>
                <a href="<?php echo $path_prefix; ?>logout.php" class="top-nav-link danger-link">
                    <i class="fas fa-sign-out-alt"></i> <span><?php echo t('logout', 'Çıkış'); ?></span>
                </a>
                <?php else: ?>
                <a href="<?php echo $path_prefix; ?>login.php" class="top-nav-link">
                    <i class="fas fa-sign-in-alt"></i> <span><?php echo t('login', 'Giriş'); ?></span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="top-nav-actions">
                <div class="language-switcher">
                    <button onclick="setLang('en')" data-lang="en" class="lang-btn lang-button">EN</button>
                    <button onclick="setLang('tr')" data-lang="tr" class="lang-btn lang-button">TR</button>
                </div>
                <button id="theme-toggle" class="icon-only-btn" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="top-nav-mobile-toggle" onclick="toggleTopNavMenu()" aria-label="Menüyü Aç/Kapat">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <div class="app-container">
        <?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
        <!-- Mobile Top App Bar for Docs view -->
        <header class="mobile-doc-topbar">
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" title="Menü">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo $path_prefix; ?>index.php" class="mobile-topbar-brand">
                <img src="<?php echo $path_prefix . htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>" alt="Artado" class="logo-img--small">
                <span><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></span>
            </a>
            <div class="mobile-topbar-actions">
                <button id="theme-toggle" class="icon-only-btn" title="Tema">
                    <i class="fas fa-moon"></i>
                </button>
                <?php if (isset($has_toc) && $has_toc): ?>
                <button class="mobile-toc-btn" onclick="toggleMobileTOC()" title="İçindekiler">
                    <i class="fas fa-list-ul"></i>
                </button>
                <?php endif; ?>
            </div>
        </header>

        <div class="mobile-sidebar-overlay" onclick="toggleMobileMenu()"></div>
        <div class="mobile-toc-overlay" onclick="toggleMobileTOC()"></div>
        <aside class="sidebar">
            <div class="sidebar-sticky-wrapper">
                <a href="<?php echo $path_prefix; ?>index.php" class="sidebar-logo">
                    <img src="<?php echo $path_prefix . htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>" alt="Artado" class="logo-img">
                    <span><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></span>
                </a>
                
                <div class="search-box">
                    <form action="<?php echo $path_prefix; ?>search.php" method="GET">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" placeholder="<?php echo t('search_docs', 'Search docs...'); ?>" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </form>
                </div>

                <nav class="nav-scroll">
                    <div class="nav-group">
                        <a href="<?php echo $path_prefix; ?>index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> <?php echo t('home', 'Home'); ?>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $path_prefix; ?>admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i> <?php echo t('dashboard', 'Yönetim Paneli'); ?>
                        </a>
                        <a href="<?php echo $path_prefix; ?>logout.php" class="nav-link danger-link">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout', 'Çıkış'); ?>
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php
                    foreach ($categories as $cat):
                        $icon = !empty($cat['icon']) ? $cat['icon'] : 'folder';
                    ?>
                    <div class="nav-group">
                        <div class="nav-group-title">
                            <i class="fas fa-<?php echo htmlspecialchars($icon); ?>"></i>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </div>
                        <?php
                        $stmt_pages = $pdo->prepare("SELECT title, slug FROM pages WHERE category_id = ? ORDER BY display_order ASC");
                        $stmt_pages->execute([$cat['id']]);
                        $pages = $stmt_pages->fetchAll();
                        foreach ($pages as $p):
                            $active = (isset($current_slug) && $current_slug == $p['slug']) ? 'active' : '';
                        ?>
                        <a href="<?php echo $path_prefix; ?>docs.php?slug=<?php echo urlencode($p['slug']); ?>" class="nav-link <?php echo $active; ?>">
                            <i class="fas fa-file-alt file-icon"></i>
                            <span><?php echo htmlspecialchars($p['title']); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </nav>

                <div class="sidebar-footer">
                    <div class="language-switcher">
                        <button onclick="setLang('en')" data-lang="en" class="lang-btn lang-button">EN</button>
                        <button onclick="setLang('tr')" data-lang="tr" class="lang-btn lang-button">TR</button>
                    </div>
                    <button id="theme-toggle">
                        <i class="fas fa-moon"></i> <span>Dark Mode</span>
                    </button>
                </div>
            </div>
        </aside>
        <?php endif; ?>
        
        <main class="main-content <?php echo (isset($hide_sidebar) && $hide_sidebar) ? 'full-width' : ''; ?> <?php echo isset($main_extra_class) ? $main_extra_class : ''; ?>">
