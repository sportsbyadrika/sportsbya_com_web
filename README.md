# sportsbya_com_web

Company website for **SportsByA Tech (OPC) Private Limited** — rebuilt in PHP with
clean (extension-less) URLs and Tailwind CSS, plus an admin area for managing
**Our Clients** and the **Blog**.

## Tech stack

- **PHP 8** (no framework) with shared `includes/` for header, footer, config, DB and mail
- **Tailwind CSS v4** — compiled to a static file (`assets/css/app.css`), no runtime CDN
- **MySQL** (PDO) for admin-managed Clients & Blog and stored contact enquiries
- **SMTP mailer** (`includes/Mailer.php`) for the contact form — self-contained, because
  the production host has PHP's `mail()` disabled
- Clean URLs via `.htaccess` (e.g. `/solutions`, `/about`, `/blog/my-post`)

## Site structure

| URL | File | Notes |
|-----|------|-------|
| `/` | `index.php` | Home |
| `/solutions` | `solutions.php` | Offerings, platforms, analytics, integrations |
| `/clients` | `clients.php` | Our Clients (from DB) |
| `/blog`, `/blog/{slug}` | `blog.php` | Blog list + single post (from DB) |
| `/about` | `about.php` | About Us / team |
| `/contact` | `contact.php` | Contact form (Name, **Mobile**, Email, Message) |
| `/terms-and-conditions` | `terms-and-conditions.php` | Linked in footer |
| `/privacy-policy` | `privacy-policy.php` | Linked in footer |
| `/admin/...` | `admin/` | Admin login + Clients/Blog management (not linked publicly) |

Primary nav: **Solutions · Our Clients · Blog · About Us · Contact** (+ a *Get Started* button).

## First-time setup / deployment

1. **Upload** the repository contents to the site's web root (e.g. `public_html/sportsbya.com/`).
   `node_modules/` is not needed on the server.
2. **Create a MySQL database** and user in cPanel.
3. **Configure** `includes/config.php` — fill in the `db`, `mail` and (optionally) `site`
   sections. Every value can also be supplied via environment variables.
   - `mail.recipients` accepts **1–3** addresses; contact-form submissions are emailed to all of them.
   - Set your SMTP `host`/`port`/`username`/`password` (e.g. Gmail/Workspace app password, or the host's SMTP).
4. **Run the installer:** visit `/install` once. It creates the tables and the first
   admin user (defaults: `admin` / `ChangeMe@123` — change these in `config.php` before running).
5. **Delete `install.php`** from the server.
6. **Sign in** at `/admin/login` to add clients and write blog posts.

## Rebuilding the CSS

The compiled stylesheet (`assets/css/app.css`) is committed, so the site works as-is.
After changing classes in any `.php` file, rebuild it:

```bash
npm install      # first time only
npm run build:css   # or: npm run watch:css
```

## Security notes

- `includes/` and `sql/` are blocked from direct web access; `uploads/` cannot execute scripts.
- Admin uses session auth, CSRF tokens, hashed passwords and validated image uploads.
- Blog HTML is sanitised on save (scripts, inline handlers and `javascript:` URLs removed).
- The legal pages are sensible templates — have them reviewed by a legal professional.
