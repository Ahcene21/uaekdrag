# QA report – version 1.0.0

Environment used: real **WordPress 6.5** (SQLite database), PHP 8.3, headless Chromium, axe-core. Date: 2026-10-08.

## Verified (automated / scripted)
| Area | Result |
|---|---|
| Plugin activation, theme activation | OK, no PHP warnings/notices/fatals in `debug.log` from our code |
| PHP syntax | All files lint clean; no PHP ≥ 8 only syntax used (written for 7.4+) |
| Custom post types / taxonomies | All 8 CPTs + archives + singles + taxonomy pages return HTTP 200 with pretty permalinks |
| Admin screens | Overview, Profile, Identifiers, Statistics, CV, Contact, Sample content, Import, add/edit screens for every type, research-area term screen: all render without errors |
| Field saving | Sanitisation verified: DOI normalised, `javascript:` URLs rejected, HTML stripped, relation IDs cast, Year taxonomy auto-synced; invalid ORCID rejected |
| Sample-content installer | Creates 6 term groups, 6 pages, 2 menus, 27 sample entries; relations resolved; idempotent |
| Publication filters / search | Year/type/area/keyword filters and site-wide search (incl. publication type filter) return correct results; REST `abc/v1/*` and `wp/v2/*` OK |
| Contact form | Valid send, invalid input (field errors + sticky values), honeypot (silent), bad nonce, too-fast submission all behave as designed |
| SEO | Meta description, Open Graph, `citation_*` tags, JSON-LD (Person, WebSite, ScholarlyArticle, BreadcrumbList) present |
| Accessibility | axe-core (WCAG 2 A/AA, 2.1 AA, best-practice): **0 violations** on 15 page types × desktop (1366 px) and mobile (390 px), including `?qalang=ar` and French |
| Responsive | No horizontal scroll at 390 px on any tested page; mobile menu, stacked grids verified by screenshot |
| RTL | Arabic locale forced on a test site: `html[dir=rtl][lang=ar]`, mirrored header, hero, cards, grids, breadcrumbs, Noto Sans Arabic |
| External requests | None at runtime (fonts bundled, no CDN, no analytics) |
| Fake data scan | No DOI, ISBN, ORCID/Scopus ID, citation count, h-index, award, grant, secret or credential in the package |

## Not testable in the build environment – please check on your site
- **Polylang** (not downloadable here): language switcher, translated menus, translation linking by the installer, `/en/ /fr/ /ar/` URLs. The code follows Polylang’s public API (`pll_set_post_language`, `pll_save_post_translations`, `pll_the_languages`, `pll_get_post_types`, …); follow `language-setup.md`.
- **WXR import** (`demo-content.xml`): validated as well-formed XML in WXR 1.2 structure; the WordPress Importer plugin was not available to run it. The built-in installer is the tested path.
- **Yoast SEO / Rank Math** interplay (detected by constants; duplicate tags are suppressed when active).
- **Lighthouse** scores and real e-mail delivery (depends on your host; use an SMTP plugin).
- Manual screen-reader testing (NVDA/VoiceOver) and a human proof-read of Arabic and French wording by a native academic.
- WebP generation depends on server libraries.

## Items that need the owner’s confirmation
- The five example statistics (10+ years, PhD, International, Multiple, Academic) were taken from the brief as editable defaults.
- All `[ADD VERIFIED INFORMATION]` markers (education, positions, publications, projects details, service, teaching, awards, certificates, languages, skills).
- Arabic spelling of the name (the demo keeps “Ahcene Babori” in Latin letters).
