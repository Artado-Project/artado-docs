<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        header('Location: admin.php');
        exit;
    } else {
        $error = t('invalid_credentials', 'Invalid username or password.');
    }
}

$page_title = t('login', 'Login');
$hide_sidebar = true;
include 'includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo-badge">
                <img src="assets/img/logo.svg" alt="Artado" class="auth-logo-img">
            </div>
            <h1 class="auth-title"><?php echo t('admin_access', 'Yönetici Girişi'); ?></h1>
            <p class="auth-subtitle"><?php echo t('sign_in_continue', 'Devam etmek için lütfen giriş yapın'); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert-error">
                <i class="fas fa-exclamation-circle"></i> <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label class="form-label"><?php echo t('username', 'Kullanıcı Adı'); ?></label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" class="form-control with-icon" required placeholder="admin" autocomplete="username">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo t('password', 'Şifre'); ?></label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-control with-icon" required placeholder="••••••••" autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-auth-submit">
                <span><?php echo t('sign_in', 'Giriş Yap'); ?></span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="auth-footer">
            <a href="index.php" class="auth-back-link">
                <i class="fas fa-arrow-left"></i> <span><?php echo t('back_to_docs', 'Dokümantasyona Dön'); ?></span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
