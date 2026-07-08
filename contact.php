<?php
require_once __DIR__ . '/includes/functions.php';

$site            = config('site');
$pageTitle       = 'Contact Us — SportsbyA Tech';
$pageDescription = 'Connect with the SportsbyA Tech team for product enquiries, partnerships and support.';

[$formTs, $formSig] = form_token();
$turnstileKey = config('security')['turnstile_site_key'] ?? '';

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid items-center gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                    <i class="bi bi-chat-dots-fill text-brand"></i> Talk to the team
                </span>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Let's build better sports experiences together</h1>
                <p class="mt-5 text-lg text-slate-600">Share what you're looking for — performance analytics, event tech or community platforms. We'll respond quickly with the right next step.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-4 py-2 text-sm text-slate-700"><i class="bi bi-envelope-fill text-brand"></i> <?= e($site['email']) ?></span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-4 py-2 text-sm text-slate-700"><i class="bi bi-telephone-fill text-brand"></i> <?= e($site['phone']) ?></span>
                </div>
            </div>
            <div class="lg:col-span-5">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand/10 text-brand"><i class="bi bi-lightning-fill text-lg"></i></div>
                        <div>
                            <p class="text-xs text-slate-500">Average reply time</p>
                            <p class="font-semibold">Under 24 hours</p>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-2 text-sm text-slate-600"><i class="bi bi-geo-alt-fill text-brand"></i> <?= e($site['address']) ?></p>
                    <p class="mt-2 flex items-center gap-2 text-sm text-slate-600"><i class="bi bi-calendar-event-fill text-brand"></i> <?= e($site['hours']) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT FORM -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <h2 class="text-3xl font-extrabold tracking-tight">Tell us about your needs</h2>
                <p class="mt-3 text-slate-600">Whether you're a federation, club or coach, we tailor solutions for your sport. Share a few details and we'll schedule a demo or follow-up call.</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600">
                    <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill mt-0.5 text-green-500"></i><span>We don't share your details with third parties.</span></li>
                    <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill mt-0.5 text-green-500"></i><span>A specialist — not a bot — reads every message.</span></li>
                </ul>
            </div>

            <div class="lg:col-span-7">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 sm:p-8">
                    <form id="contactForm" action="<?= url('contact-handler') ?>" method="POST" novalidate>
                        <!-- Honeypot (hidden from humans) -->
                        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <!-- Anti-bot: signed time token + JS-proof field -->
                        <input type="hidden" name="form_ts" value="<?= e($formTs) ?>">
                        <input type="hidden" name="form_sig" value="<?= e($formSig) ?>">
                        <input type="hidden" name="js" id="jsField" value="">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="contactName" class="block text-sm font-medium text-slate-700">Name</label>
                                <input type="text" id="contactName" name="name" required placeholder="Your full name"
                                       class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20">
                            </div>
                            <div>
                                <label for="contactMobile" class="block text-sm font-medium text-slate-700">Mobile number</label>
                                <input type="tel" id="contactMobile" name="mobile" required placeholder="+91 98765 43210"
                                       inputmode="tel" pattern="[0-9+\-\s()]{7,20}" autocomplete="tel"
                                       class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20">
                            </div>
                        </div>
                        <div class="mt-5">
                            <label for="contactEmail" class="block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" id="contactEmail" name="email" required placeholder="you@example.com"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20">
                        </div>
                        <div class="mt-5">
                            <label for="contactMessage" class="block text-sm font-medium text-slate-700">How can we help?</label>
                            <textarea id="contactMessage" name="message" rows="4" required placeholder="Tell us about your sport, goals or the problem you're solving."
                                      class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
                        </div>
                        <div class="mt-5 space-y-3">
                            <label class="flex items-start gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="consent_notifications" value="1" required
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span>I hereby authorize to send notifications on SMS/Messages/Promotional/Informational messages</span>
                            </label>
                            <label class="flex items-start gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="consent_terms" value="1" required
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span>By submitting the form, you've read and accepted our
                                    <a href="<?= url('terms-and-conditions') ?>" class="font-medium text-brand hover:underline">terms and conditions</a>
                                    and our
                                    <a href="<?= url('privacy-policy') ?>" class="font-medium text-brand hover:underline">privacy policy</a>.</span>
                            </label>
                        </div>
                        <?php if ($turnstileKey): ?>
                            <div class="cf-turnstile mt-5" data-sitekey="<?= e($turnstileKey) ?>" data-theme="light"></div>
                        <?php endif; ?>
                        <div class="mt-6 flex flex-wrap items-center gap-4">
                            <button type="submit" id="contactSubmit"
                                    class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60">
                                <i class="bi bi-send-fill"></i> Send message
                            </button>
                            <p id="contactAlert" class="hidden rounded-lg px-4 py-2 text-sm" role="alert"></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($turnstileKey): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
<script>
    (function () {
        var form = document.getElementById('contactForm');
        var alertBox = document.getElementById('contactAlert');
        var submit = document.getElementById('contactSubmit');
        if (!form) return;

        // Prove a real browser rendered and ran this page (blocks non-JS bots).
        var jsField = document.getElementById('jsField');
        if (jsField) { jsField.value = 'ok'; }

        function showAlert(message, ok) {
            alertBox.textContent = message;
            alertBox.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'bg-red-50', 'text-red-700');
            alertBox.classList.add(ok ? 'bg-green-50' : 'bg-red-50', ok ? 'text-green-700' : 'text-red-700');
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            alertBox.classList.add('hidden');
            submit.disabled = true;
            var original = submit.innerHTML;
            submit.innerHTML = '<i class="bi bi-arrow-repeat"></i> Sending…';

            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form)
                });
                var data = await response.json().catch(function () { return {}; });
                if (response.ok && data.ok) {
                    showAlert(data.message || 'Thanks! We will connect with you soon.', true);
                    form.reset();
                } else {
                    showAlert(data.message || 'Something went wrong. Please try again.', false);
                }
            } catch (err) {
                showAlert('Something went wrong. Please try again in a moment.', false);
            } finally {
                submit.disabled = false;
                submit.innerHTML = original;
            }
        });
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
