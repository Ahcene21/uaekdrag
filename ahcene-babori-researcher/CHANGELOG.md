# Changelog

## Core 1.0.32 – 2026-10-18 – About Timeline and the About link
- Menu items that point to the **About Timeline** (its entries, sections, or addresses such as `/about-timeline/`, `/abc_career/…`, or labels like "Timeline / Parcours / الجدول الزمني") now lead to the About page, because the timeline has no page of its own.
- The About page **always shows the About Timeline entries**: if the page text does not contain the timeline (old content, a page created by hand), the timeline is appended automatically.

## Core 1.0.31 – 2026-10-18 – "About me" menu link, definitive fix
- The About entry of the main menu now works whatever it is: a page, a **custom link** (wrong address, "#", an old URL), a draft/deleted page, in any language. Any menu item titled like "About / About me / À propos / نبذة عني / من أنا / Mon profil" that points inside the site (or nowhere) is sent to the real About page (the page chosen under Researcher → Profile, the page with the About key, or a page with a usual slug).
- If the site has **no About page at all** it is created automatically (it shows the About timeline) the first time it is needed.
- Aliases `/about-me/`, `/a-propos/`, `/nubdha-anni/`, `/نبذة-عني/` … that are not real pages redirect to the About page.

## 1.0.47 – 2026-10-18 (core 1.0.30)
- **Covers appear automatically on /publications/**: when you (logged in) open the publications page, every publication that has a PDF but no cover gets its cover created in the background from the PDF's first page, then the page refreshes. Visitors only see the finished covers (nothing extra is loaded for them).
- **External PDF links are supported**: for publications whose PDF is a link (not an uploaded file) the site downloads that PDF once through a protected, editors-only route (the address is read from the saved publication, never from the request) to render page 1. Links must be public direct PDF files.

## 1.0.46 – 2026-10-18 (core 1.0.29)
- **New publications page design**: cards with the **cover of the article – the first page of its PDF** – on top, year badge, Open-Access ribbon, then type, title, authors, journal and PDF / DOI / repository buttons. Publications without a cover show a neutral placeholder. The cover also appears beside the title on the publication page.
- **Cover generation**: *Researcher → Publication covers* creates the covers of all uploaded PDFs in one click; each publication also has a *Generate cover from the PDF* button (and a manual cover image field). The first page is rendered in the browser with the bundled PDF.js (Apache-2.0), so no server software is needed and nothing leaves your site. Only PDFs uploaded to the Media Library can be processed (not external links).

## Share & Statistics 1.1.1 – 2026-10-18
- Fix: some blog articles showed no share buttons (posts not rendered by the blog-article template, e.g. standard posts, translated or imported ones). The buttons are now also appended to the article content itself, once per article, for blog articles and standard posts (filter `abi_share_types` to change the types).

## 1.0.45 – 2026-10-18 (Share & Statistics 1.1.0)
- **Site visit counter in the footer** ("Site visits: N", total page views since the plugin was activated; can be switched off; `[abi_visits]` shortcode for the number).
- **View counters for blog articles** on each article (under the title, also shown at 0) and on every article card in the blog lists.
- Theme: footer extension point `abc_footer_stats`.

## Share & Statistics 1.0.1 – 2026-10-18
- Share buttons now show the **logos** of Facebook, X, LinkedIn, WhatsApp and Telegram (plus e-mail, copy-link and phone-share icons) as round icon buttons that fill with the brand colour on hover. Name kept as accessible label and tooltip. Brand glyphs from Simple Icons (CC0).

## 1.0.44 – 2026-10-18 (new plugin: Share & Statistics 1.0.0)
- **Share buttons on blog articles**: Facebook, X, LinkedIn, WhatsApp, Telegram, e-mail, copy link and the phone's native share menu. Plain links – nothing is loaded from the networks until a visitor clicks. Choose the networks under *Statistics → Sharing & tracking*; `[abi_share]` places the block anywhere. EN/FR/AR.
- **Statistics for articles and the whole site** (new dashboard menu *Statistics*): views and visitors (today / 7 / 30 / 90 days / 1 year / all time), views-per-day chart, top pages and articles, referring websites, share clicks per network and most-shared articles; *Views* and *Shares* columns in the article lists; optional public view counter under the article title. First-party only: no cookies, no IP addresses stored, bots / logged-in editors / Do-Not-Track skipped, reloads within 30 minutes counted once, works behind page caches (JS beacon).
- Theme: two extension points on the article template (`abc_note_meta`, `abc_after_note_content`).

## 1.0.43 – 2026-10-14 (core 1.0.28)
- **Arabic font choice.** New bundled fonts (no external requests): Tajawal (new default for Arabic pages), Cairo, IBM Plex Sans Arabic, plus the existing Almarai. Choose under Researcher → Appearance → Arabic font, with a preview link per font (administrators can also use `?abc_font=cairo`). All fonts are SIL OFL (licences in `assets/fonts`).

## 1.0.42 – 2026-10-14 (core 1.0.27, Student Attendance 1.1.1) – security review
- Code audit of theme and plugins (see `documentation/SECURITY-AUDIT.md`): no critical vulnerability found. Hardening added: safe response headers, no WordPress version leak, blocked user enumeration (`?author=` and REST users) for anonymous visitors, `abc_client_ip` filter for the contact rate-limit behind proxies/CDN, referrer policy on the Facebook embed, uninstall clean-up for attendance data.

## Student Attendance 1.1.0 – 2026-10-12
- New **phone page** `/attendance/` (created automatically, `[attendance_scanner]` shortcode, noindex): the teacher logs in once on the phone and gets every course with its *Take attendance* button, no need to find the Teaching page. Add it to the phone's home screen. The dashboard Attendance screen shows its link.

## 1.0.41 – 2026-10-12 (core 1.0.26, new plugin Student Attendance 1.0.0)
- The attendance feature moved out of the core plugin into its own plugin, **Ahcene Babori – Student Attendance** (`ahcene-babori-attendance-plugin.zip`). It adds the *Take attendance* button on every course (Teaching page and course page) through two extension points (`abc_card_extra` filter, `abc_teaching_single_actions` action) added to the core plugin and theme. Records already taken keep working. Own admin menu **Attendance**, own REST namespace `aba/v1`, UI strings in EN/FR/AR.

## 1.0.40 – 2026-10-12 (plugin 1.0.25)
- **Student attendance by card scan.** When you are logged in, every course on the Teaching page (and each course page) shows a *Take attendance* button. It opens a scanner that uses the phone camera to read the QR code / barcode on the student card, records the student for that course and session date, beeps/vibrates, and flags duplicates. A manual-entry field is the fallback.
- New **Researcher → Attendance** screen: filter by course and date, delete records, export CSV (spreadsheet-safe), optional private roster (`card code,Full name`) to show names.
- Privacy/security: records are private posts, never public; the REST endpoint (`abc/v1/attendance`) requires a logged-in administrator and a nonce; no external services (QR decoding uses the bundled jsQR 1.4.0, Apache-2.0, plus the browser's built-in BarcodeDetector when available).
- Needs HTTPS for camera access. Reading 1-D barcodes needs a browser with BarcodeDetector (Chrome/Android); QR works everywhere.

## 1.0.39 – 2026-10-12
- Hero video: same side-panel design, but wider and dissolved into the green block with a long, soft gradient so no hard seam is visible between the video and the block.

## 1.0.38 – 2026-10-12
- Hero video: reverted the capsule window (1.0.37); the layout is back to the 1.0.36 design (full-height side panel, caption visible).

## 1.0.37 – 2026-10-12
- Hero video integrated into the green block's design language: capsule-shaped window that bleeds off the page edge, a gold contour and dotted accent echoing the old portrait frame, a soft green tint on the seam, and the text column kept clear of it (mirrored in RTL).

## 1.0.36 – 2026-10-12
- Hero video: the frame is now cropped from the left, so the name/caption shown inside the video (lower right of the frame) is no longer cut off, and the edge fade is much narrower.

## 1.0.35 – 2026-10-12
- Hero: the text now always sits above the video's fade zone (it was partly covered by the video), the video panel is slightly narrower and the text column stays clear of it.

## 1.0.34 – 2026-10-12 (plugin 1.0.24)
- Hero: the keyword pills (Digital libraries, Open science…) were removed from the green block, and the "Loads from Facebook…" caption over the video was removed.

## 1.0.33 – 2026-10-12 (plugin 1.0.23)
- Hero video **autoplays when the homepage opens** (muted, as browsers require; visitors unmute with the player controls). New Researcher → Profile checkbox "Autoplay" (on by default). Skipped for visitors who prefer reduced motion; uploaded files loop back to the start second.

## 1.0.32 – 2026-10-12
- Hero video: back to the full-height side panel that dissolves into the green block (right in LTR, left in RTL/Arabic). The video now covers the whole panel (cropped to fit), so nothing is left empty below it.

## 1.0.31 – 2026-10-12
- Hero video: replaced the full-height panel with a **landscape (16:9) video card** inside the green block, so the Facebook/uploaded video fills its frame with no empty dark area underneath.

## 1.0.30 – 2026-10-12
- Hero video is now integrated into the green block: it fills the full height of the right-hand side and dissolves into the green background (mirrored in RTL), with a pulsing play button. On mobile it becomes a rounded banner above the text.

## 1.0.29 – 2026-10-12 (plugin 1.0.22)
- Homepage hero: the personal photo is replaced by a **video**. New settings in Researcher → Profile: uploaded video file (best, respects the start second, no third-party requests), or a Facebook/YouTube link (pre-filled with the requested reel), plus a **start second** (34). Embeds load only after the visitor clicks play. Clear both fields to return to the portrait.
- Note: Facebook may ignore the start second and can refuse embedding for some reels; use an uploaded MP4 if so.

## 1.0.28 – 2026-10-12
- Blog: the homepage block now shows **4** latest posts, and the Blog page (and its category/tag pages) lists **4 posts per page** (was 3 and 9).

## 1.0.27 – 2026-10-12
- Homepage: the **Research impact** (statistics) block and the **Collaborate with me** call-to-action were removed. The statistics settings stay available and can still be shown anywhere with the `[abc_stats]` shortcode.

## 1.0.26 – 2026-10-12
- Homepage research-area tiles: icons removed – the area image and title carry the tile; the image area is slightly taller.

## 1.0.25 – 2026-10-12
- **Homepage research areas as colour tiles** (same family as the quick-access tiles): eight tiles in one row (4 on tablets, 2 on phones) in alternating scheme colours, icon in a glass circle, the area title at the bottom and the area image fading from the top of the tile into its colour. On hover/keyboard focus a tile shows the description and the publication/project counters. The Research page keeps the detailed cards.

## 1.0.24 – 2026-10-12
- Research-areas row: a small side margin (0.5 rem) so the first and last cards no longer touch the screen edges.

## 1.0.23 – 2026-10-12
- Research-areas row: no side margins any more – the cards run from the very left edge to the very right edge of the screen (the heading stays aligned with the page content).

## 1.0.22 – 2026-10-12
- **Research-areas row now spans the full page width** (edge to edge): the first card stays aligned with the page content, the cards continue to the screen edge, arrows sit at the screen edges; no horizontal page scroll at any width.

## 1.0.21 – 2026-10-12
- **Research areas in a single scrollable row** on the homepage (about 3½ cards visible, scroll-snap, round arrow buttons on desktop, swipe on phones; works in RTL). The Research page keeps the full grid.

## 1.0.20 – 2026-10-12
- **Research-area cards: coloured title ribbon + prominent image.** The area name now sits in a gold ribbon at the top (dark navy text, icon tile and outlined number), followed by a larger image with a light colour-scheme tint, then the description and counters. Same structure with or without an image; the ribbon has a fixed height so images line up across the row.

## 1.0.19 – 2026-10-12
- Research-area images now use the **top-image layout**: a short image band with a unified colour-scheme tint (greyscale + gradient), icon and number over it, text below on white. The card still turns into the deep gradient on hover.

## 1.0.18 – 2026-10-12
- **Optional image per research area** (*Research Areas → edit → Image*, Media Library picker): shown as a full card background with a dark colour-scheme gradient (text always readable), a glass icon tile and a slow zoom on hover; the area page header gets the same image as a subtle tinted backdrop. Areas without an image keep the numbered icon card. Images are lazy-loaded and decorative (empty alt).

## 1.0.17 – 2026-10-12
- **Research areas redesigned** (“knowledge network” cards): large outlined numbers 01–08, gradient icon tile, pill-shaped counters for related publications/projects, a gold-green progress edge, and a hover/focus state that turns the card into a deep green gradient with a gold icon (motion disabled for reduced-motion visitors). Follows the chosen colour scheme.

## 1.0.16 – 2026-10-12
- **Simple footer**: name and title (from Researcher → Profile), contact e-mail, and the social/academic profile icons only; slim bottom bar with the copyright and a round “Back to top” button.

## 1.0.15 – 2026-10-11
- Homepage: the biography strip and the research-areas block now use tints of the chosen colour scheme (soft green / pale green with white cards) instead of white; bands alternate tinted → dark → white → tinted down the page.

## 1.0.14 – 2026-10-11
- **Biography strip on the homepage uses the full width** (the text no longer stops halfway, leaving the other side empty).
- **Same colours on every page**: all inner pages (About, Publications, Projects, single items, Blog, Contact, CV, search, 404…) now open with the same green gradient header as the homepage hero, with light breadcrumbs and titles.

## 1.0.13 – 2026-10-11
- **Emerald & Gold is now the default colour scheme** (chosen by the owner). Other schemes remain available in *Researcher → Appearance*.

## 1.0.12 – 2026-10-11
- **Quick-access tiles**: eight large coloured tiles with icons (Publications, Books, Projects, Presentations, Teaching, Academic Service, Blog, Contact) overlapping the bottom of the hero; they follow the chosen colour scheme. The old three-column teaching/service/presentations row was removed (the tiles replace it).
- **Mega menu**: Publications, Projects, Presentations, Teaching, Academic Service and Blog open a wide panel with their categories/types/research areas and latest items (automatic, cached 10 min); hover, keyboard focus, touch toggle button and Escape supported; accordion on mobile.
- **Alternating section bands** on the homepage with a coloured stripe before each title and a gradient edge on tinted bands.

## 1.0.11 – 2026-10-11
- **Appearance settings** (*Researcher → Appearance*): six colour schemes (Teal Navy – default, Indigo & Amber, Emerald & Gold, Burgundy & Gold, Ocean Blue & Sky, Slate & Coral) and three main-menu styles (Light, Dark, Accent). Each scheme meets WCAG AA contrast; administrators can preview any choice on the live site before saving. Default look is unchanged.

## 1.0.10 – 2026-10-11
- **“About me” / “Contact” / “Download CV” buttons can no longer silently open the homepage**: new **page links** in *Researcher → Profile & Photo* let you choose the About, Contact, Online CV and Research pages (pick the page of one language; the others are found through Polylang translations). Automatic detection also tries the page title (About / À propos / نبذة عني…) when slugs differ.
- Footer: the research-interests paragraph (statement under the name) was removed.

## 1.0.9 – 2026-10-11
- **Compact homepage** (about one third shorter): tighter sections, smaller cards and research-area tiles, one-line teaching/service/presentations row, slimmer statistics and call-to-action. Base font 16 px.
- **“Selected publications” block removed** from the homepage (publications stay in their own section).
- **Justified text** (inter-word justification, start-aligned headings) for body text, introductions, biography and abstracts; narrow cards keep normal alignment to avoid wide gaps.

## 1.0.8 – 2026-10-11
- **Fix: *About* link in every language.** Menu links to pages are now resolved through the page’s Polylang translation first, and a link is never replaced by the homepage; the “About me” and “Contact” buttons also find hand-made translations (the page key is now copied when a translation is created, and pages are also found by their usual slug or shortcode).
- **Fix: *Back to top* in the footer.** The target is no longer the sticky header (browsers cannot scroll to an element that is already stuck at the top).

## 1.0.7 – 2026-10-11
- **Resources removed** (menu, dashboard, search, templates, sample data). Previously saved resource data stays untouched in the database.
- **Research Notes became the Blog** (menu, URLs `/blog/`, labels, categories and tags; old `/research-notes/…` links redirect). Existing posts are kept.
- **CV removed from the primary menu** (the *Download CV* button and the CV page remain; CV stays in the footer menu).
- **Contact messages are dashboard entries**: the form now asks for First name, Last name, Email, Reason for contact and Message. Every message is saved under the new top-level **Contact Messages** menu (sender, e-mail, reason, message, status New / Read / Replied / Archived, red counter for new ones, “Reply by e-mail” button) and also e-mailed.
- The *Contact* button on the homepage (and in menus/footer) always works: it opens the Contact page, or a `mailto:` link when no Contact page exists.

## 1.0.6 – 2026-10-11
- **About timeline is now editable from the dashboard** (*About Timeline* menu): add, edit, reorder and translate entries (start year for sorting, period text, badge, institution, details) grouped in **Timeline Sections** (Education & Academic Qualifications, Academic Positions, Professional Experience, Awards & Honours, Certificates – add your own). The About page and the CV page display them via the `[abc_career]` shortcode; entries are sorted newest first and empty sections are hidden.
- Existing About pages are migrated automatically (current entries become editable posts; your own text in the page is kept).

## 1.0.5 – 2026-10-11
- Hero portrait: orbit design – slowly rotating dashed rings and four floating topic badges (library, open access, AI, repository) around the arch portrait; motion disabled for visitors who prefer reduced motion.

## 1.0.4 – 2026-10-11
- Homepage hero redesigned to be about half as tall: arch-shaped portrait with offset ring and dotted accent, smaller name/title/tagline, tighter spacing, slimmer topics bar.

## 1.0.3 – 2026-10-11
- **Almarai (Regular 400, Bold 700) is now the primary typeface of the Arabic interface**, bundled in the theme (SIL OFL); Noto Sans Arabic remains as fallback.

## 1.0.2 – 2026-10-11
- **About page is now a chronological timeline** (Education & Academic Qualifications, Academic Positions), in EN / FR / AR, using the owner’s CV details; same navy/teal design. Pages still holding the untouched sample text are updated automatically when the plugin is updated; edited pages are never overwritten.

## 1.0.1 – 2026-10-11
- Fix: menu links to starter pages (e.g. *About / نبذة عني*) are now resolved at run time to the page of the current language, so they cannot point to a wrong-language or missing page.
- “Research” removed from the starter primary menus (installer, `demo-content.xml`, fallback menu); the plugin removes it from existing *Primary menu (EN/FR/AR)* menus once. The Research page itself is kept.
- **Presentation Type is now a taxonomy** (*Presentations → Presentation Types*): add, rename and translate types freely. Default types: Keynote, Invited talk, Conference paper, Poster, Workshop, Webinar, Seminar. Old values are migrated automatically. Types appear as filter chips on the Presentations archive.

## 1.0.0 – 2026-10-08
First release.

### Plugin `ahcene-babori-core`
- Custom post types: Publications, Books, Research Projects, Presentations, Academic Service, Teaching, Research Resources, Research Notes (+ private Contact messages)
- Taxonomies: Publication Type, Research Area (icon + order), Year (auto-synced), Keywords, Service Category, Teaching Section, Resource Type, Note Category, Note Tag
- Admin meta boxes for all fields (media pickers, relation checklists), REST-exposed meta, admin list columns
- “Researcher” dashboard: Overview with quick actions and checklist, Profile & Photo, Identifiers & Links, Statistics, CV files, Contact form, Sample content, Import (beta)
- Trilingual interface strings (EN/FR/AR), Polylang integration (post types, taxonomies, meta copying, language switcher data)
- Shortcodes: `[abc_stats] [abc_research_areas] [abc_profiles] [abc_cv_button] [abc_publications] [abc_list] [abc_contact_card] [abc_contact_form] [abc_search_form] [abc_link]`
- SEO: description, Open Graph, Twitter, canonical, JSON-LD (Person, WebSite, ScholarlyArticle, Book, ResearchProject, Article, BreadcrumbList), Google Scholar tags; automatic hand-off to Yoast/Rank Math/AIOSEO
- Search over title/content plus authors, journal, abstract, DOI, keywords; publication filters
- Contact form (nonce, honeypot, signed time token, rate limit, validation)
- REST: `/wp-json/abc/v1/profile`, `/wp-json/abc/v1/publications`, plus standard `/wp/v2/` endpoints
- Import framework: Crossref by DOI (pending review), planned ORCID / OpenAlex / OpenAIRE / OAI-PMH / DSpace
- Idempotent sample-content installer (EN/FR/AR, translation-linked) and removal of sample entries

### Theme `ahcene-babori-academic`
- Responsive, accessible academic design (navy / charcoal / white / off-white / teal), full RTL
- Templates: front page, publications (filters), single publication/book/project/presentation/resource/note, service timeline, teaching portfolio, research-area hub, search, CV, 404
- Bundled Inter and Noto Sans Arabic (no external requests), WebP sub-sizes, print CV
