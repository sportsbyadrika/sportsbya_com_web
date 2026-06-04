<?php
$site = config('site');
$year = date('Y');
?>
    </main>

    <footer class="bg-ink text-slate-300">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-5">
                <!-- Brand -->
                <div class="lg:col-span-2">
                    <a href="<?= url('') ?>" class="flex items-center gap-2 font-bold text-lg text-white">
                        <img src="<?= url('sba-logo.png') ?>" alt="SportsbyA Tech logo" width="40" height="40" class="h-10 w-10 rounded-lg bg-white/10 object-contain">
                        <span>SportsbyA <span class="text-brand">Tech</span></span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">
                        <?= e($site['legal_name']) ?> builds platforms that uplift athletes, coaches and
                        organizations — from performance analytics to event management and community tools.
                    </p>
                    <div class="mt-5 flex items-center gap-3">
                        <?php if (!empty($site['social']['linkedin'])): ?>
                            <a href="<?= e($site['social']['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"
                               class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-brand"><i class="bi bi-linkedin"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site['social']['instagram'])): ?>
                            <a href="<?= e($site['social']['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"
                               class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-brand"><i class="bi bi-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site['social']['youtube'])): ?>
                            <a href="<?= e($site['social']['youtube']) ?>" target="_blank" rel="noopener" aria-label="YouTube"
                               class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-brand"><i class="bi bi-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Company -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Company</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="<?= url('solutions') ?>" class="transition hover:text-brand">Solutions</a></li>
                        <li><a href="<?= url('clients') ?>" class="transition hover:text-brand">Our Clients</a></li>
                        <li><a href="<?= url('blog') ?>" class="transition hover:text-brand">Blog</a></li>
                        <li><a href="<?= url('about') ?>" class="transition hover:text-brand">About Us</a></li>
                        <li><a href="<?= url('contact') ?>" class="transition hover:text-brand">Contact</a></li>
                    </ul>
                </div>

                <!-- Our Brands -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Our Brands</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <?php foreach ($site['brands'] as $brand): ?>
                            <li><a href="<?= e($brand['url']) ?>" target="_blank" rel="noopener" class="transition hover:text-brand"><?= e($brand['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Get in touch</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex items-start gap-2"><i class="bi bi-envelope-fill mt-0.5 text-brand"></i><a href="mailto:<?= e($site['email']) ?>" class="transition hover:text-brand"><?= e($site['email']) ?></a></li>
                        <li class="flex items-start gap-2"><i class="bi bi-telephone-fill mt-0.5 text-brand"></i><a href="tel:<?= e(preg_replace('/\s+/', '', $site['phone'])) ?>" class="transition hover:text-brand"><?= e($site['phone']) ?></a></li>
                        <li class="flex items-start gap-2"><i class="bi bi-geo-alt-fill mt-0.5 text-brand"></i><span><?= e($site['address']) ?></span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 text-sm text-slate-400 md:flex-row">
                <p>© <?= $year ?> <?= e($site['legal_name']) ?>. All rights reserved.</p>
                <div class="flex items-center gap-5">
                    <a href="<?= url('terms-and-conditions') ?>" class="transition hover:text-brand">Terms &amp; Conditions</a>
                    <a href="<?= url('privacy-policy') ?>" class="transition hover:text-brand">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile nav toggle
        (function () {
            var btn = document.getElementById('navToggle');
            var menu = document.getElementById('mobileNav');
            if (btn && menu) {
                btn.addEventListener('click', function () {
                    menu.classList.toggle('hidden');
                });
            }
        })();
    </script>
</body>
</html>
