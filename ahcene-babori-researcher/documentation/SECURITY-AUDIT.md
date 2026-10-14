# Security & quality review – ahcenebabori.com (theme 1.0.42, core 1.0.27, attendance 1.1.1)

**Scope and limits.** The live site could not be reached from the review environment (outbound access to ahcenebabori.com is blocked), so this is a **source-code audit plus sandbox tests** of the theme and both plugins. It is *not* a live scan: WordPress/PHP versions, other installed plugins, hosting configuration, SSL settings and file permissions must be checked on the server (checklist below).

## Code audit – result
No critical or high-severity vulnerability was found in the theme or plugins.
- Forms/saves: nonces + capability checks on every admin save, delete and export; contact form has nonce, honeypot, signed time token, rate limit, validation, header-injection protection.
- Output: escaping on all dynamic output; the few raw `echo` calls print HTML built by escaped helpers.
- SQL: only one custom query, fully prepared (`$wpdb->prepare`, `esc_like`, integer-cast IDs).
- REST: public endpoints are read-only; the attendance endpoint needs a logged-in administrator + REST nonce (anonymous request → 401, tested).
- Privacy: contact messages and attendance records are non-public post types, hidden from REST and search.
- No `eval`, `unserialize`, shell execution or file uploads handled by the code; the only outbound request is the optional Crossref DOI import (admin only).
- CSV export neutralises spreadsheet-formula injection.

## Fixed in this update
| # | Finding | Severity | Fix |
|---|---|---|---|
| 1 | No security response headers | Medium | `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` (camera allowed only for this site) |
| 2 | WordPress version published in page source | Low | Generator tag removed |
| 3 | Login names discoverable (`/?author=1`, `/wp-json/wp/v2/users`) | Medium | Blocked for anonymous visitors (filter `abc_harden_users`) |
| 4 | Contact rate-limit uses `REMOTE_ADDR`; behind a CDN/proxy all visitors share one counter (5 messages/hour site-wide) | Medium (availability) | Filter `abc_client_ip` to map the real visitor IP |
| 5 | Facebook embed sent a full referrer | Low | `referrerpolicy=strict-origin-when-cross-origin` |
| 6 | Attendance data stayed after deleting the plugin | Low (privacy) | `uninstall.php` removes records, roster and phone page |

## Recommendations (server / site level – cannot be done from the theme)
1. **Updates**: WordPress core, PHP (8.1+), all plugins and themes; remove unused plugins/themes.
2. **Login protection**: two-factor authentication for administrators and login-attempt limiting; strong unique passwords; an administrator account not named "admin". The attendance phone page uses the normal login, so the same protection applies.
3. **HTTPS everywhere**: force HTTPS, enable HSTS (`Strict-Transport-Security`) at the server/CDN; camera scanning requires HTTPS.
4. **XML-RPC**: disable it at the server if you do not use the WordPress mobile app or Jetpack.
5. **Backups**: automatic off-site daily backups, tested restore.
6. **Content-Security-Policy**: add one at the server after testing (must allow `https://www.facebook.com` for the hero video, or use an uploaded MP4 and remove that allowance).
7. **File permissions**: files 644, folders 755, `wp-config.php` 440/400; `DISALLOW_FILE_EDIT` true in `wp-config.php`; block PHP execution in `wp-content/uploads`.
8. **Spam**: add a CAPTCHA/Akismet via the `abc_contact_spam_check` filter if bots appear.
9. **Privacy**: the hero video autoplay loads Facebook on page load (visitors' data go to Facebook). Prefer an uploaded MP4 (no third party) or disable Autoplay, and add a short privacy notice. Delete attendance records and contact messages when no longer needed.
10. **Content**: replace every `[ADD VERIFIED INFORMATION]` placeholder and sample (DEMO) item before indexing; keep Search-Engine visibility on only when ready.

## Quality / performance suggestions
- Use an uploaded, compressed MP4 (≤ 5 MB, 720p) for the hero; add a page-cache + CDN; serve images as WebP with correct sizes.
- Test with PageSpeed Insights / Lighthouse, securityheaders.com, SSL Labs and WPScan, and send me the outputs: I can interpret them and adjust the theme.
- Validate structured data (Google Rich Results Test) and submit the sitemap in Search Console.
