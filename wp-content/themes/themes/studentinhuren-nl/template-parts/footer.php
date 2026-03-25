<?php
if (!defined('ABSPATH')) exit;

$support_email = 'support@student-inhuren.nl';

$footer_categories = [
    [
        'title' => 'Office & Operations',
        'items' => [
            ['label' => 'Financieel personeel',      'url' => home_url('/financieel-personeel/')],
            ['label' => 'Werkstudent',               'url' => home_url('/wat-is-een-werkstudent/')],
            ['label' => 'Administratief medewerker', 'url' => home_url('/administratief-medewerker/')],
            ['label' => 'Backoffice medewerker',     'url' => home_url('/backoffice-medewerker/')],
            ['label' => 'Officemanager',             'url' => home_url('/officemanager/')],
            ['label' => 'Planner',                   'url' => home_url('/planner/')],
            ['label' => 'Archiefmedewerker',         'url' => home_url('/archiefmedewerker/')],
            ['label' => 'Data entry medewerker',     'url' => home_url('/data-entry-medewerker/')],
        ],
    ],
    [
        'title' => 'Online Marketing & Communicatie',
        'items' => [
            ['label' => 'Online marketing student', 'url' => home_url('/online-marketing-student/')],
            ['label' => 'SEO',                      'url' => home_url('/seo-student/')],
            ['label' => 'SEA',                      'url' => home_url('/sea/')],
            ['label' => 'Social media',             'url' => home_url('/social-media-student-inhuren/')],
            ['label' => 'Website laten maken',      'url' => home_url('/website-laten-maken/')],
            ['label' => 'Content marketeer',        'url' => home_url('/content-marketeer/')],
        ],
    ],
    [
        'title' => 'Software & AI',
        'items' => [
            ['label' => 'Software development',    'url' => home_url('/software-development/')],
            ['label' => 'Laravel developer',       'url' => home_url('/laravel-developer/')],
            ['label' => 'PHP developer',           'url' => home_url('/php-developer-inhuren/')],
            ['label' => 'WordPress development',   'url' => home_url('/wordpress-development/')],
            ['label' => 'Shopify development',     'url' => home_url('/shopify-webshop-laten-bouwen/')],
            ['label' => 'Artificial intelligence', 'url' => home_url('/artificial-intelligence/')],
            ['label' => 'Javascript developer',    'url' => home_url('/javascript-developer/')],
            ['label' => 'Data engineer',           'url' => home_url('/data-engineer-inhuren/')],
        ],
    ],
    [
        'title' => 'Creative & Media',
        'items' => [
            ['label' => 'Grafisch vormgever',    'url' => home_url('/grafisch-vormgever/')],
            ['label' => 'Illustrator',           'url' => home_url('/illustrator/')],
            ['label' => 'Webdesigner',           'url' => home_url('/webdesigner-inhuren/')],
            ['label' => 'Tekenaar',              'url' => home_url('/tekenaar-inhuren/')],
            ['label' => 'Logo laten ontwerpen',  'url' => home_url('/logo-laten-ontwerpen/')],
            ['label' => 'Tekstschrijver',        'url' => home_url('/tekstschrijver-inhuren/')],
            ['label' => 'Video editor',          'url' => home_url('/video-editor/')],
            ['label' => 'Fotograaf',             'url' => home_url('/fotograaf-inhuren/')],
        ],
    ],
    [
        'title' => 'IT & Hardware',
        'items' => [
            ['label' => 'Hardware migratie',      'url' => home_url('/hardware-migratie/')],
            ['label' => 'Floorwalker',            'url' => home_url('/floorwalker/')],
            ['label' => 'ICT student',            'url' => home_url('/ict-student/')],
            ['label' => 'ICT verhuizing',         'url' => home_url('/ict-verhuizing/')],
            ['label' => 'Cablemanagement',        'url' => home_url('/cablemanagement/')],
            ['label' => 'Helpdeskmedewerker',     'url' => home_url('/helpdeskmedewerker-inhuren/')],
            ['label' => 'Technisch tekenaar',     'url' => home_url('/technisch-tekenaar/')],
        ],
    ],
    [
        'title' => 'Logistiek & Fysiek',
        'items' => [
            ['label' => 'Monteurs',            'url' => home_url('/monteurs-inhuren/')],
            ['label' => 'Klusser',             'url' => home_url('/klusser-inhuren/')],
            ['label' => 'Klus student',        'url' => home_url('/klus-student-inhuren/')],
            ['label' => 'Studentchauffeurs',   'url' => home_url('/studentchauffeurs-inhuren/')],
            ['label' => 'Bijrijder',           'url' => home_url('/bijrijder-inhuren/')],
            ['label' => 'Logistiek medewerker','url' => home_url('/logistiek-medewerker-inhuren/')],
            ['label' => 'Sjouwers',            'url' => home_url('/sjouwers-inhuren/')],
            ['label' => 'Verhuizers',          'url' => home_url('/student-verhuizers-inhuren/')],
        ],
    ],
    [
        'title' => 'Opdrachten',
        'items' => [
            ['label' => 'Marketing',             'url' => home_url('/vacatures/?filter_vakgebied[]=marketing')],
            ['label' => 'Communicatie',          'url' => home_url('/vacatures/?filter_vakgebied[]=communicatie')],
            ['label' => 'Web Development',       'url' => home_url('/vacatures/?filter_vakgebied[]=web-development')],
            ['label' => 'Software Development',  'url' => home_url('/vacatures/?filter_vakgebied[]=software-development')],
            ['label' => 'Office',                'url' => home_url('/vacatures/?filter_vakgebied[]=office')],
            ['label' => 'IT & Hardware',         'url' => home_url('/vacatures/?filter_vakgebied[]=it-hardware')],
            ['label' => 'Grafische vormgeving',  'url' => home_url('/vacatures/?filter_vakgebied[]=grafische-vormgeving')],
            ['label' => 'Artificial Intelligence','url' => home_url('/vacatures/?filter_vakgebied[]=artificial-intelligence')],
            ['label' => 'Logistiek',             'url' => home_url('/vacatures/?filter_vakgebied[]=logistiek')],
        ],
    ],
];
?>

<footer class="si-footer" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Footer</h2>

    <div class="si-footer__inner">
        <div class="si-footer__layout">

            <aside class="si-footer__side">

                <section class="si-footer__side-block">
                    <h3 class="si-footer__heading">Contact</h3>
                    <ul>
                        <li>Sustainable Recruitment Marketing B.V.</li>
                        <li><a href="tel:+31852392040">085 239 20 40</a></li>
                        <li><a href="mailto:<?php echo esc_attr($support_email); ?>"><?php echo esc_html($support_email); ?></a></li>
                        <li>Veenland 31, 2291 NS, Wateringen</li>
                    </ul>
                    <div class="si-footer__socials" aria-label="Social media">
                        <a href="https://www.linkedin.com/company/studentinhuren/" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="ph ph-linkedin-logo" aria-hidden="true"></i>
                        </a>
                        <a href="https://www.instagram.com/studentinhuren/" target="_blank" rel="noopener" aria-label="Instagram">
                            <i class="ph ph-instagram-logo" aria-hidden="true"></i>
                        </a>
                    </div>
                </section>

                <section class="si-footer__side-block">
                    <h3 class="si-footer__heading">Werkgever</h3>
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/vacatures/')); ?>">Overzicht</a></li>
                        <li><a href="<?php echo esc_url(home_url('/diensten/')); ?>">Diensten</a></li>
                        <li><a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>">Informatie aanvragen</a></li>
                        <li><a href="https://platform.student-inhuren.nl/aanmelden">Aanmelden</a></li>
                    </ul>
                </section>

                <section class="si-footer__side-block">
                    <h3 class="si-footer__heading">Werkzoekende</h3>
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/vacatures/')); ?>">Bekijk overzicht</a></li>
                        <li><a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a></li>
                        <li><a href="https://platform.student-inhuren.nl/inloggen">Inloggen</a></li>
                        <li><a href="https://platform.student-inhuren.nl/aanmelden">Profiel aanmaken</a></li>
                        <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
                    </ul>
                </section>

            </aside>

            <div class="si-footer__topics">
                <?php foreach ($footer_categories as $category) : ?>
                    <section class="si-footer__topic-col">
                        <h3 class="si-footer__heading"><?php echo esc_html($category['title']); ?></h3>
                        <ul>
                            <?php foreach ($category['items'] as $item) : ?>
                                <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="si-footer__bottom">
        <div class="si-footer__bottom-inner">
            <span>&copy; <?php echo date('Y'); ?> Sustainable Recruitment Marketing B.V.</span>
            <nav aria-label="Footer juridisch" class="si-footer__legal-nav">
                <a href="<?php echo esc_url(home_url('/privacybeleid/')); ?>">Privacybeleid</a>
                <a href="<?php echo esc_url(home_url('/algemene-voorwaarden/')); ?>">Algemene voorwaarden</a>
                <a href="<?php echo esc_url(home_url('/sitemap/')); ?>">Sitemap</a>
            </nav>
        </div>
    </div>
</footer>
