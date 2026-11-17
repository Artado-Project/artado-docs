<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

// Redirect admin parameters to admin page
if (isset($_GET['tab']) || isset($_GET['msg']) || isset($_GET['edit_cat']) || isset($_GET['edit_page']) || isset($_GET['delete_cat']) || isset($_GET['delete_page']) || isset($_GET['delete_user'])) {
    header('Location: admin.php?' . http_build_query($_GET));
    exit;
}

$page_title = t('hero_title', 'Artado Geliştirici Dokümantasyonu');
$hide_sidebar = true;
$main_extra_class = 'homepage-full';
include 'includes/header.php';

// Fetch counts for metrics
$cat_count = count($categories);
$page_count = 0;
try {
    $page_count = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
} catch (Exception $e) {}
?>

<!-- Hero Section -->
<section class="dev-hero">
    <div class="dev-hero-container">
        <div class="dev-hero-center">
            <h1 class="dev-hero-title">
                <?php echo htmlspecialchars($site_settings['hero_title'] ?? 'Artado Geliştirici Dokümantasyonu'); ?>
            </h1>
            <p class="dev-hero-subtitle">
                <?php echo htmlspecialchars($site_settings['hero_desc'] ?? 'Artado platformu için hazırlanan resmi kılavuzlar, rehberler ve teknik dokümanlar.'); ?>
            </p>

            <!-- Search -->
            <div class="dev-search-box">
                <form action="search.php" method="GET" class="dev-search-form">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" placeholder="<?php echo t('search_docs_placeholder', 'Dokümantasyonda arayın...'); ?>" autocomplete="off">
                    <button type="submit" class="btn btn-primary btn-search-submit">
                        <?php echo t('search', 'Ara'); ?>
                    </button>
                </form>
            </div>

            <div class="dev-quick-links">
                <span class="quick-label">Önerilen:</span>
                <a href="docs.php?slug=getting-started" class="quick-tag">Başlangıç</a>
                <a href="docs.php?slug=proxy-tanitim" class="quick-tag">Artado Proxy</a>
                <a href="docs.php?slug=what-is-artado" class="quick-tag">Python SDK</a>
            </div>

            <div class="dev-hero-actions">
                <a href="docs.php?slug=getting-started" class="btn btn-primary">
                    <i class="fas fa-book-open"></i> Dokümantasyonu Oku
                </a>
                <a href="#categories" class="btn btn-outline">
                    Kategorileri İncele
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Active Suggestion / Notice -->
<?php
$suggestions_raw = $site_settings['suggestions_list'] ?? '';
$suggestions = array_filter(array_map('trim', explode("\n", $suggestions_raw)));
$active_index = intval($site_settings['active_suggestion'] ?? 0);
if (!empty($suggestions) && isset($suggestions[$active_index])):
    $active_line = $suggestions[$active_index];
    $parts = preg_split('/\s*-\s*/', $active_line, 2);
    $s_title = $parts[0] ?? '';
    $s_desc = $parts[1] ?? '';
?>
<div class="announcement-strip">
    <div class="announcement-container">
        <strong><?php echo htmlspecialchars($s_title); ?>:</strong> <span><?php echo htmlspecialchars($s_desc); ?></span>
    </div>
</div>
<?php endif; ?>

<!-- System Architecture & Category Explorer -->
<section id="categories" class="dev-section">
    <div class="dev-container">
        <div class="dev-section-head">
            <div>
                <h2 class="main-heading">Dokümantasyon Kategorileri</h2>
            </div>
            <p class="head-desc">
                Artado ekosistemini oluşturan projeler.
            </p>
        </div>

        <div class="modules-grid">
            <?php foreach ($categories as $cat): 
                $icon = !empty($cat['icon']) ? $cat['icon'] : 'cubes';
                $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE category_id = ?");
                $stmt_count->execute([$cat['id']]);
                $c_pages = $stmt_count->fetchColumn();
            ?>
            <a href="docs.php?cat=<?php echo urlencode($cat['slug']); ?>" class="module-card">
                <div class="module-card-top">
                    <div class="module-icon">
                        <i class="fas fa-<?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <span class="module-badge"><?php echo $c_pages; ?> Doküman</span>
                </div>

                <h3 class="module-title"><?php echo htmlspecialchars($cat['name']); ?></h3>
                <p class="module-desc"><?php echo htmlspecialchars($cat['description'] ?? 'Artado platformu ve geliştirici dokümantasyonu.'); ?></p>
                
                <div class="module-card-footer">
                    <span class="explore-btn">Kılavuzları Oku <i class="fas fa-chevron-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Technical Guides -->
<section class="dev-section alt-bg">
    <div class="dev-container">
        <div class="dev-section-head">
            <div>
                <span class="sub-heading">Rehberler</span>
                <h2 class="main-heading">Öne Çıkan Başlangıç Dokümanları</h2>
            </div>
            <a href="docs.php?slug=getting-started" class="view-all-link">
                Tüm Dokümanları Gör <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="tech-guides-grid">
            <?php
            try {
                $stmt_featured = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon FROM pages p JOIN categories c ON p.category_id = c.id ORDER BY p.display_order ASC, p.id ASC LIMIT 4");
                $featured_pages = $stmt_featured->fetchAll();
                foreach ($featured_pages as $fp):
                    $f_icon = !empty($fp['category_icon']) ? $fp['category_icon'] : 'file-alt';
                    $snippet = mb_substr(strip_tags($fp['content']), 0, 120) . '...';
            ?>
            <div class="tech-guide-card">
                <div class="tech-guide-header">
                    <span class="tech-cat-pill">
                        <i class="fas fa-<?php echo htmlspecialchars($f_icon); ?>"></i>
                        <?php echo htmlspecialchars($fp['category_name']); ?>
                    </span>
                    <span class="tech-update-time"><?php echo date('M j, Y', strtotime($fp['updated_at'])); ?></span>
                </div>
                
                <h3 class="tech-guide-title">
                    <a href="docs.php?slug=<?php echo urlencode($fp['slug']); ?>">
                        <?php echo htmlspecialchars($fp['title']); ?>
                    </a>
                </h3>
                
                <p class="tech-guide-snippet"><?php echo htmlspecialchars($snippet); ?></p>
                
                <div class="tech-guide-bottom">
                    <a href="docs.php?slug=<?php echo urlencode($fp['slug']); ?>" class="read-link">
                        Rehbere Git <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php 
                endforeach;
            } catch (Exception $e) {}
            ?>
        </div>
    </div>
</section>

<!-- Terminal Quick Install Banner -->
<section class="dev-cta-section">
    <div class="dev-container">
        <div class="dev-cta-box">
            <div class="dev-cta-text">
                <h2>Artado Geliştiriciliğine Katılın</h2>
                <p>Artado ekosisteminde proje oluşturmak veya açık kaynak projelere katkıda bulunmak için dokümanlarımızı takip edin.</p>
            </div>

            <div class="dev-cta-actions">
                <a href="docs.php?slug=getting-started" class="btn btn-primary btn-cta">
                    <i class="fas fa-play"></i> Hızlı Kurulum Rehberi
                </a>
                <a href="docs.php?slug=what-is-artado" class="btn btn-outline btn-cta-outline">
                    <i class="fas fa-file-alt"></i> Artado Hakkında Detaylar
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
