<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

$query = $_GET['q'] ?? '';
$page_title = t('search_results', 'Search results') . ' "' . htmlspecialchars($query) . '"';

include 'includes/header.php';

if (empty($query)) {
    echo "<div class='search-empty-hero'><h1>" . t('search', 'Arama') . "</h1><p>" . t('search_placeholder', 'Lütfen aramak istediğiniz kelimeyi girin.') . "</p></div>";
} else {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE title LIKE ? OR content LIKE ? ORDER BY title ASC");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    echo "<div class='search-results-header'>";
    echo "<h1 class='search-results-title'>" . t('search_results', 'Arama Sonuçları') . "</h1>";
    echo "<p class='search-results-count'>" . t('found_results', 'Bulunan') . " <span class='search-highlight-count'>" . count($results) . "</span> " . t('matching_pages', 'eşleşen sonuç') . " <mark class='search-term-tag'>\"" . htmlspecialchars($query) . "\"</mark></p>";
    echo "</div>";

    if ($results) {
        echo "<div class='search-results-list'>";
        foreach ($results as $row) {
            ?>
            <a href="docs.php?slug=<?php echo htmlspecialchars($row['slug']); ?>" class="search-result-card">
                <div class="search-result-card-top">
                    <h3 class="search-result-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <span class="search-result-badge"><i class="fas fa-file-alt"></i> Doküman</span>
                </div>
                <?php
                $snippet = strip_tags($row['content']);
                $pos = stripos($snippet, $query);
                $start = max(0, $pos - 100);
                $snippet = mb_substr($snippet, $start, 250);
                ?>
                <p class="search-result-snippet"><?php echo htmlspecialchars($snippet); ?>...</p>
                <div class="search-result-footer">
                    <span><?php echo t('read_more', 'Dokümana Git'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php
        }
        echo "</div>";
    } else {
        echo "<div class='search-no-results'>";
        echo "<div class='search-no-results-icon'>";
        echo "<i class='fas fa-search'></i>";
        echo "</div>";
        echo "<h2>" . t('no_matches', 'Eşleşen sonuç bulunamadı') . "</h2>";
        echo "<p>" . t('try_different', 'Farklı anahtar kelimeler deneyebilir veya ana sayfadaki kategorileri inceleyebilirsiniz:') . " <a href='index.php'>" . t('home', 'Ana Sayfa') . "</a></p>";
        echo "</div>";
    }
}

include 'includes/footer.php';
?>
