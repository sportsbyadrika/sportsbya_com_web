<?php
declare(strict_types=1);

/**
 * Minimal, dependency-free SMTP mailer.
 *
 * The production host has PHP's mail() function disabled, so the contact form
 * delivers messages through an authenticated SMTP connection. This implements
 * the same handshake PHPMailer uses (EHLO, STARTTLS / implicit SSL, AUTH LOGIN,
 * multipart/alternative body) without requiring Composer or any vendored
 * dependency — useful on locked-down shared hosting.
 */
final class Mailer
{
    /** @var array<string,mixed> */
    private array $cfg;
    public string $error = '';

    /** @param array<string,mixed> $cfg the "mail" config section */
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * @param string[] $recipients
     */
    public function send(
        array $recipients,
        string $subject,
        string $htmlBody,
        string $textBody = '',
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        $cfg  = $this->cfg;
        $host = $cfg['host'];
        $port = (int) $cfg['port'];
        $enc  = strtolower((string) $cfg['encryption']);

        $recipients = array_values(array_filter(array_map('trim', $recipients)));
        if (!$recipients) {
            $this->error = 'No recipients configured.';
            return false;
        }

        $remote  = ($enc === 'ssl') ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
        $context = stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true],
        ]);

        $fp = @stream_socket_client($remote, $errno, $errstr, 25, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            $this->error = "Connection to SMTP server failed: {$errstr} ({$errno})";
            return false;
        }
        stream_set_timeout($fp, 25);

        try {
            $this->expect($fp, ['220']);

            $ehloHost = $_SERVER['SERVER_NAME'] ?? gethostname() ?: 'localhost';
            $this->cmd($fp, "EHLO {$ehloHost}");
            $this->expect($fp, ['250']);

            if ($enc === 'tls') {
                $this->cmd($fp, 'STARTTLS');
                $this->expect($fp, ['220']);
                $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (!stream_socket_enable_crypto($fp, true, $crypto)) {
                    throw new RuntimeException('TLS negotiation failed.');
                }
                $this->cmd($fp, "EHLO {$ehloHost}");
                $this->expect($fp, ['250']);
            }

            if (!empty($cfg['username'])) {
                $this->cmd($fp, 'AUTH LOGIN');
                $this->expect($fp, ['334']);
                $this->cmd($fp, base64_encode((string) $cfg['username']));
                $this->expect($fp, ['334']);
                $this->cmd($fp, base64_encode((string) $cfg['password']));
                $this->expect($fp, ['235']);
            }

            $this->cmd($fp, 'MAIL FROM:<' . $cfg['from_email'] . '>');
            $this->expect($fp, ['250']);

            foreach ($recipients as $rcpt) {
                $this->cmd($fp, "RCPT TO:<{$rcpt}>");
                $this->expect($fp, ['250', '251']);
            }

            $this->cmd($fp, 'DATA');
            $this->expect($fp, ['354']);

            $message = $this->buildMessage($recipients, $subject, $htmlBody, $textBody, $replyToEmail, $replyToName);
            // Dot-stuffing: any line that begins with "." gets an extra ".".
            $message = preg_replace('/(^|\r\n)\./', '$1..', $message) ?? $message;
            fwrite($fp, $message . "\r\n.\r\n");
            $this->expect($fp, ['250']);

            $this->cmd($fp, 'QUIT');
            fclose($fp);
            return true;
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            @fclose($fp);
            return false;
        }
    }

    /** @param resource $fp */
    private function cmd($fp, string $line): void
    {
        fwrite($fp, $line . "\r\n");
    }

    /**
     * Read a (possibly multi-line) SMTP reply and assert its code.
     *
     * @param resource $fp
     * @param string[] $codes
     */
    private function expect($fp, array $codes): void
    {
        $response = '';
        while (($line = fgets($fp, 515)) !== false) {
            $response .= $line;
            // Final line of a reply has a space at position 3 ("250 OK"),
            // continuation lines have a hyphen ("250-...").
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('Unexpected SMTP reply: ' . trim($response));
        }
    }

    /** @param string[] $recipients */
    private function buildMessage(
        array $recipients,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $replyToEmail,
        ?string $replyToName
    ): string {
        $cfg      = $this->cfg;
        $boundary = 'sba_' . bin2hex(random_bytes(10));
        $domain   = substr(strrchr($cfg['from_email'], '@') ?: '@sportsbya.com', 1);

        if ($textBody === '') {
            $textBody = trim(html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8'));
        }

        $headers = [];
        $headers[] = 'From: ' . $this->encodeWord((string) $cfg['from_name']) . ' <' . $cfg['from_email'] . '>';
        $headers[] = 'To: ' . implode(', ', array_map(static fn ($r) => "<{$r}>", $recipients));
        if ($replyToEmail) {
            $name = $replyToName ? $this->encodeWord($replyToName) . ' ' : '';
            $headers[] = 'Reply-To: ' . $name . '<' . $replyToEmail . '>';
        }
        $headers[] = 'Subject: ' . $this->encodeWord($subject);
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($this->normalize($textBody)));
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($this->normalize($htmlBody)));
        $body .= "--{$boundary}--";

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function normalize(string $text): string
    {
        return str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $text);
    }

    private function encodeWord(string $text): string
    {
        return preg_match('/[\x80-\xFF]/', $text)
            ? '=?UTF-8?B?' . base64_encode($text) . '?='
            : $text;
    }
}
