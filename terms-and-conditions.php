<?php
require_once __DIR__ . '/includes/functions.php';

$site            = config('site');
$pageTitle       = 'Terms & Conditions — SportsbyA Tech';
$pageDescription = 'The terms and conditions governing the use of the SportsbyA Tech website and services.';

require __DIR__ . '/includes/header.php';
?>

<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-14">
        <h1 class="text-4xl font-extrabold tracking-tight">Terms &amp; Conditions</h1>
        <p class="mt-3 text-slate-600">Last updated <?= date('F j, Y') ?></p>
    </div>
</section>

<section class="py-14">
    <div class="prose-content mx-auto max-w-4xl px-4 text-slate-700 sm:px-6 lg:px-8">
        <p>These Terms &amp; Conditions ("Terms") govern your access to and use of the website operated by
            <strong><?= e($site['legal_name']) ?></strong> ("SportsbyA Tech", "we", "us" or "our") and any
            services, products or content made available through it. By accessing or using this website you
            agree to be bound by these Terms. If you do not agree, please do not use the website.</p>

        <h2>1. Use of the website</h2>
        <p>You agree to use the website only for lawful purposes and in a manner that does not infringe the
            rights of, or restrict or inhibit the use and enjoyment of, the website by any third party. You
            must not attempt to gain unauthorised access to any part of the website, its servers, or any
            system or network connected to it.</p>

        <h2>2. Intellectual property</h2>
        <p>All content on this website — including text, graphics, logos, images, software and the design and
            arrangement thereof — is the property of <?= e($site['legal_name']) ?> or its licensors and is
            protected by applicable intellectual property laws. You may not reproduce, distribute, modify or
            create derivative works from any content without our prior written consent.</p>

        <h2>3. Services and demonstrations</h2>
        <p>Information about our products and solutions (including performance analytics, event management and
            community platforms) is provided for general information. Features, availability and pricing may
            change without notice. Any pilot, demonstration or proof-of-concept is subject to a separate
            written agreement.</p>

        <h2>4. User submissions</h2>
        <p>When you submit information through our contact or enquiry forms, you confirm that the information
            provided is accurate and that you are authorised to share it. We may contact you using the details
            you provide in order to respond to your enquiry.</p>

        <h2>5. Third-party links</h2>
        <p>The website may contain links to third-party websites, including our sister products. We are not
            responsible for the content, policies or practices of any third-party website and provide such
            links for convenience only.</p>

        <h2>6. Disclaimer of warranties</h2>
        <p>The website is provided on an "as is" and "as available" basis without warranties of any kind,
            whether express or implied. We do not warrant that the website will be uninterrupted, error-free
            or free of harmful components.</p>

        <h2>7. Limitation of liability</h2>
        <p>To the fullest extent permitted by law, <?= e($site['legal_name']) ?> shall not be liable for any
            indirect, incidental, special or consequential damages arising out of or in connection with your
            use of the website.</p>

        <h2>8. Governing law</h2>
        <p>These Terms are governed by and construed in accordance with the laws of India, and any disputes
            shall be subject to the exclusive jurisdiction of the courts of Thiruvananthapuram, Kerala.</p>

        <h2>9. Changes to these Terms</h2>
        <p>We may revise these Terms from time to time. The updated version will be posted on this page with a
            revised "Last updated" date. Your continued use of the website constitutes acceptance of the
            revised Terms.</p>

        <h2>10. Contact</h2>
        <p>For any questions about these Terms, contact us at
            <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.</p>

        <p class="text-sm text-slate-500"><em>This document is provided as a general template and does not
            constitute legal advice. Please have it reviewed by a qualified legal professional before
            publishing.</em></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
