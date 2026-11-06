<?php
session_start();
require __DIR__ . '/data.php';

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$photo = null;
foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    if (is_file(__DIR__ . "/profile.$ext")) {
        $photo = "profile.$ext?v=" . filemtime(__DIR__ . "/profile.$ext");
        break;
    }
}
$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($profile['name'])), 0, 2) as $w) {
    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}
$errors = [];
$sent = false;
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    if (!hash_equals($_SESSION['token'], $_POST['token'] ?? '')) {
        http_response_code(400);
        exit('Invalid request.');
    }
    if (!empty($_POST['website'])) {
        exit;
    }
    if ($old['name'] === '' || mb_strlen($old['name']) > 80) {
        $errors['name'] = 'Enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
        $errors['message'] = 'Write between 10 and 2000 characters.';
    }

    if (!$errors) {
        $subject = 'Portfolio message from ' . str_replace(["\r", "\n"], ' ', $old['name']);
        $body = $old['message'] . "\n\nFrom: {$old['name']} <{$old['email']}>";
        $headers = 'Reply-To: ' . str_replace(["\r", "\n"], '', $old['email']);
        if (!@mail($profile['email'], $subject, $body, $headers)) {
            file_put_contents(__DIR__ . '/messages.txt', date('c') . "\n$subject\n$body\n\n", FILE_APPEND | LOCK_EX);
        }
        $_SESSION['sent'] = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '#contact');
        exit;
    }
}
if (!empty($_SESSION['sent'])) {
    $sent = true;
    unset($_SESSION['sent']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($profile['name']) ?> | <?= e($profile['role']) ?></title>
    <meta name="description" content="<?= e($profile['tagline']) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dark-card': '#1a1528',
                        'darker': '#0c0a14',
                        'pink-neon': '#a855f7',
                        'pink-soft': '#c084fc',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen overflow-x-hidden">
    <!-- Floating Hearts -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <span class="heart" style="top: 8%; left: 4%; animation-delay: 0s;">♥</span>
        <span class="heart" style="top: 18%; left: 88%; animation-delay: 1s; font-size: 1.2rem;">♥</span>
        <span class="heart" style="top: 38%; left: 8%; animation-delay: 2s; font-size: 1.8rem;">♥</span>
        <span class="heart" style="top: 55%; left: 92%; animation-delay: 0.5s;">♥</span>
        <span class="heart" style="top: 72%; left: 12%; animation-delay: 1.5s; font-size: 1.3rem;">♥</span>
        <span class="heart" style="top: 85%; left: 78%; animation-delay: 2.5s;">♥</span>
        <span class="heart" style="top: 28%; left: 48%; animation-delay: 3s; font-size: 1rem;">♥</span>
        <span class="heart" style="top: 48%; left: 28%; animation-delay: 1.2s; font-size: 1.6rem;">♥</span>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[95%] max-w-5xl">
        <div class="nav-pill rounded-full px-5 py-3 flex items-center justify-between">
            <a href="#home" class="text-white font-bold text-lg tracking-wide">
                <span class="text-pink-neon">&lt;<?= e($initials) ?>/&gt;</span>
            </a>
            <div class="hidden md:flex items-center gap-5 text-sm font-medium text-gray-300">
                <a href="#home" class="nav-link active hover:text-white transition">HOME</a>
                <a href="#about" class="nav-link hover:text-white transition">ABOUT</a>
                <a href="#education" class="nav-link hover:text-white transition">EDUCATION</a>
                <a href="#projects" class="nav-link hover:text-white transition">PROJECTS</a>
                <a href="#skills" class="nav-link hover:text-white transition">SKILLS</a>
                <a href="#contact" class="nav-link hover:text-white transition">CONTACT</a>
            </div>
            <a href="#contact" class="btn-pink text-white text-sm font-semibold px-4 py-2 rounded-full">
                Hire Me
            </a>
        </div>
    </nav>

    <!-- Hero - tighter gap between text and photo -->
    <section id="home" class="hero-bg relative min-h-[90vh] flex items-center pt-24 pb-16 px-4 md:px-8">
        <div class="max-w-5xl mx-auto w-full flex flex-col lg:flex-row items-center gap-6 lg:gap-8 relative z-10">
            <div class="flex-1">
                <p class="status-badge mb-4">
                    <span class="dot"></span>
                    <?= e($profile['status']) ?>
                </p>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-3 leading-tight">
                    Hi, I'm <span class="text-pink-neon"><?= e(explode(' ', $profile['name'])[0]) ?></span>
                    <span class="inline-block">🌸</span>
                </h1>
                <h2 class="text-xl md:text-2xl font-semibold text-pink-soft mb-3">
                    <?= e($profile['role']) ?>
                </h2>
                <p class="text-gray-400 text-sm mb-5">
                    <?= e($profile['course']) ?> · <?= e($profile['school']) ?>
                </p>
                <p class="text-gray-400 text-base max-w-md mb-8 leading-relaxed">
                    <?= e($profile['tagline']) ?>
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="#projects" class="btn-pink text-white font-semibold px-6 py-3 rounded-full flex items-center gap-2">
                        <i class="fas fa-code"></i> See my work
                    </a>
                    <a href="#contact" class="border border-pink-neon/50 text-pink-soft font-semibold px-6 py-3 rounded-full hover:bg-pink-neon/10 transition">
                        Get in touch
                    </a>
                    <div class="flex gap-2 ml-1">
                        <a href="<?= e($profile['github']) ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-darker border border-pink-neon/30 flex items-center justify-center text-pink-soft hover:bg-pink-neon hover:text-white transition">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="<?= e($profile['facebook']) ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-darker border border-pink-neon/30 flex items-center justify-center text-pink-soft hover:bg-pink-neon hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Profile Image - closer -->
            <div class="relative flex-shrink-0">
                <div class="w-64 h-64 md:w-80 md:h-80 rounded-full overflow-hidden profile-glow relative bg-white">
                    <?php if ($photo): ?>
                        <img src="<?= e($photo) ?>" alt="Photo of <?= e($profile['name']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-6xl font-bold text-pink-neon bg-darker">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-block px-4 md:px-8 py-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="section-title text-2xl md:text-3xl font-bold mb-6 flex items-center gap-3">
                <span class="text-pink-neon">🌸</span> About
            </h2>
            <p class="text-gray-600 leading-relaxed mb-6 text-base md:text-lg"><?= e($profile['about']) ?></p>
            <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                <span class="flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-pink-neon"></i>
                    <?= e($profile['location']) ?>
                </span>
                <a href="<?= e($profile['github']) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-pink-soft transition">
                    <i class="fab fa-github text-pink-neon"></i> GitHub
                </a>
                <a href="<?= e($profile['facebook']) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-pink-soft transition">
                    <i class="fab fa-facebook text-pink-neon"></i> Facebook
                </a>
            </div>
        </div>
    </section>

    <!-- Education Section -->
    <section id="education" class="section-block px-4 md:px-8 py-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="section-title text-2xl md:text-3xl font-bold mb-8 flex items-center gap-3">
                <span class="text-pink-neon">🌸</span> Education
            </h2>
            <div class="relative border-l-2 border-pink-neon/40 pl-6 space-y-8">
                <?php foreach ($education as $i => $ed): ?>
                <div class="relative">
                    <div class="absolute -left-[31px] w-4 h-4 rounded-full <?= $i === 0 ? 'bg-pink-neon shadow-neon-pink' : 'bg-pink-soft' ?>"></div>
                    <div>
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 mb-1">
                            <h3 class="font-semibold text-lg text-gray-800"><?= e($ed['what']) ?></h3>
                            <span class="text-pink-neon text-sm font-medium whitespace-nowrap"><?= e($ed['years']) ?></span>
                        </div>
                        <p class="text-gray-500 text-sm"><?= e($ed['where']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="section-block px-4 md:px-8 py-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="section-title text-2xl md:text-3xl font-bold mb-8 flex items-center gap-3">
                <span class="text-pink-neon">🌸</span> Projects
            </h2>
            <div class="grid sm:grid-cols-2 gap-5">
                <?php foreach ($projects as $i => $p): ?>
                <div class="project-card">
                    <?php if (!empty($p['image']) && is_file(__DIR__ . '/' . $p['image'])): ?>
                        <button type="button"
                                class="project-photo mb-4 project-view-btn"
                                data-src="<?= e($p['image']) ?>"
                                data-title="<?= e($p['title']) ?>"
                                data-desc="<?= e($p['desc']) ?>"
                                data-stack="<?= e(implode(' · ', $p['stack'])) ?>"
                                aria-label="View <?= e($p['title']) ?>">
                            <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>">
                            <span class="project-view-overlay">
                                <i class="fas fa-expand"></i> View project
                            </span>
                        </button>
                    <?php else: ?>
                        <div class="project-photo project-photo-placeholder mb-4">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <span class="text-pink-neon text-[10px] uppercase tracking-wider font-semibold"><?= e($p['type']) ?></span>
                        <h3 class="font-semibold text-lg mt-1"><?= e($p['title']) ?></h3>
                    </div>
                    <p class="text-sm mb-3 leading-relaxed"><?= e($p['desc']) ?></p>
                    <div class="flex flex-wrap gap-1.5 mt-auto">
                        <?php foreach ($p['stack'] as $t): ?>
                            <span class="skill-tag text-[10px] px-2.5 py-1 rounded-full"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="section-block px-4 md:px-8 py-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="section-title text-2xl md:text-3xl font-bold mb-8 flex items-center gap-3">
                <span class="text-pink-neon">🌸</span> Skills
            </h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <?php foreach ($skills as $group => $list): ?>
                <div class="skill-group">
                    <h3 class="text-pink-soft font-semibold mb-4 text-lg"><?= e($group) ?></h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($list as $s): ?>
                            <span class="skill-tag px-3 py-1.5 rounded-lg text-sm"><?= e($s['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-block px-4 md:px-8 py-16 pb-20">
        <div class="max-w-4xl mx-auto">
            <h2 class="section-title text-2xl md:text-3xl font-bold mb-3 flex items-center gap-3">
                <span class="text-pink-neon">🌸</span> Contact
            </h2>
            <p class="text-gray-500 mb-6">Get in touch with me</p>
            
            <?php if ($sent): ?>
                <div class="mb-5 p-4 rounded-xl bg-pink-neon/10 border border-pink-neon/30 text-pink-soft text-sm max-w-lg">
                    Thank you! Your message was sent.
                </div>
            <?php endif; ?>
            
            <form method="post" action="#contact" class="space-y-4 max-w-lg" novalidate>
                <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">
                <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                
                <div>
                    <input type="text" name="name" id="name" placeholder="Your Name" required
                           value="<?= e($old['name']) ?>"
                           class="form-input w-full rounded-xl px-4 py-3 focus:outline-none transition">
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="email" name="email" id="email-in" placeholder="Your Email" required
                           value="<?= e($old['email']) ?>"
                           class="form-input w-full rounded-xl px-4 py-3 focus:outline-none transition">
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <textarea name="message" id="message" rows="4" placeholder="Your Message" required
                              class="form-input w-full rounded-xl px-4 py-3 focus:outline-none transition resize-none"><?= e($old['message']) ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= e($errors['message']) ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-pink text-white font-semibold px-8 py-3 rounded-xl flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
            
            <div class="mt-6 text-sm text-gray-500">
                <a href="mailto:<?= e($profile['email']) ?>" class="flex items-center gap-2 hover:text-pink-soft transition">
                    <i class="fas fa-envelope text-pink-neon"></i>
                    <?= e($profile['email']) ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="px-4 md:px-8 py-8 text-center border-t border-pink-neon/10">
        <div class="max-w-4xl mx-auto">
            <p class="text-gray-500 text-sm">
                © <?= date('Y') ?> <span class="text-pink-neon font-semibold"><?= e($profile['name']) ?></span>
                <span class="text-pink-neon">♥</span>
            </p>
        </div>
    </footer>

    <!-- Project Lightbox Modal -->
    <div id="project-modal" class="project-modal" hidden>
        <div class="project-modal-backdrop" data-close></div>
        <div class="project-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <button type="button" class="project-modal-close" data-close aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <img id="modal-img" src="" alt="">
            <div class="project-modal-info">
                <h3 id="modal-title"></h3>
                <p id="modal-stack" class="modal-stack"></p>
                <p id="modal-desc"></p>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
