<?php
require_once __DIR__ . '/includes/functions.php';

$site            = config('site');
$pageTitle       = 'Privacy Policy — SportsbyA Tech';
$pageDescription = 'How SportsbyA Tech collects, uses and protects your personal information.';

require __DIR__ . '/includes/header.php';
?>

<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-14">
        <h1 class="text-4xl font-extrabold tracking-tight">Privacy Policy</h1>
        <p class="mt-3 text-slate-600">Last updated <?= date('F j, Y') ?></p>
    </div>
</section>

<section class="py-14">
    <div class="prose-content mx-auto max-w-4xl px-4 text-slate-700 sm:px-6 lg:px-8">
        <p><strong><?= e($site['legal_name']) ?></strong> ("SportsbyA Tech", "we", "us" or "our") is committed
            to protecting your privacy. This Privacy Policy explains what personal information we collect, how
            we use it and the choices you have.</p>

        <h2>1. Information we collect</h2>
        <ul>
            <li><strong>Information you provide:</strong> when you submit our contact or enquiry forms we
                collect your name, mobile number, email address and the contents of your message.</li>
            <li><strong>Technical information:</strong> we may automatically collect limited technical data
                such as your IP address, browser type and the date and time of your request for security and
                analytics purposes.</li>
        </ul>

        <h2>2. How we use your information</h2>
        <ul>
            <li>To respond to your enquiries and provide the information, demos or services you request.</li>
            <li>To communicate with you about our products, solutions and updates where you have asked us to.</li>
            <li>To operate, maintain, secure and improve our website.</li>
            <li>To comply with legal obligations.</li>
        </ul>

        <h2>3. Legal basis</h2>
        <p>We process your personal information on the basis of your consent (which you provide by submitting a
            form), to take steps at your request prior to entering into a contract, and for our legitimate
            interests in operating and improving our business.</p>

        <h2>4. Sharing your information</h2>
        <p>We do not sell your personal information. We do not share your details with third parties for their
            own marketing. We may share information with trusted service providers who help us operate our
            website and communications, and where required by law.</p>

        <h2>5. Data retention</h2>
        <p>We retain enquiry information only for as long as necessary to respond to and follow up on your
            request, and to comply with our legal and accounting obligations.</p>

        <h2>6. Security</h2>
        <p>We implement reasonable technical and organisational measures to protect your information against
            unauthorised access, alteration, disclosure or destruction. However, no method of transmission
            over the internet is completely secure.</p>

        <h2>7. Your rights</h2>
        <p>Subject to applicable law, you may request access to, correction of, or deletion of your personal
            information, and you may withdraw consent at any time. To exercise these rights, contact us using
            the details below.</p>

        <h2>8. Cookies</h2>
        <p>Our website uses only essential functionality. If we introduce analytics or marketing cookies in
            future, we will update this policy and, where required, request your consent.</p>

        <h2>9. Children's privacy</h2>
        <p>Where our services involve athletes who are minors, personal information is processed only with
            appropriate consent from a parent, guardian or sponsoring institution, in line with applicable
            safeguarding requirements.</p>

        <h2>10. Changes to this policy</h2>
        <p>We may update this Privacy Policy from time to time. The latest version will always be available on
            this page with a revised "Last updated" date.</p>

        <h2>11. Contact us</h2>
        <p>If you have any questions about this Privacy Policy or how we handle your information, contact us at
            <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a> or call
            <a href="tel:<?= e(preg_replace('/\s+/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a>.</p>

        <p class="text-sm text-slate-500"><em>This document is provided as a general template and does not
            constitute legal advice. Please have it reviewed by a qualified legal professional before
            publishing.</em></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
