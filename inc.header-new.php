<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$currentPath = strtolower((string)($_SERVER['PHP_SELF'] ?? ''));
$parentLoginUrl = defined('PARENT_URL') ? PARENT_URL : (SITE_URL . 'parent/');

$menuItems = [
    ['label' => 'Home', 'url' => SITE_URL . 'index.php', 'active' => ['index.php', '']],
    ['label' => 'Pricing', 'url' => SITE_URL . 'package.php', 'active' => ['package.php']],
    ['label' => 'FAQ', 'url' => SITE_URL . 'faq.php', 'active' => ['faq.php']],
    ['label' => 'Contact', 'url' => SITE_URL . 'contact-us.php', 'active' => ['contact-us.php']],
    ['label' => 'Sign In', 'url' => SITE_URL . 'login.php', 'active' => ['login.php']],
    ['label' => 'Parent Login', 'url' => $parentLoginUrl, 'active' => ['parent/index.php', 'parent/login.php']],
];

$isMenuItemActive = function (array $item) use ($currentPage, $currentPath) {
    foreach (($item['active'] ?? []) as $active) {
        $active = strtolower(trim((string)$active));
        if ($active === '') {
            if ($currentPage === '' || $currentPage === 'index.php') {
                return true;
            }
            continue;
        }

        $activeBase = basename($active);
        if ($activeBase === strtolower($currentPage)) {
            return true;
        }

        // PHP 7.x compatible version of str_contains()
        if (strpos($currentPath, '/' . ltrim($active, '/')) !== false) {
            return true;
        }
    }
    return false;
};
?>

<header class="glass-header">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <a href="<?= SITE_URL ?>" class="flex items-center gap-2 text-slate-900 no-underline">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold">B</span>
                <span class="text-lg md:text-xl font-extrabold tracking-tight">Best School Page</span>
            </a>

            <nav class="hidden md:flex items-center gap-2">
                <?php foreach ($menuItems as $item):
                    $isActive = $isMenuItemActive($item);
                ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>"
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= $isActive ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-indigo-700' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <button id="mobileMenuToggle" type="button" class="md:hidden text-slate-700 hover:text-indigo-700 text-2xl" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div id="mobileMenu" class="md:hidden hidden pb-4">
            <div class="grid gap-2">
                <?php foreach ($menuItems as $item):
                    $isActive = $isMenuItemActive($item);
                ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>"
                       class="px-4 py-3 rounded-lg text-sm font-semibold transition <?= $isActive ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-indigo-700' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</header>

<script>
(function() {
    var toggle = document.getElementById('mobileMenuToggle');
    var menu = document.getElementById('mobileMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function() {
        var isHidden = menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    });
})();
</script>