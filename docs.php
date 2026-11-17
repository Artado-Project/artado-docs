<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/Parsedown.php';
require_once 'includes/toc.php';

$slug = $_GET['slug'] ?? null;
$cat_slug = $_GET['cat'] ?? null;

if (!$slug && $cat_slug) {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
    $stmt->execute([$cat_slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        // Category doesn't exist, show 404
        $page_title = '404 - Category Not Found';
        include 'includes/header.php';
        echo "<div class='hero'><h1>404</h1><p>The category you are looking for does not exist.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
        include 'includes/footer.php';
        exit;
    }
    
    // If category requested without slug, redirect to first page of that category
    $stmt = $pdo->prepare("SELECT p.slug FROM pages p JOIN categories c ON p.category_id = c.id WHERE c.slug = ? ORDER BY p.display_order ASC LIMIT 1");
    $stmt->execute([$cat_slug]);
    $first_page = $stmt->fetch();
    if ($first_page) {
        header("Location: docs.php?slug=" . $first_page['slug']);
        exit;
    } else {
        // If no pages in category, show category page with message
        $page_title = htmlspecialchars($category['name']);
        include 'includes/header.php';
        echo "<div class='hero'><h1>" . htmlspecialchars($category['name']) . "</h1><p style='color: var(--text-muted);'>This category doesn't have any pages yet.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
        include 'includes/footer.php';
        exit;
    }
}

if (!$slug) {
        header("Location: index.php");
        exit;
}

// Fetch the page
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM pages p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    $page_title = '404 - Not Found';
    include 'includes/header.php';
        echo "<div class='hero'><h1>404</h1><p>The documentation page you are looking for does not exist.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
    include 'includes/footer.php';
    exit;
}

$page_content_raw = $page['content'];
$page_content_html = renderMarkdown($page_content_raw);
$toc_data = TOCGenerator::generate($page_content_html);
$page_content = $toc_data['html'];
$toc_list = TOCGenerator::renderList($toc_data['toc']);
$page_description = mb_substr(strip_tags($page_content), 0, 160) . '...';

$page_title = $page['title'];
$current_slug = $page['slug'];
$has_toc = !empty($toc_data['toc']);
include 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php"><i class="fas fa-home"></i> <?php echo t('home', 'Home'); ?></a>
    <i class="fas fa-chevron-right breadcrumb-separator"></i>
    <a href="docs.php?cat=<?php echo $page['category_slug']; ?>"><?php echo htmlspecialchars($page['category_name']); ?></a>
    <i class="fas fa-chevron-right breadcrumb-separator"></i>
    <span class="breadcrumb-current"><?php echo htmlspecialchars($page['title']); ?></span>
</div>

<article class="doc-article">
    <header class="doc-header">
        <h1><?php echo htmlspecialchars($page['title']); ?></h1>
        <div class="doc-meta">
            <span class="doc-meta-item"><i class="far fa-folder"></i> <?php echo htmlspecialchars($page['category_name']); ?></span>
            <span class="doc-meta-item"><i class="far fa-clock"></i> <?php echo t('updated', 'Updated'); ?> <?php echo date('M j, Y', strtotime($page['updated_at'])); ?></span>
            <?php if (!empty($toc_data['toc'])): ?>
            <button class="mobile-toc-inline-btn" onclick="toggleMobileTOC()">
                <i class="fas fa-list-ul"></i> <?php echo t('on_this_page', 'Bu Sayfada'); ?>
            </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="doc-container">
        <div class="content-body">
            <?php echo $page_content; ?>
        </div>
        
        <?php if (!empty($toc_data['toc'])): ?>
        <aside class="toc-sidebar">
            <button class="mobile-toc-close" onclick="toggleMobileTOC()" aria-label="<?php echo t('close', 'Kapat'); ?>">
                <i class="fas fa-times"></i>
            </button>
            <?php echo $toc_list; ?>
        </aside>
        <?php endif; ?>
    </div>

 
</article>

<?php include 'includes/footer.php'; ?>
