<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GCWP_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_init',            [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function register_menu() {
        add_menu_page(
            'GoatCounter', 'Analytics', 'manage_options', 'goatcounter-wp',
            [ $this, 'page_stats' ], 'dashicons-chart-area', 80
        );
        add_submenu_page( 'goatcounter-wp', 'Estadístiques', 'Estadístiques', 'manage_options', 'goatcounter-wp', [ $this, 'page_stats' ] );
        add_submenu_page( 'goatcounter-wp', 'Configuració',  'Configuració',  'manage_options', 'goatcounter-wp-settings', [ $this, 'page_settings' ] );
    }

    public function register_settings() {
        register_setting( 'gcwp_options', 'gcwp_site_code', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'gcwp_options', 'gcwp_api_token', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'goatcounter' ) === false ) return;
        wp_enqueue_style( 'gcwp-admin', GCWP_URL . 'assets/admin.css', [], GCWP_VERSION );

        wp_register_script( 'gcwp-admin-tabs', false, [], GCWP_VERSION, true );
        wp_enqueue_script( 'gcwp-admin-tabs' );
        wp_add_inline_script( 'gcwp-admin-tabs', $this->tabs_script() );
    }

    /* ── Inline JS for tab switching ────────────────────── */
    private function tabs_script() {
        return 'function gcwpTab(e, view) {'
             . '    if (e && e.preventDefault) e.preventDefault();'
             . '    var tabs   = document.querySelectorAll(".gcwp-tab");'
             . '    var panels = document.querySelectorAll(".gcwp-panel");'
             . '    var i;'
             . '    for (i = 0; i < tabs.length; i++) {'
             . '        if (tabs[i].getAttribute("data-view") === view) {'
             . '            tabs[i].className = tabs[i].className.replace(/\bgcwp-active\b/g, "").trim() + " gcwp-active";'
             . '        } else {'
             . '            tabs[i].className = tabs[i].className.replace(/\bgcwp-active\b/g, "").trim();'
             . '        }'
             . '    }'
             . '    for (i = 0; i < panels.length; i++) {'
             . '        panels[i].style.display = panels[i].getAttribute("data-panel") === view ? "block" : "none";'
             . '    }'
             . '    try { localStorage.setItem("gcwp_tab", view); } catch (x) {}'
             . '}'
             . '(function() {'
             . '    var saved;'
             . '    try { saved = localStorage.getItem("gcwp_tab"); } catch (x) {}'
             . '    if (saved === "pagines" || saved === "dispositius") {'
             . '        gcwpTab(null, saved);'
             . '    }'
             . '})();';
    }

    /* ── Settings page ──────────────────────────────────── */
    public function page_settings() {
        $site_code = get_option( 'gcwp_site_code', '' );
        ?>
        <div class="wrap gcwp-wrap">        <style>
            .gcwp-period-nav--pages { margin: 0 0 .75rem; }
            .gcwp-period-hint { color:#6b7280; font-size:12px; margin: -.25rem 0 1rem; }
        </style>

            <h1>GoatCounter — Configuració</h1>
            <div class="gcwp-setup-box">
                <h2>Com configurar GoatCounter</h2>
                <ol>
                    <li>Crea un compte gratuït a <a href="https://www.goatcounter.com" target="_blank" rel="noopener">goatcounter.com</a>.</li>
                    <li>El codi de lloc és el subdomini del teu dashboard. Exemple: si la URL és <code>mysitee.goatcounter.com</code>, el codi és <code>mysite</code>.</li>
                    <li>Obtén el token API a <strong>Settings → API</strong> del teu dashboard GoatCounter.</li>
                    <li>Enganxa els dos valors aquí sota i desa.</li>
                </ol>
            </div>
            <form method="post" action="options.php">
                <?php settings_fields( 'gcwp_options' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="gcwp_site_code">Codi de lloc</label></th>
                        <td>
                            <input type="text" id="gcwp_site_code" name="gcwp_site_code"
                                   value="<?php echo esc_attr( get_option( 'gcwp_site_code' ) ); ?>"
                                   class="regular-text" placeholder="mysite" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gcwp_api_token">Token API</label></th>
                        <td>
                            <input type="password" id="gcwp_api_token" name="gcwp_api_token"
                                   value="<?php echo esc_attr( get_option( 'gcwp_api_token' ) ); ?>"
                                   class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'Desa la configuració' ); ?>
            </form>
        </div>
        <?php
    }

    /* ── Stats page ─────────────────────────────────────── */
    public function page_stats() {
        $api = new GCWP_API();

        if ( ! $api->is_configured() ) {
            $settings_url = admin_url( 'admin.php?page=goatcounter-wp-settings' );
            echo '<div class="wrap gcwp-wrap">        <style>
            .gcwp-period-nav--pages { margin: 0 0 .75rem; }
            .gcwp-period-hint { color:#6b7280; font-size:12px; margin: -.25rem 0 1rem; }
        </style>
<div class="gcwp-notice">'
               . '⚙️ Configura el codi de lloc i el token API a <a href="' . esc_url( $settings_url ) . '">Configuració</a>.'
               . '</div></div>';
            return;
        }

        $valid  = [ 'day', 'week', 'month', 'quarter', 'year' ];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter, no state change.
        $period = isset( $_GET['period'] ) && in_array( $_GET['period'], $valid, true )
                ? sanitize_key( $_GET['period'] ) : 'week';

        $periods = [
            'day'     => [ 'label' => 'Avui',       'sub' => '24h' ],
            'week'    => [ 'label' => '7 dies',      'sub' => 'última setmana' ],
            'month'   => [ 'label' => '30 dies',     'sub' => 'últim mes' ],
            'quarter' => [ 'label' => 'Trimestre',   'sub' => '90 dies' ],
            'year'    => [ 'label' => 'Any',         'sub' => '365 dies' ],
        ];

        // Page-period filter (for Pàgines/Seccions)
        $valid_pp     = [ 'day', 'week', 'month', 'quarter', 'year', 'all' ];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $pages_period = isset( $_GET['pp'] ) && in_array( $_GET['pp'], $valid_pp, true ) ? sanitize_key( $_GET['pp'] ) : 'all';
        $api_pp       = ( $pages_period === 'all' ) ? 'year' : $pages_period;

        // Fetch data
        $totals    = $api->get_totals( $period );
        $hits      = $api->get_hits( $api_pp, 50 );
        $browsers  = $api->get_browsers( $period );
        $systems   = $api->get_systems( $period );
        $locations = $api->get_locations( $period );

        $total_visits = $totals['total'] ?? 0;
        $daily_stats  = $totals['stats'] ?? [];
        $pages        = $hits['hits'] ?? [];
        $browser_list = $browsers['stats'] ?? [];
        $system_list  = $systems['stats'] ?? [];
        $country_list = $locations['stats'] ?? [];

        $chart_data   = $this->prepare_chart_data( $daily_stats, $period );
        $dow_data     = $this->dow_distribution( $daily_stats );

        $site_code = get_option( 'gcwp_site_code' );
        ?>
        <div class="wrap gcwp-wrap">        <style>
            .gcwp-period-nav--pages { margin: 0 0 .75rem; }
            .gcwp-period-hint { color:#6b7280; font-size:12px; margin: -.25rem 0 1rem; }
        </style>


        <!-- Top bar -->
        <div class="gcwp-topbar">
            <h1>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D51F17" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Analytics
            </h1>
            <a href="https://<?php echo esc_attr( $site_code ); ?>.goatcounter.com" target="_blank" rel="noopener" class="gcwp-gc-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                GoatCounter
            </a>
        </div>

        <!-- Period selector -->
        <nav class="gcwp-period-nav" aria-label="Filtre temporal">
            <?php foreach ( $periods as $key => $p ) :
                $url    = esc_url( add_query_arg( 'period', $key ) );
                $active = $period === $key ? ' active' : '';
                ?>
                <a href="<?php echo esc_url( $url ); ?>" class="gcwp-period-opt<?php echo esc_attr( $active ); ?>">
                    <?php echo esc_html( $p['label'] ); ?>
                    <span class="gcwp-period-sub"><?php echo esc_html( $p['sub'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Metric cards -->
        <div class="gcwp-metrics">
            <div class="gcwp-metric">
                <div class="gcwp-metric-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D51F17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div>
                    <span class="gcwp-metric-val"><?php echo esc_html( number_format_i18n( $total_visits ) ); ?></span>
                    <span class="gcwp-metric-lbl">Visites</span>
                </div>
            </div>
            <?php if ( ! empty( $pages ) ) : ?>
            <div class="gcwp-metric">
                <div class="gcwp-metric-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D51F17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <span class="gcwp-metric-val"><?php echo count( $pages ); ?></span>
                    <span class="gcwp-metric-lbl">Pàgines</span>
                </div>
            </div>
            <?php endif; ?>
            <?php if ( ! empty( $country_list ) ) : ?>
            <div class="gcwp-metric">
                <div class="gcwp-metric-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D51F17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <span class="gcwp-metric-val"><?php echo count( $country_list ); ?></span>
                    <span class="gcwp-metric-lbl">Països</span>
                </div>
            </div>
            <?php endif; ?>
            <?php if ( ! empty( $browser_list ) ) : ?>
            <div class="gcwp-metric">
                <div class="gcwp-metric-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D51F17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="2" y1="9" x2="22" y2="9"/><line x1="8" y1="3" x2="8" y2="9"/></svg>
                </div>
                <div>
                    <span class="gcwp-metric-val"><?php echo esc_html( $browser_list[0]['name'] ?? '—' ); ?></span>
                    <span class="gcwp-metric-lbl">Navegador #1</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- View tabs -->
        <div class="gcwp-tabs-row" role="tablist">
            <a href="#" class="gcwp-tab gcwp-active" data-view="temporal" role="tab" onclick="gcwpTab(event,'temporal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Temporal
            </a>
            <a href="#" class="gcwp-tab" data-view="pagines" role="tab" onclick="gcwpTab(event,'pagines')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Pàgines
            </a>
            <a href="#" class="gcwp-tab" data-view="dispositius" role="tab" onclick="gcwpTab(event,'dispositius')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                Dispositius
            </a>
        </div>

        <!-- ═══ VIEW: TEMPORAL ══════════════════════════════ -->
        <div id="gcwp-view-temporal" class="gcwp-panel" data-panel="temporal">

            <?php if ( $total_visits > 0 ) : ?>

            <!-- Time series chart -->
            <div class="gcwp-block">
                <div class="gcwp-block-header">
                    <p class="gcwp-block-title"><?php echo esc_html( $this->chart_title( $period ) ); ?></p>
                    <span class="gcwp-block-badge"><?php echo esc_html( number_format_i18n( $total_visits ) ); ?> visites</span>
                </div>
                <div class="gcwp-svg-wrap"><?php echo wp_kses( $this->render_svg_chart( $chart_data ), $this->svg_allowed_html() ); ?></div>
            </div>

            <!-- Day of week -->
            <?php if ( ! empty( $dow_data ) ) : ?>
            <div class="gcwp-block">
                <div class="gcwp-block-header">
                    <p class="gcwp-block-title">Distribució per dia de la setmana</p>
                </div>
                <?php $max_dow = max( array_column( $dow_data, 'count' ) ); ?>
                <div class="gcwp-dow">
                    <?php foreach ( $dow_data as $day => $d ) :
                        $bar    = $max_dow > 0 ? round( $d['count'] / $max_dow * 100 ) : 0;
                        $is_max = $d['count'] === $max_dow && $max_dow > 0;
                        $zero   = $d['count'] === 0;
                        $row_class = $is_max ? ' gcwp-dow-max' : ( $zero ? ' gcwp-dow-zero' : '' );
                    ?>
                    <div class="gcwp-dow-row<?php echo esc_attr( $row_class ); ?>">
                        <span class="gcwp-dow-day"><?php echo esc_html( $day ); ?></span>
                        <div class="gcwp-dow-track">
                            <div class="gcwp-dow-fill" style="width:<?php echo esc_attr( $bar ); ?>%"></div>
                        </div>
                        <span class="gcwp-dow-stat">
                            <strong><?php echo esc_html( $d['pct'] ); ?>%</strong>
                            <?php if ( $d['count'] > 0 ) echo esc_html( '· ' . number_format_i18n( $d['count'] ) . ' vis.' ); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <div class="gcwp-block">
                <p class="gcwp-empty">Encara no hi ha visites registrades per al període seleccionat. Comprova que el codi de seguiment és actiu.</p>
            </div>
            <?php endif; ?>

        </div><!-- /temporal -->

        <!-- ═══ VIEW: PÀGINES ═══════════════════════════════ -->
        <div id="gcwp-view-pagines" class="gcwp-panel" data-panel="pagines" style="display:none">

            <nav class="gcwp-period-nav gcwp-period-nav--pages" aria-label="Filtre per període pàgines">
                <?php
                $page_periods = [
                    'day'     => [ 'label' => 'Dia' ],
                    'week'    => [ 'label' => 'Setmana' ],
                    'month'   => [ 'label' => 'Mes' ],
                    'quarter' => [ 'label' => 'Trimestre' ],
                    'year'    => [ 'label' => 'Any' ],
                    'all'     => [ 'label' => 'Totalitat' ],
                ];
                $pp = $pages_period;
                foreach ( $page_periods as $pkey => $pl ) :
                    $purl = esc_url( add_query_arg( [ 'pp' => $pkey ] ) );
                    $pact = $pp === $pkey ? ' active' : '';
                ?>
                <a href="<?php echo $purl; ?>" class="gcwp-period-opt<?php echo esc_attr( $pact ); ?>"><?php echo esc_html( $pl['label'] ); ?></a>
                <?php endforeach; ?>
            </nav>
            <?php
            // Range display
            if ( $pp === 'all' ) {
                echo '<div class="gcwp-period-hint">Mostrant dades totals disponibles</div>';
            } else {
                $rmap = [ 'day' => 0, 'week' => 6, 'month' => 30, 'quarter' => 90, 'year' => 365 ];
                $days = $rmap[$pp] ?? 7;
                $pf   = gmdate( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
                $pt   = gmdate( 'Y-m-d' );
                echo '<div class="gcwp-period-hint">Període: ' . esc_html( $pf ) . ' → ' . esc_html( $pt ) . '</div>';
            }
            ?>

            <div class="gcwp-block">
                <div class="gcwp-block-header">
                    <p class="gcwp-block-title">Seccions</p>
                </div>
                <?php
                $sections = [];
                if ( ! empty( $pages ) ) {
                    foreach ( $pages as $h ) {
                        $path = $h['path'] ?? $h['title'] ?? '';
                        $parts = explode( '/', trim( $path, '/' ) );
                        if ( empty( $parts[0] ) ) { $sec = 'home'; }
                        elseif ( preg_match( '/^[a-z]{2}$/', $parts[0] ) && isset( $parts[1] ) ) { $sec = $parts[1]; }
                        else { $sec = $parts[0]; }
                        $map_sec = [
                            'agenda'=>'Agenda','concerts'=>'Agenda','gigs'=>'Agenda',
                            'biografia'=>'Biografia','biography'=>'Biografia',
                            'discografia'=>'Discografia','discography'=>'Discografia',
                            'videos'=>'Vídeos','video'=>'Vídeos',
                            'fotos'=>'Fotografies','photos'=>'Fotografies',
                            'premsa'=>'Premsa','press'=>'Premsa',
                            'contacte'=>'Contacte','contact'=>'Contacte',
                            'contractacio'=>'Contractació','booking'=>'Contractació',
                            'home'=>'Inici',
                        ];
                        $sname = $map_sec[$sec] ?? ucfirst($sec);
                        $sections[$sname] = ($sections[$sname] ?? 0) + intval( $h['count'] ?? 0 );
                    }
                    arsort( $sections );
                    $maxs = ! empty( $sections ) ? max( $sections ) : 1;
                    foreach ( $sections as $sname => $scnt ) :
                        $bp = round( ( $scnt / $maxs ) * 100 );
                ?>
                <div class="gcwp-hbar-row">
                    <div class="gcwp-hbar-label"><?php echo esc_html( $sname ); ?></div>
                    <div class="gcwp-hbar-track"><div class="gcwp-hbar-fill" style="width:<?php echo esc_attr( $bp ); ?>%"></div></div>
                    <div class="gcwp-hbar-val"><?php echo esc_html( number_format_i18n( $scnt ) ); ?></div>
                </div>
                <?php endforeach; if ( empty( $sections ) ) echo '<p class="gcwp-empty">Sense dades de seccions</p>'; } else { echo '<p class="gcwp-empty">Sense dades per al període seleccionat.</p>'; } ?>
            </div>

            <div class="gcwp-block">
                <div class="gcwp-block-header">
                    <p class="gcwp-block-title">Pàgines més visitades</p>
                    <?php if ( ! empty( $pages ) ) : ?>
                    <span class="gcwp-block-badge"><?php echo count( $pages ); ?> pàgines · <?php echo esc_html( number_format_i18n( $total_visits ) ); ?> visites</span>
                    <?php endif; ?>
                </div>
                <?php if ( empty( $pages ) ) : ?>
                    <p class="gcwp-empty">Sense dades per al període seleccionat.</p>
                <?php else : ?>
                    <?php $this->render_pages_list( $pages ); ?>
                <?php endif; ?>
            </div>

        </div><!-- /pagines -->

        <!-- ═══ VIEW: DISPOSITIUS ═══════════════════════════ -->
        <div id="gcwp-view-dispositius" class="gcwp-panel" data-panel="dispositius" style="display:none">

            <div class="gcwp-grid">
                <div class="gcwp-block" style="margin-bottom:0">
                    <div class="gcwp-block-header">
                        <p class="gcwp-block-title">Navegadors</p>
                        <?php if ( ! empty( $browser_list ) ) : ?>
                        <span class="gcwp-block-badge"><?php echo count( $browser_list ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php $this->render_hbar_list( $browser_list ); ?>
                </div>
                <div class="gcwp-block" style="margin-bottom:0">
                    <div class="gcwp-block-header">
                        <p class="gcwp-block-title">Sistema operatiu</p>
                        <?php if ( ! empty( $system_list ) ) : ?>
                        <span class="gcwp-block-badge"><?php echo count( $system_list ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php $this->render_hbar_list( $system_list ); ?>
                </div>
            </div>

            <div class="gcwp-block">
                <div class="gcwp-block-header">
                    <p class="gcwp-block-title">Països</p>
                    <?php if ( ! empty( $country_list ) ) : ?>
                    <span class="gcwp-block-badge"><?php echo count( $country_list ); ?> països</span>
                    <?php endif; ?>
                </div>
                <?php $this->render_hbar_list( $country_list ); ?>
            </div>

        </div><!-- /dispositius -->

        </div><!-- .gcwp-wrap -->
        <?php
    }

    /* ── Render pages list with WP title lookup ─────────── */
    private function render_pages_list( $pages ) {
        $total = array_sum( array_column( $pages, 'count' ) );
        $max   = $pages[0]['count'] ?? 1;
        echo '<div class="gcwp-hbar-list">';
        foreach ( $pages as $page ) {
            $count = $page['count'];
            $path  = $page['path'] ?? '/';
            $bar   = $max > 0 ? round( $count / $max * 100 ) : 0;
            $pct   = $total > 0 ? round( $count / $total * 100 ) : 0;
            $url   = home_url( $path );

            $post_id = url_to_postid( $url );
            if ( ! $post_id ) $post_id = url_to_postid( rtrim( $url, '/' ) );
            $title = $post_id ? get_the_title( $post_id ) : '';

            if ( ! $title ) {
                if ( $path === '/' ) $title = 'Portada';
                elseif ( is_front_page() && get_option( 'page_on_front' ) === $post_id ) $title = 'Portada';
            }
            ?>
            <div class="gcwp-hbar-item">
                <div class="gcwp-hbar-meta">
                    <span class="gcwp-hbar-name">
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html( $title ?: $path ); ?>
                        </a>
                        <?php if ( $title && $title !== $path ) : ?>
                        <small><?php echo esc_html( $path ); ?></small>
                        <?php endif; ?>
                    </span>
                    <span class="gcwp-hbar-right">
                        <span class="gcwp-hbar-count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
                        <span class="gcwp-hbar-pct"><?php echo esc_html( $pct ); ?>%</span>
                    </span>
                </div>
                <div class="gcwp-hbar-track">
                    <div class="gcwp-hbar-fill" style="width:<?php echo esc_attr( $bar ); ?>%"></div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /* ── Render horizontal bar list ─────────────────────── */
    private function render_hbar_list( $list ) {
        if ( empty( $list ) ) {
            echo '<p class="gcwp-empty">Sense dades per al període seleccionat.</p>';
            return;
        }
        $total = array_sum( array_column( $list, 'count' ) );
        $max   = $list[0]['count'] ?? 1;
        echo '<div class="gcwp-hbar-list">';
        foreach ( $list as $item ) {
            $count = $item['count'];
            $name  = $item['name'] ?? '—';
            $bar   = $max > 0 ? round( $count / $max * 100 ) : 0;
            $pct   = $total > 0 ? round( $count / $total * 100 ) : 0;
            ?>
            <div class="gcwp-hbar-item">
                <div class="gcwp-hbar-meta">
                    <span class="gcwp-hbar-name"><?php echo esc_html( $name ); ?></span>
                    <span class="gcwp-hbar-right">
                        <span class="gcwp-hbar-count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
                        <span class="gcwp-hbar-pct"><?php echo esc_html( $pct ); ?>%</span>
                    </span>
                </div>
                <div class="gcwp-hbar-track">
                    <div class="gcwp-hbar-fill" style="width:<?php echo esc_attr( $bar ); ?>%"></div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    /* ── Chart data preparation ─────────────────────────── */
    private function prepare_chart_data( $daily_stats, $period ) {
        $data     = [];
        $cat_days = [ 'Mon'=>'Dl','Tue'=>'Dt','Wed'=>'Dc','Thu'=>'Dj','Fri'=>'Dv','Sat'=>'Ds','Sun'=>'Dg' ];

        if ( $period === 'day' ) {
            $today = gmdate( 'Y-m-d' );
            $entry = null;
            foreach ( $daily_stats as $s ) {
                if ( isset( $s['day'] ) && $s['day'] === $today ) { $entry = $s; break; }
            }
            if ( ! $entry ) $entry = end( $daily_stats );
            $hourly = $entry['hourly'] ?? array_fill( 0, 24, 0 );
            foreach ( $hourly as $h => $count ) {
                $data[ sprintf( '%02dh', $h ) ] = (int) $count;
            }
        } elseif ( $period === 'quarter' ) {
            $weeks = [];
            foreach ( $daily_stats as $s ) {
                if ( ! isset( $s['day'] ) ) continue;
                $ts  = strtotime( $s['day'] );
                $dow = (int) gmdate( 'N', $ts );
                $mon = $ts - ( $dow - 1 ) * 86400;
                $key = gmdate( 'Y-m-d', $mon );
                $weeks[ $key ] = ( $weeks[ $key ] ?? 0 ) + ( $s['daily'] ?? 0 );
            }
            ksort( $weeks );
            foreach ( $weeks as $date => $count ) {
                $data[ gmdate( 'd/m', strtotime( $date ) ) ] = $count;
            }
        } elseif ( $period === 'year' ) {
            $months = [];
            foreach ( $daily_stats as $s ) {
                if ( ! isset( $s['day'] ) ) continue;
                $key = gmdate( 'Y-m', strtotime( $s['day'] ) );
                $months[ $key ] = ( $months[ $key ] ?? 0 ) + ( $s['daily'] ?? 0 );
            }
            ksort( $months );
            foreach ( $months as $ym => $count ) {
                $data[ gmdate( 'M y', strtotime( $ym . '-01' ) ) ] = $count;
            }
        } else {
            foreach ( $daily_stats as $s ) {
                if ( ! isset( $s['day'] ) ) continue;
                $ts = strtotime( $s['day'] );
                if ( $ts > time() ) continue;
                $eng   = gmdate( 'D', $ts );
                $label = $period === 'week' ? ( $cat_days[ $eng ] ?? $eng ) : gmdate( 'd/m', $ts );
                $data[ $label ] = $s['daily'] ?? 0;
            }
        }

        return $data;
    }

    /* ── DOW distribution ───────────────────────────────── */
    private function dow_distribution( $daily_stats ) {
        $labels = [ 1=>'Dl', 2=>'Dt', 3=>'Dc', 4=>'Dj', 5=>'Dv', 6=>'Ds', 7=>'Dg' ];
        $counts = array_fill( 1, 7, 0 );
        foreach ( $daily_stats as $s ) {
            $daily = $s['daily'] ?? 0;
            if ( $daily > 0 && isset( $s['day'] ) ) {
                $dow = (int) gmdate( 'N', strtotime( $s['day'] ) );
                $counts[ $dow ] += $daily;
            }
        }
        $total = array_sum( $counts );
        if ( $total === 0 ) return null;
        $result = [];
        for ( $d = 1; $d <= 7; $d++ ) {
            $result[ $labels[ $d ] ] = [
                'count' => $counts[ $d ],
                'pct'   => round( $counts[ $d ] / $total * 100 ),
            ];
        }
        return $result;
    }

    /* ── SVG bar chart ──────────────────────────────────── */
    private function render_svg_chart( $data ) {
        if ( empty( $data ) ) return '<p class="gcwp-empty">Sense dades.</p>';

        $values = array_values( $data );
        $labels = array_keys( $data );
        $n      = count( $data );
        $max    = max( $values ) ?: 1;
        $max_v  = max( $values );

        $vb_w    = 1000;
        $chart_h = 160;
        $label_h = 26;
        $total_h = $chart_h + $label_h;

        $bar_total_w = $vb_w / $n;
        $gap         = max( 1, min( 5, $bar_total_w * 0.2 ) );
        $bar_w       = max( 2, $bar_total_w - $gap );
        $label_step  = max( 1, (int) ceil( $n / 16 ) );

        $svg = '<svg viewBox="0 0 ' . $vb_w . ' ' . $total_h . '" xmlns="http://www.w3.org/2000/svg" '
             . 'style="width:100%;height:auto;display:block;" aria-hidden="true">';

        // Gradient defs
        $svg .= '<defs>'
              . '<linearGradient id="gcwp-gmax" x1="0" y1="0" x2="0" y2="1">'
              . '<stop offset="0%" stop-color="#ef4444"/>'
              . '<stop offset="100%" stop-color="#D51F17"/>'
              . '</linearGradient>'
              . '<linearGradient id="gcwp-gnorm" x1="0" y1="0" x2="0" y2="1">'
              . '<stop offset="0%" stop-color="#fca5a5"/>'
              . '<stop offset="100%" stop-color="#fecaca"/>'
              . '</linearGradient>'
              . '</defs>';

        // Horizontal grid lines
        for ( $g = 1; $g <= 4; $g++ ) {
            $gy  = round( $chart_h - ( $g / 4 ) * $chart_h );
            $val = round( $max * $g / 4 );
            $svg .= '<line x1="0" y1="' . $gy . '" x2="' . $vb_w . '" y2="' . $gy
                  . '" stroke="#f3f4f6" stroke-width="1"/>';
            $svg .= '<text x="4" y="' . ( $gy - 3 ) . '" font-size="9" fill="#d1d5db">' . $val . '</text>';
        }

        // Bars
        for ( $i = 0; $i < $n; $i++ ) {
            $x      = round( $i * $bar_total_w );
            $v      = $values[ $i ];
            $bh     = $max > 0 ? round( ( $v / $max ) * $chart_h ) : 0;
            $by     = $chart_h - $bh;
            $cx     = round( $x + $bar_w / 2 );
            $is_max = ( $v === $max_v && $v > 0 );
            $rx     = min( 4, $bar_w / 3 );
            $fill   = $is_max ? 'url(#gcwp-gmax)' : ( $v > 0 ? 'url(#gcwp-gnorm)' : '#f9fafb' );

            if ( $bh > 0 ) {
                $svg .= '<rect x="' . $x . '" y="' . $by . '" width="' . $bar_w . '" height="' . $bh
                      . '" fill="' . $fill . '" rx="' . $rx . '"/>';
            }

            // Value label above max bar
            if ( $is_max ) {
                $ly  = max( 13, $by - 6 );
                $svg .= '<text x="' . $cx . '" y="' . $ly . '" text-anchor="middle" '
                      . 'font-size="12" font-weight="700" fill="#D51F17">' . $v . '</text>';
            }

            // X-axis label
            if ( $i % $label_step === 0 ) {
                $ly    = $total_h - 5;
                $label = htmlspecialchars( $labels[ $i ], ENT_QUOTES );
                $svg .= '<text x="' . $cx . '" y="' . $ly . '" text-anchor="middle" '
                      . 'font-size="10" fill="#9ca3af">' . $label . '</text>';
            }
        }

        // Baseline
        $svg .= '<line x1="0" y1="' . $chart_h . '" x2="' . $vb_w . '" y2="' . $chart_h
              . '" stroke="#e5e7eb" stroke-width="1"/>';
        $svg .= '</svg>';

        return $svg;
    }

    /* ── Allowed tags/attrs for wp_kses() on generated SVG ── */
    private function svg_allowed_html() {
        return [
            'svg'            => [ 'viewbox' => true, 'xmlns' => true, 'style' => true, 'aria-hidden' => true, 'width' => true, 'height' => true ],
            'defs'           => [],
            'lineargradient' => [ 'id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ],
            'stop'           => [ 'offset' => true, 'stop-color' => true ],
            'line'           => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true ],
            'text'           => [ 'x' => true, 'y' => true, 'text-anchor' => true, 'font-size' => true, 'font-weight' => true, 'fill' => true ],
            'rect'           => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'fill' => true, 'rx' => true ],
        ];
    }

    private function chart_title( $period ) {
        return [
            'day'     => 'Visites per hora (avui)',
            'week'    => 'Visites per dia (darrers 7 dies)',
            'month'   => 'Visites per dia (darrers 30 dies)',
            'quarter' => 'Visites per setmana (darrer trimestre)',
            'year'    => 'Visites per mes (darrer any)',
        ][ $period ] ?? '';
    }
}
