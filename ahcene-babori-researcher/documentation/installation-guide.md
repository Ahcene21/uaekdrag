# Installation guide – ahcenebabori.com

Step-by-step set-up of the website of **Dr. Ahcene Babori** on any normal WordPress hosting
(PHP 7.4+, WordPress 6.0+, MySQL/MariaDB). No development tools are needed.

> The package contains **no passwords, API keys or database credentials**. Everything is configured from the WordPress dashboard.

## 0. What you have

| File | Purpose |
|---|---|
| `ahcene-babori-academic-theme.zip` | The **theme** – design, layout, typography, RTL, templates |
| `ahcene-babori-core-plugin.zip` | The **plugin** – all academic data (publications, books, projects, …), settings, SEO/schema, contact form |
| `demo-content.xml` | Optional WordPress (WXR) import of starter pages, menus and sample entries |
| `language-setup.md` | Polylang EN / FR / AR configuration |

Academic data lives in the plugin: **if you change the theme later, nothing disappears.**

## 1. Install WordPress
Install WordPress 6.0 or newer on `https://ahcenebabori.com` (one-click installer of your host, or the official zip from wordpress.org). Use HTTPS. In **Settings → Permalinks** choose **Post name**.

## 2. Install the theme
*Appearance → Themes → Add New → Upload Theme* → choose `ahcene-babori-academic-theme.zip` → **Install** → **Activate**.
(Until step 3 the site shows a short “setup in progress” notice – this is normal.)

## 3. Install the core plugin
*Plugins → Add New → Upload Plugin* → `ahcene-babori-core-plugin.zip` → **Install** → **Activate**.
A new **Researcher** menu appears in the dashboard, plus the content types: Publications, Books, Research Projects, Presentations, Academic Service, Teaching, Blog, About Timeline and Contact Messages.

## 4. Install Polylang
*Plugins → Add New* → search **Polylang** → Install → Activate. (The free version is enough.)

## 5. Configure English, French and Arabic
Follow [`language-setup.md`](language-setup.md) (5 minutes). Short version: *Languages → Languages* → add **English (en)**, **Français (fr)**, **العربية (ar)**; choose URL modifications “language set from the directory name” so you get `/en/`, `/fr/`, `/ar/`.

## 6. Import the starter content (choose ONE method)

**A. Built-in installer (recommended – links EN/FR/AR translations automatically)**
*Researcher → Sample content* → click **1. Install structure** then **2. Install sample entries**.

**B. WordPress importer**
*Tools → Import → WordPress* (install the importer when asked) → upload `demo-content.xml` → *Assign posts to an existing user* → Submit. Afterwards link translations in the Polylang columns of *Pages* and each content list, and assign menus (step 7).

Both methods only **add** content; nothing existing is overwritten or deleted.
Every sample entry shows a yellow **Demo** label and the marker `[ADD VERIFIED INFORMATION]`.

## 7. Menus
*Appearance → Menus*. Method A creates *Primary menu (EN/FR/AR)* and *Footer menu (EN/FR/AR)* and assigns them per language. Check *Languages → Settings → Menus* (Polylang) that each language has its own menu in the **Primary menu** and **Footer menu** locations. The nine entries are: Home, About, Publications, Projects, Teaching, Academic Service, Presentations, Blog, Contact.

## 8. Upload the profile photo
*Researcher → Profile & Photo* → **Choose / upload** → portrait (recommended 800 × 1000 px or larger) → add a short alt text → Save. Replace it any time from the Media Library.
On the same screen, **Page links** let you choose which page the *About me*, *Contact*, *Online CV* and *Research* buttons open – use it if you created your own pages or a button opens the homepage.

**Colours:** *Researcher → Appearance* lets you choose the colour scheme and the main-menu style (with a live preview link).

## 9. Upload the CV
*Researcher → CV files* → upload one PDF per language (EN / FR / AR). The *Download CV* buttons use the visitor’s language. To replace a CV, choose a new file.
Fill in the online CV text in *Pages → Curriculum Vitae* (one per language).

## 10. Research identifiers
*Researcher → Identifiers & Links*: ORCID iD, Google Scholar, Scopus, ResearchGate, LinkedIn, institutional profile, public e-mail. Only filled fields appear on the website. **Do not enter anything you have not verified.**

## 11. Add publications
*Publications → Add publication*: title, authors (one per line), year, journal, DOI (only the DOI), abstract, PDF (only if legally shareable), Open Access status, repository URL; tick **Publication Type**, **Research Areas**, **Keywords**; tick “Show on the homepage” for featured items. The *Year* filter is generated automatically. The *About* page shows your career timeline: manage it under **About Timeline** (entries) and **About Timeline → Timeline Sections** (Education, Positions, Experience, Awards, Certificates…); the introduction text stays in *Pages → About*. Presentation types (Keynote, Invited talk, …) are managed under *Presentations → Presentation Types*. Use the same pattern for books, projects, presentations, academic service, courses and blog posts (*Researcher → Overview* has a button for each).
To add an entry in another language: use the “+” of that language in the Polylang column.

## 12. SEO
The theme/plugin already output: meta description, canonical URLs, Open Graph, Twitter cards, schema.org (**Person, WebSite, ScholarlyArticle, Book, ResearchProject, BreadcrumbList**) and Google Scholar tags (`citation_title`, `citation_author`, `citation_publication_date`, `citation_pdf_url`, `citation_doi`, …).
- **Without an SEO plugin**: nothing to do; the built-in sitemap is at `/wp-sitemap.xml`.
- **With Yoast SEO or Rank Math**: install it normally. The core plugin detects it and stops printing duplicate description/Open Graph/canonical/WebSite/Breadcrumb tags; it still adds the academic schema (ScholarlyArticle, Book, Person) and the citation tags. Set the site name, social profiles and sitemap in that plugin; add custom meta titles/descriptions per page.
- Submit `https://ahcenebabori.com/sitemap_index.xml` (Yoast/Rank Math) or `/wp-sitemap.xml` to Google Search Console. In *Settings → Reading* make sure “Discourage search engines” is **unticked** at launch.

## 13. Contact form
The page *Contact* contains `[abc_contact_form]`. Mail goes to the public e-mail address unless you set another one in *Researcher → Contact form*. The form asks for first name, last name, e-mail, reason for contact and message. Spam protection: nonce, hidden honeypot, signed minimum-time token, rate limit (5/hour/visitor), link limit – no CAPTCHA service, no cookies.
For reliable delivery install an SMTP plugin (e.g. “WP Mail SMTP”) and send a test message. Every message is also saved under the top-level **Contact Messages** menu (first name, last name, e-mail, reason for contact, message, status) with a red counter for new ones.
If you use a page-cache plugin, **exclude the Contact page** from caching (the form contains a time-limited security token).

## 14. Replace demo content with verified information
1. *Researcher → Overview* shows a checklist (Polylang, theme, photo, CV, ORCID, sample content).
2. Edit each sample entry: replace every `[ADD VERIFIED INFORMATION]`, then untick **This entry is sample content** (side box) – the *Demo* label disappears.
3. Edit the pages *About* and *Curriculum Vitae* (all three languages): education, positions, experience, awards, certificates, languages, technical skills.
4. Check *Researcher → Statistics*: the five default values (e.g. “10+ years of professional experience”) were suggested by the owner; **confirm or change them**.
5. Delete the sample entries you do not need, or use *Researcher → Sample content → Remove sample entries* (removes only entries still marked as sample).
6. Review Arabic wording of the owner’s name (the demo keeps the Latin spelling “Ahcene Babori”) and any sentence you want to adjust.

## Image optimisation / WebP
New JPEG uploads get WebP sub-sizes automatically when your server supports it (WordPress 6.1+ with GD/Imagick WebP). Images are lazy-loaded by WordPress; the homepage portrait is loaded with high priority.

## Privacy
The site loads **no external fonts, scripts or trackers** (Inter and Noto Sans Arabic are bundled, SIL Open Font Licence). Contact messages stored in the dashboard contain name, e-mail and message – delete them periodically (*Contact Messages*) and mention the form in your privacy notice.

## Updating / moving
Back up with any WordPress backup tool. Content is in the database, files in `wp-content/uploads`. Switching themes keeps all academic content (the plugin owns it).

## Troubleshooting
| Symptom | Fix |
|---|---|
| Archive pages (Publications…) show 404 | *Settings → Permalinks → Save* once |
| “Setup in progress” on the front end | Activate the plugin *Ahcene Babori Core* |
| Menu shows only English | Assign each language’s menu in *Polylang → Settings → Menus* |
| Arabic page is left-to-right | Language “العربية” must have **Text direction: RTL** in *Languages → Languages* |
| No CV button file | Upload the PDF in *Researcher → CV files* (otherwise the button opens the online CV) |

## Student attendance (card scan) – separate plugin

Install and activate `ahcene-babori-attendance-plugin.zip` (needs the core plugin for the Teaching courses).

0. On your phone open `yoursite.com/attendance/`, log in once, then *Add to Home screen* — every course is listed with its scan button. (The button never appears for visitors or when you are not logged in.)
1. Log in, open the **Teaching** page and press **Take attendance** on the course.
2. Allow camera access (the site must use HTTPS), choose the session date and point the phone at the QR code/barcode on the student card. Each new card is recorded with a short beep; scanning it again says "Already recorded".
3. Review, delete or export (CSV) under the **Attendance** menu in the dashboard. Optionally paste a roster (`card code,Full name`) there to see names.
Records are stored privately on your site and are never shown to visitors. Collect student data only as permitted by your institution's rules and delete it when no longer needed.

## Share buttons and statistics

Install `ahcene-babori-insights-plugin.zip` and activate it. Blog articles then show share buttons and a view counter; **Statistics** in the dashboard menu shows site and article statistics (**Sharing & tracking** chooses networks and switches tracking/counter on or off). No cookies and no IP addresses are stored. If your privacy policy lists analytics, mention first-party anonymous statistics there.

## Publication covers (first page of the PDF)

1. Upload each article's PDF in the publication (field *PDF file*) – only PDFs you may legally share.
2. Open **Researcher → Publication covers** and press **Generate all missing covers** (or use *Generate cover from the PDF* inside a publication, then Update).
3. The publications page shows each article as a card with its first page. You can replace any cover with your own image in the *Cover image* field.
