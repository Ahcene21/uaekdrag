<?php
/**
 * Demo / starter content definition (EN, FR, AR).
 *
 * Single source of truth for:
 *  - the in-dashboard installer (Researcher → Sample content), and
 *  - the WXR file generator (tools/build-wxr.php → demo-content.xml).
 *
 * NO invented facts: no DOIs, ISBNs, citation counts, awards, positions, ORCID/Scopus IDs or
 * grants. Anything unknown is the literal marker [ADD VERIFIED INFORMATION].
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

$L = static function ( $en, $fr, $ar ) {
	return array( 'en' => $en, 'fr' => $fr, 'ar' => $ar );
};
$P = '[ADD VERIFIED INFORMATION]';

/* ------------------------------------------------------------ taxonomies */
$tax = array();

$tax['abc_pub_type'] = array(
	'journal-article'  => array( 'name' => $L( 'Journal Article', 'Article de revue', 'مقال في مجلة علمية' ) ),
	'conference-paper' => array( 'name' => $L( 'Conference Paper', 'Communication de conférence', 'ورقة مؤتمر' ) ),
	'book-chapter'     => array( 'name' => $L( 'Book Chapter', 'Chapitre d’ouvrage', 'فصل في كتاب' ) ),
	'book'             => array( 'name' => $L( 'Book', 'Ouvrage', 'كتاب' ) ),
	'thesis'           => array( 'name' => $L( 'Thesis', 'Thèse', 'أطروحة' ) ),
	'report'           => array( 'name' => $L( 'Report', 'Rapport', 'تقرير' ) ),
	'preprint'         => array( 'name' => $L( 'Preprint', 'Prépublication', 'نسخة أولية' ) ),
	'other'            => array( 'name' => $L( 'Other', 'Autre', 'أخرى' ) ),
);

$tax['abc_research_area'] = array(
	'digital-libraries' => array(
		'icon' => 'library', 'order' => 10,
		'name' => $L( 'Digital Libraries', 'Bibliothèques numériques', 'المكتبات الرقمية' ),
		'slug' => $L( 'digital-libraries', 'bibliotheques-numeriques', 'al-maktabat-al-raqmiya' ),
		'desc' => $L(
			'Design, development and evaluation of digital collections, services and platforms that preserve and give access to scholarly and cultural heritage resources.',
			'Conception, développement et évaluation de collections, de services et de plateformes numériques qui préservent et donnent accès aux ressources scientifiques et patrimoniales.',
			'تصميم المجموعات والخدمات والمنصات الرقمية وتطويرها وتقييمها، بما يحفظ المصادر العلمية والتراثية ويتيح الوصول إليها.'
		),
	),
	'institutional-repositories' => array(
		'icon' => 'repository', 'order' => 20,
		'name' => $L( 'Institutional Digital Repositories', 'Dépôts numériques institutionnels', 'المستودعات الرقمية المؤسسية' ),
		'slug' => $L( 'institutional-repositories', 'depots-institutionnels', 'al-mustawdaat-al-muassasiya' ),
		'desc' => $L(
			'Building, managing and sustaining repositories that collect, preserve and disseminate the research output of institutions.',
			'Mise en place, gestion et pérennisation de dépôts qui collectent, conservent et diffusent la production scientifique des établissements.',
			'بناء المستودعات التي تجمع الإنتاج البحثي للمؤسسات وتحفظه وتنشره، وإدارتها واستدامتها.'
		),
	),
	'open-access-open-science' => array(
		'icon' => 'openaccess', 'order' => 30,
		'name' => $L( 'Open Access & Open Science', 'Accès ouvert et science ouverte', 'الوصول الحر والعلم المفتوح' ),
		'slug' => $L( 'open-access-open-science', 'acces-ouvert-science-ouverte', 'al-wusul-al-hurr' ),
		'desc' => $L(
			'Policies, infrastructures and practices that make research outputs, data and methods openly available and reusable.',
			'Politiques, infrastructures et pratiques qui rendent les résultats, les données et les méthodes de la recherche librement accessibles et réutilisables.',
			'السياسات والبنى التحتية والممارسات التي تجعل نتائج البحث وبياناته ومناهجه متاحة بحرية وقابلة لإعادة الاستخدام.'
		),
	),
	'scholarly-communication' => array(
		'icon' => 'communication', 'order' => 40,
		'name' => $L( 'Scholarly Communication', 'Communication scientifique', 'الاتصال العلمي' ),
		'slug' => $L( 'scholarly-communication', 'communication-scientifique', 'al-ittisal-al-ilmi' ),
		'desc' => $L(
			'How research is created, evaluated, disseminated and preserved, including publishing models, peer review and research assessment.',
			'La manière dont la recherche est produite, évaluée, diffusée et conservée : modèles d’édition, évaluation par les pairs et évaluation de la recherche.',
			'كيفية إنتاج البحث وتقييمه ونشره وحفظه، بما في ذلك نماذج النشر والتحكيم العلمي وتقييم البحث.'
		),
	),
	'metadata-knowledge-organization' => array(
		'icon' => 'metadata', 'order' => 50,
		'name' => $L( 'Metadata & Knowledge Organization', 'Métadonnées et organisation des connaissances', 'البيانات الوصفية وتنظيم المعرفة' ),
		'slug' => $L( 'metadata-knowledge-organization', 'metadonnees-organisation-connaissances', 'al-bayanat-al-wasfiya' ),
		'desc' => $L(
			'Metadata schemas, controlled vocabularies, authority control and subject indexing that make resources findable and interoperable.',
			'Schémas de métadonnées, vocabulaires contrôlés, contrôle d’autorité et indexation matière qui rendent les ressources repérables et interopérables.',
			'مخططات البيانات الوصفية والمفردات المضبوطة والضبط الاستنادي والفهرسة الموضوعية التي تجعل المصادر قابلة للاكتشاف والتشغيل البيني.'
		),
	),
	'ai-in-libraries' => array(
		'icon' => 'ai', 'order' => 60,
		'name' => $L( 'Artificial Intelligence in Libraries', 'Intelligence artificielle en bibliothèque', 'الذكاء الاصطناعي في المكتبات' ),
		'slug' => $L( 'ai-in-libraries', 'ia-en-bibliotheque', 'al-dhaka-al-istinai' ),
		'desc' => $L(
			'Responsible use of machine learning and language technologies for discovery, classification, metadata enrichment and library services.',
			'Usage responsable de l’apprentissage automatique et des technologies du langage pour le repérage, le classement, l’enrichissement des métadonnées et les services de bibliothèque.',
			'الاستخدام المسؤول للتعلم الآلي وتقنيات اللغة في الاكتشاف والتصنيف وإثراء البيانات الوصفية وخدمات المكتبات.'
		),
	),
	'research-data-management' => array(
		'icon' => 'data', 'order' => 70,
		'name' => $L( 'Research Data Management', 'Gestion des données de la recherche', 'إدارة بيانات البحث' ),
		'slug' => $L( 'research-data-management', 'gestion-donnees-recherche', 'idarat-bayanat-al-bahth' ),
		'desc' => $L(
			'Planning, documenting, sharing and preserving research data according to the FAIR principles.',
			'Planification, documentation, partage et conservation des données de la recherche selon les principes FAIR.',
			'تخطيط بيانات البحث وتوثيقها ومشاركتها وحفظها وفق مبادئ FAIR.'
		),
	),
	'digital-transformation' => array(
		'icon' => 'transform', 'order' => 80,
		'name' => $L( 'Digital Transformation', 'Transformation numérique', 'التحول الرقمي' ),
		'slug' => $L( 'digital-transformation', 'transformation-numerique', 'al-tahawwul-al-raqmi' ),
		'desc' => $L(
			'The evolution of library and information services, skills and organisations in a digital research environment.',
			'L’évolution des services d’information et de bibliothèque, des compétences et des organisations dans un environnement de recherche numérique.',
			'تطور خدمات المكتبات والمعلومات والمهارات والمؤسسات في بيئة بحثية رقمية.'
		),
	),
);

$tax['abc_service_type'] = array(
	'peer-reviewer'        => array( 'name' => $L( 'Peer Reviewer', 'Évaluateur (relecteur)', 'محكّم علمي' ) ),
	'editorial-board'      => array( 'name' => $L( 'Editorial Board', 'Comité éditorial', 'هيئة التحرير' ) ),
	'associate-editor'     => array( 'name' => $L( 'Associate Editor', 'Rédacteur associé', 'محرر مشارك' ) ),
	'conference-reviewer'  => array( 'name' => $L( 'Conference Reviewer', 'Évaluateur de conférences', 'محكّم مؤتمرات' ) ),
	'academic-committee'   => array( 'name' => $L( 'Academic Committee', 'Comité académique', 'لجنة أكاديمية' ) ),
	'professional-volunteer' => array( 'name' => $L( 'Professional Volunteer', 'Bénévolat professionnel', 'عمل تطوعي مهني' ) ),
	'open-science-service' => array( 'name' => $L( 'Open Science Service', 'Service en science ouverte', 'خدمة في مجال العلم المفتوح' ) ),
);

$tax['abc_teaching_section'] = array(
	'courses'             => array( 'name' => $L( 'Courses', 'Enseignements', 'المقررات الدراسية' ) ),
	'teaching-philosophy' => array( 'name' => $L( 'Teaching Philosophy', 'Philosophie pédagogique', 'فلسفة التدريس' ) ),
	'student-supervision' => array( 'name' => $L( 'Student Supervision', 'Encadrement des étudiants', 'الإشراف على الطلبة' ) ),
	'teaching-materials'  => array( 'name' => $L( 'Teaching Materials', 'Supports pédagogiques', 'المواد التعليمية' ) ),
	'research-supervision' => array( 'name' => $L( 'Research Supervision', 'Encadrement de recherche', 'الإشراف البحثي' ) ),
);

$tax['abc_resource_type'] = array(
	'guides'             => array( 'name' => $L( 'Guides', 'Guides pratiques', 'أدلة' ) ),
	'tutorials'          => array( 'name' => $L( 'Tutorials', 'Tutoriels', 'دروس تطبيقية' ) ),
	'datasets'           => array( 'name' => $L( 'Datasets', 'Jeux de données', 'مجموعات بيانات' ) ),
	'presentations'      => array( 'name' => $L( 'Presentations', 'Présentations', 'عروض تقديمية' ) ),
	'templates'          => array( 'name' => $L( 'Templates', 'Modèles', 'نماذج وقوالب' ) ),
	'research-tools'     => array( 'name' => $L( 'Research Tools', 'Outils de recherche', 'أدوات بحثية' ) ),
	'teaching-materials' => array( 'name' => $L( 'Teaching Materials', 'Supports pédagogiques', 'مواد تعليمية' ) ),
	'reports'            => array( 'name' => $L( 'Reports', 'Rapports', 'تقارير' ) ),
);

$tax['abc_note_category'] = array(
	'digital-libraries'          => array( 'name' => $L( 'Digital Libraries', 'Bibliothèques numériques', 'المكتبات الرقمية' ) ),
	'institutional-repositories' => array( 'name' => $L( 'Institutional Repositories', 'Dépôts institutionnels', 'المستودعات المؤسسية' ) ),
	'open-access'                => array( 'name' => $L( 'Open Access', 'Accès ouvert', 'الوصول الحر' ) ),
	'ai-and-libraries'           => array( 'name' => $L( 'AI & Libraries', 'IA et bibliothèques', 'الذكاء الاصطناعي والمكتبات' ) ),
	'scholarly-communication'    => array( 'name' => $L( 'Scholarly Communication', 'Communication scientifique', 'الاتصال العلمي' ) ),
	'metadata'                   => array( 'name' => $L( 'Metadata', 'Métadonnées', 'البيانات الوصفية' ) ),
	'library-technology'         => array( 'name' => $L( 'Library Technology', 'Technologies des bibliothèques', 'تقنيات المكتبات' ) ),
	'research-methods'           => array( 'name' => $L( 'Research Methods', 'Méthodes de recherche', 'مناهج البحث' ) ),
);

/* ----------------------------------------------------------------- pages */
$pages = array();

$pages['home'] = array(
	'title'   => $L( 'Home', 'Accueil', 'الرئيسية' ),
	'slug'    => $L( 'home', 'accueil', 'al-ra2isiya' ),
	'content' => $L( '', '', '' ), // The homepage layout is built by the theme (front-page.php).
	'front'   => true,
);

$pages['about'] = array(
	'title'   => $L( 'About', 'À propos', 'نبذة عني' ),
	'slug'    => $L( 'about', 'a-propos', 'nubdha-anni' ),
	'content' => $L(
		'<h2>Profile</h2>
<p>Dr. Ahcene Babori is an Assistant Professor in Library and Information Science. His professional and research interests include digital libraries, institutional repositories, open access, open science, scholarly communication, metadata, library technologies, artificial intelligence in libraries and research data management.</p>
<p>He has professional experience in academic and cultural heritage library environments and experience developing and managing institutional digital repositories.</p>
<h2>Research interests</h2>
<ul><li>Digital libraries and institutional repositories</li><li>Open access and open science</li><li>Scholarly communication</li><li>Metadata and knowledge organization</li><li>Research data management</li><li>Artificial intelligence in libraries and library technologies</li></ul>
<h2>Education</h2>
<p>' . $P . '</p>
<h2>Current position and affiliation</h2>
<p>Assistant Professor, Library and Information Science. ' . $P . ' (institution, faculty, city and country).</p>
<h2>Collaboration</h2>
<p>For research collaboration, invited talks, peer review or supervision enquiries, please use the [abc_link page="contact" label="contact page"].</p>',
		'<h2>Profil</h2>
<p>Dr Ahcene Babori est professeur assistant en sciences de l’information et des bibliothèques. Ses intérêts professionnels et de recherche portent sur les bibliothèques numériques, les dépôts institutionnels, l’accès ouvert, la science ouverte, la communication scientifique, les métadonnées, les technologies des bibliothèques, l’intelligence artificielle en bibliothèque et la gestion des données de la recherche.</p>
<p>Il possède une expérience professionnelle dans des bibliothèques universitaires et patrimoniales, ainsi qu’une expérience dans le développement et la gestion de dépôts numériques institutionnels.</p>
<h2>Domaines de recherche</h2>
<ul><li>Bibliothèques numériques et dépôts institutionnels</li><li>Accès ouvert et science ouverte</li><li>Communication scientifique</li><li>Métadonnées et organisation des connaissances</li><li>Gestion des données de la recherche</li><li>Intelligence artificielle et technologies des bibliothèques</li></ul>
<h2>Formation</h2>
<p>' . $P . '</p>
<h2>Poste actuel et affiliation</h2>
<p>Professeur assistant en sciences de l’information et des bibliothèques. ' . $P . ' (établissement, faculté, ville et pays).</p>
<h2>Collaboration</h2>
<p>Pour toute collaboration de recherche, invitation, évaluation par les pairs ou demande d’encadrement, veuillez utiliser la [abc_link page="contact" label="page de contact"].</p>',
		'<h2>الملف التعريفي</h2>
<p>الدكتور Ahcene Babori أستاذ مساعد في علم المكتبات والمعلومات. تشمل اهتماماته المهنية والبحثية المكتبات الرقمية والمستودعات المؤسسية والوصول الحر والعلم المفتوح والاتصال العلمي والبيانات الوصفية وتقنيات المكتبات والذكاء الاصطناعي في المكتبات وإدارة بيانات البحث.</p>
<p>يتمتع بخبرة مهنية في بيئات المكتبات الأكاديمية ومكتبات التراث الثقافي، وبخبرة في تطوير المستودعات الرقمية المؤسسية وإدارتها.</p>
<h2>الاهتمامات البحثية</h2>
<ul><li>المكتبات الرقمية والمستودعات المؤسسية</li><li>الوصول الحر والعلم المفتوح</li><li>الاتصال العلمي</li><li>البيانات الوصفية وتنظيم المعرفة</li><li>إدارة بيانات البحث</li><li>الذكاء الاصطناعي وتقنيات المكتبات</li></ul>
<h2>التكوين العلمي</h2>
<p>' . $P . '</p>
<h2>المنصب الحالي والانتماء</h2>
<p>أستاذ مساعد في علم المكتبات والمعلومات. ' . $P . ' (المؤسسة والكلية والمدينة والبلد).</p>
<h2>التعاون</h2>
<p>للتعاون البحثي أو الدعوات العلمية أو التحكيم أو طلبات الإشراف، يُرجى استخدام [abc_link page="contact" label="صفحة التواصل"].</p>'
	),
);

$pages['research'] = array(
	'title'   => $L( 'Research', 'Recherche', 'البحث' ),
	'slug'    => $L( 'research', 'recherche', 'al-bahth' ),
	'content' => $L(
		'<p>My research explores the intersection of digital libraries, open science, scholarly communication and artificial intelligence. The eight areas below organise projects, publications, presentations and research notes into one connected body of work.</p>
[abc_research_areas]
<h2>Featured projects</h2>
[abc_list type="project" featured="1" limit="4"]',
		'<p>Mes recherches se situent à l’intersection des bibliothèques numériques, de la science ouverte, de la communication scientifique et de l’intelligence artificielle. Les huit domaines ci-dessous relient projets, publications, communications et notes de recherche au sein d’un même ensemble.</p>
[abc_research_areas]
<h2>Projets à la une</h2>
[abc_list type="project" featured="1" limit="4"]',
		'<p>يتناول بحثي نقطة التقاء المكتبات الرقمية والعلم المفتوح والاتصال العلمي والذكاء الاصطناعي. وتنظّم المجالات الثمانية أدناه المشاريع والمنشورات والمداخلات والملاحظات البحثية في منظومة واحدة مترابطة.</p>
[abc_research_areas]
<h2>مشاريع مختارة</h2>
[abc_list type="project" featured="1" limit="4"]'
	),
);

$cv_block = static function ( $h ) use ( $P ) {
	// $h: headings in order.
	return '<h2 id="cv-profile">' . $h['profile'] . '</h2>
<p>' . $h['profile_text'] . '</p>
<h2 id="cv-education">' . $h['education'] . '</h2><p>' . $P . '</p>
<h2 id="cv-positions">' . $h['positions'] . '</h2><p>' . $h['positions_text'] . ' ' . $P . '</p>
<h2 id="cv-experience">' . $h['experience'] . '</h2><p>' . $h['experience_text'] . ' ' . $P . '</p>
<h2 id="cv-research">' . $h['research'] . '</h2><p>' . $h['research_text'] . '</p>
<h2 id="cv-publications">' . $h['publications'] . '</h2>[abc_list type="publication" layout="cv" limit="100"]
<h2 id="cv-books">' . $h['books'] . '</h2>[abc_list type="book" layout="cv" limit="50"]
<h2 id="cv-projects">' . $h['projects'] . '</h2>[abc_list type="project" layout="cv" limit="50"]
<h2 id="cv-teaching">' . $h['teaching'] . '</h2>[abc_list type="teaching" layout="cv" limit="50"]
<h2 id="cv-supervision">' . $h['supervision'] . '</h2><p>' . $P . '</p>
<h2 id="cv-service">' . $h['service'] . '</h2>[abc_list type="service" layout="cv" limit="100"]
<h2 id="cv-conferences">' . $h['conferences'] . '</h2>[abc_list type="presentation" layout="cv" limit="100"]
<h2 id="cv-awards">' . $h['awards'] . '</h2><p>' . $P . '</p>
<h2 id="cv-certificates">' . $h['certificates'] . '</h2><p>' . $P . '</p>
<h2 id="cv-languages">' . $h['languages'] . '</h2><p>' . $P . '</p>
<h2 id="cv-skills">' . $h['skills'] . '</h2><p>' . $P . '</p>';
};

$pages['cv'] = array(
	'title'    => $L( 'Curriculum Vitae', 'Curriculum vitae', 'السيرة الذاتية' ),
	'slug'     => $L( 'cv', 'cv', 'al-sira-al-dhatiya' ),
	'template' => 'template-cv.php',
	'content'  => $L(
		$cv_block( array(
			'profile' => 'Professional Profile', 'profile_text' => 'Assistant Professor in Library and Information Science, with professional experience in academic and cultural heritage library environments and in developing and managing institutional digital repositories.',
			'education' => 'Education', 'positions' => 'Academic Positions', 'positions_text' => 'Assistant Professor, Library and Information Science.',
			'experience' => 'Professional Experience', 'experience_text' => 'Academic and cultural heritage libraries; development and management of institutional digital repositories.',
			'research' => 'Research', 'research_text' => 'Digital libraries, institutional repositories, open access, open science, scholarly communication, metadata, research data management, artificial intelligence in libraries.',
			'publications' => 'Publications', 'books' => 'Books', 'projects' => 'Projects', 'teaching' => 'Teaching', 'supervision' => 'Supervision',
			'service' => 'Academic Service', 'conferences' => 'Conferences', 'awards' => 'Awards', 'certificates' => 'Certificates', 'languages' => 'Languages', 'skills' => 'Technical Skills',
		) ),
		$cv_block( array(
			'profile' => 'Profil professionnel', 'profile_text' => 'Professeur assistant en sciences de l’information et des bibliothèques, avec une expérience professionnelle dans des bibliothèques universitaires et patrimoniales et dans le développement et la gestion de dépôts numériques institutionnels.',
			'education' => 'Formation', 'positions' => 'Postes académiques', 'positions_text' => 'Professeur assistant, sciences de l’information et des bibliothèques.',
			'experience' => 'Expérience professionnelle', 'experience_text' => 'Bibliothèques universitaires et patrimoniales ; développement et gestion de dépôts numériques institutionnels.',
			'research' => 'Recherche', 'research_text' => 'Bibliothèques numériques, dépôts institutionnels, accès ouvert, science ouverte, communication scientifique, métadonnées, gestion des données de la recherche, intelligence artificielle en bibliothèque.',
			'publications' => 'Publications', 'books' => 'Ouvrages', 'projects' => 'Projets', 'teaching' => 'Enseignement', 'supervision' => 'Encadrement',
			'service' => 'Service académique', 'conferences' => 'Communications', 'awards' => 'Distinctions', 'certificates' => 'Certificats', 'languages' => 'Langues', 'skills' => 'Compétences techniques',
		) ),
		$cv_block( array(
			'profile' => 'الملف المهني', 'profile_text' => 'أستاذ مساعد في علم المكتبات والمعلومات، يتمتع بخبرة مهنية في بيئات المكتبات الأكاديمية ومكتبات التراث الثقافي، وفي تطوير المستودعات الرقمية المؤسسية وإدارتها.',
			'education' => 'التكوين العلمي', 'positions' => 'المناصب الأكاديمية', 'positions_text' => 'أستاذ مساعد، علم المكتبات والمعلومات.',
			'experience' => 'الخبرة المهنية', 'experience_text' => 'المكتبات الأكاديمية ومكتبات التراث الثقافي؛ تطوير المستودعات الرقمية المؤسسية وإدارتها.',
			'research' => 'البحث', 'research_text' => 'المكتبات الرقمية، المستودعات المؤسسية، الوصول الحر، العلم المفتوح، الاتصال العلمي، البيانات الوصفية، إدارة بيانات البحث، الذكاء الاصطناعي في المكتبات.',
			'publications' => 'المنشورات', 'books' => 'الكتب', 'projects' => 'المشاريع', 'teaching' => 'التدريس', 'supervision' => 'الإشراف',
			'service' => 'الخدمة الأكاديمية', 'conferences' => 'المؤتمرات والمداخلات', 'awards' => 'الجوائز', 'certificates' => 'الشهادات', 'languages' => 'اللغات', 'skills' => 'المهارات التقنية',
		) )
	),
);

$pages['contact'] = array(
	'title'   => $L( 'Contact', 'Contact', 'اتصل بي' ),
	'slug'    => $L( 'contact', 'contact', 'ittisal' ),
	'content' => $L(
		'<p>For research collaboration, invited talks, peer review, supervision or any academic enquiry, please write using the form or the details below.</p>
[abc_contact_card]
[abc_contact_form]',
		'<p>Pour toute collaboration de recherche, invitation, évaluation, demande d’encadrement ou question académique, merci d’écrire via le formulaire ou aux coordonnées ci-dessous.</p>
[abc_contact_card]
[abc_contact_form]',
		'<p>للتعاون البحثي أو الدعوات العلمية أو التحكيم أو الإشراف أو أي استفسار أكاديمي، يُرجى المراسلة عبر النموذج أو باستخدام البيانات أدناه.</p>
[abc_contact_card]
[abc_contact_form]'
	),
);

$pages['discover'] = array(
	'title'   => $L( 'Research Discovery', 'Explorer la recherche', 'استكشاف الأبحاث' ),
	'slug'    => $L( 'discover', 'explorer', 'istikshaf' ),
	'content' => $L(
		'<p>Search across publications, books, projects, presentations, research notes, resources and pages.</p>[abc_search_form]',
		'<p>Recherchez dans les publications, ouvrages, projets, communications, notes de recherche, ressources et pages.</p>[abc_search_form]',
		'<p>ابحث في المنشورات والكتب والمشاريع والمداخلات والملاحظات البحثية والموارد والصفحات.</p>[abc_search_form]'
	),
);

/* ----------------------------------------------------------------- menus */
$menus = array(
	'primary' => array(
		array( 'page', 'home',      $L( 'Home', 'Accueil', 'الرئيسية' ) ),
		array( 'page', 'about',     $L( 'About', 'À propos', 'نبذة عني' ) ),
		array( 'page', 'research',  $L( 'Research', 'Recherche', 'البحث' ) ),
		array( 'archive', 'abc_publication',  $L( 'Publications', 'Publications', 'المنشورات' ) ),
		array( 'archive', 'abc_project',      $L( 'Projects', 'Projets', 'المشاريع' ) ),
		array( 'archive', 'abc_teaching',     $L( 'Teaching', 'Enseignement', 'التدريس' ) ),
		array( 'archive', 'abc_service',      $L( 'Academic Service', 'Service académique', 'الخدمة الأكاديمية' ) ),
		array( 'archive', 'abc_presentation', $L( 'Presentations', 'Communications', 'المداخلات' ) ),
		array( 'archive', 'abc_resource',     $L( 'Resources', 'Ressources', 'الموارد' ) ),
		array( 'archive', 'abc_note',         $L( 'Research Notes', 'Notes de recherche', 'ملاحظات بحثية' ) ),
		array( 'page', 'cv',        $L( 'CV', 'CV', 'السيرة الذاتية' ) ),
		array( 'page', 'contact',   $L( 'Contact', 'Contact', 'اتصل بي' ) ),
	),
	'footer' => array(
		array( 'page', 'cv',        $L( 'CV', 'CV', 'السيرة الذاتية' ) ),
		array( 'archive', 'abc_book', $L( 'Books', 'Ouvrages', 'الكتب' ) ),
		array( 'page', 'discover',  $L( 'Research Discovery', 'Explorer la recherche', 'استكشاف الأبحاث' ) ),
		array( 'page', 'contact',   $L( 'Contact', 'Contact', 'اتصل بي' ) ),
	),
);

/* --------------------------------------------------------- sample entries */
$posts = array();

// Publications – placeholders only (no invented bibliographic data).
$posts[] = array(
	'key' => 'pub-sample-1', 'type' => 'abc_publication', 'slug' => 'sample-publication-1', 'order' => 0,
	'title'   => $L( $P . ' Title of a journal article', $P . ' Titre d’un article de revue', $P . ' عنوان مقال في مجلة علمية' ),
	'content' => $L( '<p>Sample entry. Replace it with a verified publication: add authors, year, journal, DOI and abstract in the fields below.</p>', '<p>Notice d’exemple. Remplacez-la par une publication vérifiée : renseignez les auteurs, l’année, la revue, le DOI et le résumé dans les champs ci-dessous.</p>', '<p>مدخل تجريبي. استبدله بمنشور موثَّق: أضف المؤلفين والسنة والمجلة ومعرّف DOI والملخص في الحقول أدناه.</p>' ),
	'meta'    => array(
		'pub_authors'  => $P,
		'pub_journal'  => $P,
		'pub_abstract' => $L( $P . ' Paste the abstract here.', $P . ' Collez le résumé ici.', $P . ' ألصق الملخص هنا.' ),
		'pub_featured' => '1',
	),
	'terms'   => array( 'abc_pub_type' => array( 'journal-article' ), 'abc_research_area' => array( 'institutional-repositories', 'open-access-open-science' ) ),
);
$posts[] = array(
	'key' => 'pub-sample-2', 'type' => 'abc_publication', 'slug' => 'sample-publication-2', 'order' => 0,
	'title'   => $L( $P . ' Title of a conference paper', $P . ' Titre d’une communication de conférence', $P . ' عنوان ورقة مؤتمر' ),
	'content' => $L( '<p>Sample entry. Replace it with a verified conference paper.</p>', '<p>Notice d’exemple. Remplacez-la par une communication vérifiée.</p>', '<p>مدخل تجريبي. استبدله بورقة مؤتمر موثَّقة.</p>' ),
	'meta'    => array( 'pub_authors' => $P, 'pub_journal' => $P, 'pub_featured' => '1' ),
	'terms'   => array( 'abc_pub_type' => array( 'conference-paper' ), 'abc_research_area' => array( 'ai-in-libraries', 'digital-libraries' ) ),
);

// Book – placeholder (no ISBN, no publisher URL).
$posts[] = array(
	'key' => 'book-sample-1', 'type' => 'abc_book', 'slug' => 'sample-book', 'order' => 0,
	'title'   => $L( $P . ' Book title', $P . ' Titre de l’ouvrage', $P . ' عنوان الكتاب' ),
	'content' => $L( '<p>Sample entry. Add the description, publisher, year and ISBN only when verified, and upload the cover as the featured image.</p>', '<p>Notice d’exemple. Ajoutez la description, l’éditeur, l’année et l’ISBN uniquement lorsqu’ils sont vérifiés, et téléversez la couverture comme image à la une.</p>', '<p>مدخل تجريبي. أضف الوصف والناشر والسنة ورقم ISBN بعد التحقق منها فقط، وارفع الغلاف كصورة مميزة.</p>' ),
	'meta'    => array( 'book_author' => $P, 'book_publisher' => $P ),
	'terms'   => array( 'abc_research_area' => array( 'digital-libraries' ) ),
);

// Projects – names supplied by the researcher; details are placeholders.
$proj = static function ( $key, $slug, $order, $title, $excerpt, $areas, $rel ) use ( $P, $L ) {
	return array(
		'key' => $key, 'type' => 'abc_project', 'slug' => $slug, 'order' => $order,
		'title' => $title, 'excerpt' => $excerpt,
		'content' => $L( '<p>' . $P . ' Describe the project: context, aims, approach and results.</p>', '<p>' . $P . ' Décrivez le projet : contexte, objectifs, démarche et résultats.</p>', '<p>' . $P . ' صف المشروع: السياق والأهداف والمنهج والنتائج.</p>' ),
		'meta' => array(
			'project_question'   => $L( $P, $P, $P ),
			'project_problem'    => $L( $P, $P, $P ),
			'project_objectives' => $P,
			'project_method'     => $P,
			'project_tech'       => $P,
			'project_partners'   => $P,
			'project_outputs'    => $P,
			'project_featured'   => '1',
		),
		'terms' => array( 'abc_research_area' => $areas ),
		'rel'   => $rel,
	);
};
$posts[] = $proj(
	'proj-aggregator', 'algerian-national-open-access-repository-aggregator', 10,
	$L( 'Algerian National Open Access Repository Aggregator', 'Agrégateur national algérien des dépôts en accès ouvert', 'المجمِّع الوطني الجزائري لمستودعات الوصول الحر' ),
	$L( 'A project on aggregating records from open access repositories in Algeria. ' . $P, 'Un projet d’agrégation des notices des dépôts en accès ouvert en Algérie. ' . $P, 'مشروع لتجميع بيانات المستودعات ذات الوصول الحر في الجزائر. ' . $P ),
	array( 'institutional-repositories', 'open-access-open-science' ),
	array( 'project_pubs' => array( 'pub-sample-1' ), 'project_pres' => array( 'pres-sample-1' ) )
);
$posts[] = $proj(
	'proj-ir-development', 'university-institutional-repository-development', 20,
	$L( 'University Institutional Repository Development', 'Développement d’un dépôt institutionnel universitaire', 'تطوير مستودع رقمي مؤسسي جامعي' ),
	$L( 'A project on developing a university institutional repository. ' . $P, 'Un projet de développement d’un dépôt institutionnel universitaire. ' . $P, 'مشروع لتطوير مستودع رقمي مؤسسي جامعي. ' . $P ),
	array( 'institutional-repositories', 'metadata-knowledge-organization' ),
	array( 'project_pubs' => array( 'pub-sample-1' ) )
);
$posts[] = $proj(
	'proj-ai-discovery', 'ai-powered-academic-knowledge-discovery', 30,
	$L( 'AI-Powered Academic Knowledge Discovery', 'Découverte des connaissances académiques assistée par l’IA', 'اكتشاف المعرفة الأكاديمية بالذكاء الاصطناعي' ),
	$L( 'A project on applying artificial intelligence to academic knowledge discovery. ' . $P, 'Un projet d’application de l’intelligence artificielle à la découverte des connaissances académiques. ' . $P, 'مشروع لتطبيق الذكاء الاصطناعي في اكتشاف المعرفة الأكاديمية. ' . $P ),
	array( 'ai-in-libraries', 'digital-libraries' ),
	array( 'project_pubs' => array( 'pub-sample-2' ), 'project_pres' => array( 'pres-sample-2' ) )
);
$posts[] = $proj(
	'proj-analytics', 'library-collection-analytics', 40,
	$L( 'Library Collection Analytics', 'Analyse des collections de bibliothèque', 'تحليلات مقتنيات المكتبة' ),
	$L( 'A project on analysing library collections and their use. ' . $P, 'Un projet d’analyse des collections de bibliothèque et de leurs usages. ' . $P, 'مشروع لتحليل مقتنيات المكتبة واستخداماتها. ' . $P ),
	array( 'digital-transformation', 'research-data-management' ),
	array()
);

// Presentations – placeholders.
$posts[] = array(
	'key' => 'pres-sample-1', 'type' => 'abc_presentation', 'slug' => 'sample-presentation-1', 'order' => 0,
	'title'   => $L( $P . ' Presentation title', $P . ' Titre de la communication', $P . ' عنوان المداخلة' ),
	'content' => $L( '<p>Sample entry. Add the conference, date, location and slides when verified.</p>', '<p>Notice d’exemple. Ajoutez la conférence, la date, le lieu et les diapositives lorsqu’ils sont vérifiés.</p>', '<p>مدخل تجريبي. أضف المؤتمر والتاريخ والمكان والشرائح بعد التحقق منها.</p>' ),
	'meta'    => array( 'pres_conference' => $P ),
	'terms'   => array( 'abc_research_area' => array( 'institutional-repositories' ) ),
);
$posts[] = array(
	'key' => 'pres-sample-2', 'type' => 'abc_presentation', 'slug' => 'sample-presentation-2', 'order' => 0,
	'title'   => $L( $P . ' Invited talk title', $P . ' Titre de la conférence invitée', $P . ' عنوان المحاضرة المدعوة' ),
	'content' => $L( '<p>Sample entry.</p>', '<p>Notice d’exemple.</p>', '<p>مدخل تجريبي.</p>' ),
	'meta'    => array( 'pres_conference' => $P ),
	'terms'   => array( 'abc_research_area' => array( 'ai-in-libraries' ) ),
);

// Academic service – placeholders.
$posts[] = array(
	'key' => 'svc-sample-1', 'type' => 'abc_service', 'slug' => 'sample-service-peer-review', 'order' => 0,
	'title'   => $L( 'Peer Reviewer — ' . $P, 'Évaluateur — ' . $P, 'محكّم علمي — ' . $P ),
	'content' => $L( '', '', '' ),
	'meta'    => array( 'svc_org' => $P, 'svc_position' => $L( 'Peer Reviewer', 'Évaluateur (relecteur)', 'محكّم علمي' ), 'svc_description' => $L( $P . ' Journal or conference, period and scope of the review work.', $P . ' Revue ou conférence, période et nature du travail d’évaluation.', $P . ' المجلة أو المؤتمر والفترة وطبيعة عمل التحكيم.' ) ),
	'terms'   => array( 'abc_service_type' => array( 'peer-reviewer' ) ),
);
$posts[] = array(
	'key' => 'svc-sample-2', 'type' => 'abc_service', 'slug' => 'sample-service-open-science', 'order' => 0,
	'title'   => $L( 'Open Science Service — ' . $P, 'Service en science ouverte — ' . $P, 'خدمة في العلم المفتوح — ' . $P ),
	'content' => $L( '', '', '' ),
	'meta'    => array( 'svc_org' => $P, 'svc_position' => $P, 'svc_description' => $L( $P, $P, $P ) ),
	'terms'   => array( 'abc_service_type' => array( 'open-science-service' ) ),
);

// Teaching – the possible course topics, institution/level to be verified.
$courses = array(
	array( 'course-ismgmt',   $L( 'Scientific Management of Information Systems', 'Gestion scientifique des systèmes d’information', 'الإدارة العلمية لنظم المعلومات' ) ),
	array( 'course-collections', $L( 'Digital Collections Development', 'Développement des collections numériques', 'تنمية المجموعات الرقمية' ) ),
	array( 'course-libsystems', $L( 'Library Systems', 'Systèmes de bibliothèque', 'نظم المكتبات' ) ),
	array( 'course-docsoftware', $L( 'Documentary Software', 'Logiciels documentaires', 'البرمجيات الوثائقية' ) ),
	array( 'course-authority', $L( 'Authority Control & Subject Headings', 'Contrôle d’autorité et vedettes-matière', 'الضبط الاستنادي ورؤوس الموضوعات' ) ),
	array( 'course-scholcomm', $L( 'Scholarly Communication', 'Communication scientifique', 'الاتصال العلمي' ) ),
	array( 'course-oa',       $L( 'Open Access', 'Accès ouvert', 'الوصول الحر' ) ),
	array( 'course-dl',       $L( 'Digital Libraries', 'Bibliothèques numériques', 'المكتبات الرقمية' ) ),
);
foreach ( $courses as $i => $c ) {
	$posts[] = array(
		'key' => $c[0], 'type' => 'abc_teaching', 'slug' => $c[0], 'order' => ( $i + 1 ) * 10,
		'title'   => $c[1],
		'content' => $L( '<p>' . $P . ' Course description, objectives and level.</p>', '<p>' . $P . ' Description du cours, objectifs et niveau.</p>', '<p>' . $P . ' وصف المقرر وأهدافه ومستواه.</p>' ),
		'meta'    => array( 'teach_institution' => $P, 'teach_level' => $P, 'teach_period' => $P ),
		'terms'   => array( 'abc_teaching_section' => array( 'courses' ) ),
	);
}
$posts[] = array(
	'key' => 'teach-philosophy', 'type' => 'abc_teaching', 'slug' => 'teaching-philosophy', 'order' => 100,
	'title'   => $L( 'Teaching Philosophy', 'Philosophie pédagogique', 'فلسفة التدريس' ),
	'content' => $L( '<p>' . $P . ' Write a short statement of your teaching philosophy (about 200–300 words).</p>', '<p>' . $P . ' Rédigez une courte présentation de votre philosophie pédagogique (200 à 300 mots environ).</p>', '<p>' . $P . ' اكتب نبذة موجزة عن فلسفتك في التدريس (نحو 200–300 كلمة).</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_teaching_section' => array( 'teaching-philosophy' ) ),
);
$posts[] = array(
	'key' => 'teach-supervision', 'type' => 'abc_teaching', 'slug' => 'student-supervision', 'order' => 110,
	'title'   => $L( 'Student Supervision', 'Encadrement des étudiants', 'الإشراف على الطلبة' ),
	'content' => $L( '<p>' . $P . ' Levels, topics and number of supervised dissertations.</p>', '<p>' . $P . ' Niveaux, sujets et nombre de mémoires encadrés.</p>', '<p>' . $P . ' المستويات والموضوعات وعدد المذكرات المشرف عليها.</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_teaching_section' => array( 'student-supervision' ) ),
);
$posts[] = array(
	'key' => 'teach-research-supervision', 'type' => 'abc_teaching', 'slug' => 'research-supervision', 'order' => 120,
	'title'   => $L( 'Research Supervision', 'Encadrement de recherche', 'الإشراف البحثي' ),
	'content' => $L( '<p>' . $P . ' Doctoral or master’s research supervision, if applicable.</p>', '<p>' . $P . ' Encadrement doctoral ou de master, le cas échéant.</p>', '<p>' . $P . ' الإشراف على أطروحات الدكتوراه أو الماستر، إن وجد.</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_teaching_section' => array( 'research-supervision' ) ),
);
$posts[] = array(
	'key' => 'teach-materials', 'type' => 'abc_teaching', 'slug' => 'teaching-materials', 'order' => 130,
	'title'   => $L( 'Teaching Materials', 'Supports pédagogiques', 'المواد التعليمية' ),
	'content' => $L( '<p>' . $P . ' Link slides, handouts or exercises here (upload files or add URLs in the fields below).</p>', '<p>' . $P . ' Ajoutez ici des diapositives, polycopiés ou exercices (téléversez des fichiers ou indiquez des URL dans les champs ci-dessous).</p>', '<p>' . $P . ' أضف هنا الشرائح أو المطبوعات أو التمارين (ارفع الملفات أو أدخل الروابط في الحقول أدناه).</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_teaching_section' => array( 'teaching-materials' ) ),
);

// Resources – placeholders (no files).
$posts[] = array(
	'key' => 'res-sample-1', 'type' => 'abc_resource', 'slug' => 'sample-guide', 'order' => 10,
	'title'   => $L( $P . ' Guide title', $P . ' Titre du guide', $P . ' عنوان الدليل' ),
	'content' => $L( '<p>Sample resource. Upload a PDF, DOCX, PPTX, XLSX or CSV file in the “Resource file” box to enable the download button.</p>', '<p>Ressource d’exemple. Téléversez un fichier PDF, DOCX, PPTX, XLSX ou CSV dans le bloc « Resource file » pour activer le bouton de téléchargement.</p>', '<p>مورد تجريبي. ارفع ملف PDF أو DOCX أو PPTX أو XLSX أو CSV في مربع «Resource file» لتفعيل زر التنزيل.</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_resource_type' => array( 'guides' ), 'abc_research_area' => array( 'institutional-repositories' ) ),
);
$posts[] = array(
	'key' => 'res-sample-2', 'type' => 'abc_resource', 'slug' => 'sample-template', 'order' => 20,
	'title'   => $L( $P . ' Template title', $P . ' Titre du modèle', $P . ' عنوان القالب' ),
	'content' => $L( '<p>Sample resource.</p>', '<p>Ressource d’exemple.</p>', '<p>مورد تجريبي.</p>' ),
	'meta'    => array(),
	'terms'   => array( 'abc_resource_type' => array( 'templates' ), 'abc_research_area' => array( 'research-data-management' ) ),
);

// Research notes – general explanatory texts (no claims about the author).
$posts[] = array(
	'key' => 'note-ir', 'type' => 'abc_note', 'slug' => 'what-is-an-institutional-repository', 'order' => 0,
	'title'   => $L( 'What is an institutional repository?', 'Qu’est-ce qu’un dépôt institutionnel ?', 'ما المستودع الرقمي المؤسسي؟' ),
	'excerpt' => $L( 'A short introduction to what institutional repositories are, what they need to work well, and why they matter for researchers and institutions.', 'Une brève introduction à ce que sont les dépôts institutionnels, à ce qu’il leur faut pour bien fonctionner et à leur intérêt pour les chercheurs et les établissements.', 'مدخل موجز إلى مفهوم المستودعات المؤسسية، وما تحتاجه لتعمل بكفاءة، وأهميتها للباحثين والمؤسسات.' ),
	'content' => $L(
		'<p>An institutional repository is an online service through which a university or research organisation collects, preserves and makes available the intellectual output of its community: articles, theses, conference papers, datasets and teaching materials.</p>
<p>A good repository is more than a storage space. It relies on consistent metadata, persistent identifiers, clear rights statements and interoperability protocols such as OAI-PMH, so that its content can be discovered by search engines, aggregators and other repositories.</p>
<p>For researchers, depositing work in a repository increases visibility and supports long-term access. For institutions, it provides a trustworthy record of their research and a foundation for open access policies.</p>',
		'<p>Un dépôt institutionnel est un service en ligne par lequel une université ou un organisme de recherche collecte, préserve et diffuse la production intellectuelle de sa communauté : articles, thèses, communications, jeux de données et supports pédagogiques.</p>
<p>Un bon dépôt ne se réduit pas à un espace de stockage. Il repose sur des métadonnées cohérentes, des identifiants pérennes, des mentions de droits claires et des protocoles d’interopérabilité tels qu’OAI-PMH, afin que ses contenus puissent être repérés par les moteurs de recherche, les agrégateurs et les autres dépôts.</p>
<p>Pour les chercheurs, déposer ses travaux dans un dépôt accroît leur visibilité et garantit un accès durable. Pour les établissements, c’est une mémoire fiable de leur recherche et le socle d’une politique d’accès ouvert.</p>',
		'<p>المستودع الرقمي المؤسسي خدمة إلكترونية تقوم من خلالها الجامعة أو مؤسسة البحث بجمع الإنتاج الفكري لمجتمعها العلمي وحفظه وإتاحته، ويشمل ذلك المقالات والأطروحات وأوراق المؤتمرات ومجموعات البيانات والمواد التعليمية.</p>
<p>والمستودع الجيد ليس مجرد مساحة للتخزين؛ فهو يقوم على بيانات وصفية متسقة ومعرّفات دائمة وبيانات واضحة عن الحقوق وبروتوكولات للتشغيل البيني مثل OAI-PMH، حتى يمكن لمحركات البحث والمجمِّعات والمستودعات الأخرى اكتشاف محتواه.</p>
<p>وبالنسبة للباحثين، فإن إيداع أعمالهم في المستودع يزيد من ظهورها ويدعم إتاحتها على المدى الطويل. أما بالنسبة للمؤسسات، فهو سجل موثوق لإنتاجها البحثي وأساس لسياسات الوصول الحر.</p>'
	),
	'meta'    => array(),
	'terms'   => array( 'abc_note_category' => array( 'institutional-repositories' ), 'abc_research_area' => array( 'institutional-repositories' ) ),
);
$posts[] = array(
	'key' => 'note-oa-os', 'type' => 'abc_note', 'slug' => 'open-access-and-open-science', 'order' => 0,
	'title'   => $L( 'Open access and open science: how are they related?', 'Accès ouvert et science ouverte : quel lien ?', 'الوصول الحر والعلم المفتوح: ما العلاقة بينهما؟' ),
	'excerpt' => $L( 'Open access and open science are often confused. This note explains the difference and the role libraries play in both.', 'L’accès ouvert et la science ouverte sont souvent confondus. Cette note en explique la différence et le rôle des bibliothèques dans les deux.', 'كثيرًا ما يُخلط بين الوصول الحر والعلم المفتوح. توضح هذه الملاحظة الفرق بينهما ودور المكتبات في كليهما.' ),
	'content' => $L(
		'<p>Open access and open science are often used interchangeably, but they are not the same thing.</p>
<p>Open access concerns the free availability of research outputs, mainly scholarly articles and books, together with the right to read, download and reuse them under clear licences.</p>
<p>Open science is broader. It covers open access, but also open research data, open source software and tools, transparent methods, citizen participation and more equitable ways of evaluating research.</p>
<p>Seen this way, open access is one essential building block of open science. Libraries and repositories contribute to both: they provide infrastructure, metadata expertise and training so that research can be shared responsibly.</p>',
		'<p>L’accès ouvert et la science ouverte sont souvent employés l’un pour l’autre, mais ils ne recouvrent pas la même réalité.</p>
<p>L’accès ouvert concerne la disponibilité gratuite des résultats de la recherche, principalement les articles et les ouvrages scientifiques, ainsi que le droit de les lire, de les télécharger et de les réutiliser selon des licences claires.</p>
<p>La science ouverte est plus large. Elle inclut l’accès ouvert, mais aussi l’ouverture des données de la recherche, des logiciels et des outils, la transparence des méthodes, la participation citoyenne et des modes d’évaluation de la recherche plus équitables.</p>
<p>Dans cette perspective, l’accès ouvert est l’une des briques essentielles de la science ouverte. Les bibliothèques et les dépôts contribuent aux deux : ils offrent l’infrastructure, l’expertise en métadonnées et la formation nécessaires à un partage responsable de la recherche.</p>',
		'<p>كثيرًا ما يُستخدم مصطلحا الوصول الحر والعلم المفتوح بوصفهما مترادفين، غير أنهما ليسا الشيء نفسه.</p>
<p>يتعلق الوصول الحر بإتاحة نتائج البحث مجانًا، وفي مقدمتها المقالات والكتب العلمية، مع حق قراءتها وتنزيلها وإعادة استخدامها بموجب تراخيص واضحة.</p>
<p>أما العلم المفتوح فهو أوسع نطاقًا؛ إذ يشمل الوصول الحر، ويمتد كذلك إلى بيانات البحث المفتوحة والبرمجيات والأدوات مفتوحة المصدر وشفافية المناهج والمشاركة المجتمعية وطرق أكثر إنصافًا لتقييم البحث.</p>
<p>وبهذا المعنى يُعد الوصول الحر لبنة أساسية من لبنات العلم المفتوح. وتسهم المكتبات والمستودعات في الأمرين معًا، فهي توفر البنية التحتية والخبرة في البيانات الوصفية والتدريب اللازم لمشاركة البحث بمسؤولية.</p>'
	),
	'meta'    => array(),
	'terms'   => array( 'abc_note_category' => array( 'open-access' ), 'abc_research_area' => array( 'open-access-open-science' ) ),
);

return array(
	'taxonomies' => $tax,
	'pages'      => $pages,
	'menus'      => $menus,
	'posts'      => $posts,
);
