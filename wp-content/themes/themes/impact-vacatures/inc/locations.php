<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode [fondsen_vacature_kaart]
 * Toont een kaart met alle open vacaturelocaties.
 *
 * Optionele attributen:
 *   height="500"  — hoogte van de kaart in pixels (standaard 500)
 */
add_shortcode( 'fondsen_vacature_kaart', function ( $atts ) {

    $atts   = shortcode_atts( [ 'height' => '500' ], $atts );
    $height = max( 200, (int) $atts['height'] );

    wp_enqueue_style(
        'maplibre-gl',
        'https://unpkg.com/maplibre-gl@4/dist/maplibre-gl.css',
        [],
        '4'
    );
    wp_enqueue_script(
        'maplibre-gl',
        'https://unpkg.com/maplibre-gl@4/dist/maplibre-gl.js',
        [],
        '4',
        true
    );

    $job_ids = get_posts( [
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    $markers = [];

    foreach ( $job_ids as $job_id ) {
        $location = get_post_meta( $job_id, '_job_location', true );
        if ( ! $location ) continue;

        $lat = get_post_meta( $job_id, '_job_location_lat', true );
        $lng = get_post_meta( $job_id, '_job_location_lng', true );

        $company_terms = get_the_terms( $job_id, 'job_company' );
        $company       = ( ! is_wp_error( $company_terms ) && ! empty( $company_terms ) )
                         ? $company_terms[0]->name
                         : '';

        $markers[] = [
            'id'       => $job_id,
            'title'    => get_the_title( $job_id ),
            'url'      => get_permalink( $job_id ),
            'company'  => $company,
            'location' => $location,
            'lat'      => ( $lat !== '' ) ? (float) $lat : null,
            'lng'      => ( $lng !== '' ) ? (float) $lng : null,
        ];
    }

    $map_id = 'fn-vk-' . wp_unique_id();

    ob_start();
    ?>
    <div class="fn-vacature-kaart-wrap">
        <div id="<?php echo esc_attr( $map_id ); ?>"
             class="fn-vacature-kaart"
             style="height:<?php echo $height; ?>px;">
        </div>
        <p class="fn-vacature-kaart__attr">
            Kaart: <a href="https://openfreemap.org" target="_blank" rel="noopener">OpenFreeMap</a>
            &amp; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>
        </p>
    </div>

    <style>
    .fn-vacature-kaart-wrap { margin: 0; }

    .fn-vacature-kaart {
        width: 100%;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #E5E7EB;
    }

    .fn-vacature-kaart__attr {
        font-size: 11px;
        color: #9CA3AF;
        margin: 4px 0 0;
        text-align: right;
    }

    .fn-vacature-kaart__attr a {
        color: #9CA3AF;
        text-decoration: underline;
    }

    .fn-map-popup {
        font-family: Poppins, system-ui, sans-serif;
        min-width: 200px;
    }

    .fn-map-popup__company {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #0884CC;
        margin-bottom: 4px;
    }

    .fn-map-popup__title {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        text-decoration: none;
        line-height: 1.3;
        margin-bottom: 6px;
    }

    .fn-map-popup__title:hover { color: #0884CC; }

    .fn-map-popup__location {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 10px;
    }

    .fn-map-popup__link {
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
        color: #0884CC;
        text-decoration: none;
        border: 1px solid #0884CC;
        border-radius: 4px;
        padding: 4px 10px;
    }

    .fn-map-popup__link:hover { background: rgba(8,132,204,.08); }

    .maplibregl-popup-content { padding: 14px 16px; }
    </style>

    <script>
    (function () {
        var MAP_ID  = <?php echo wp_json_encode( $map_id ); ?>;
        var JOBS    = <?php echo wp_json_encode( $markers ); ?>;

        function esc(str) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
        }

        function popupHtml(m) {
            var h = '<div class="fn-map-popup">';
            if (m.company) h += '<div class="fn-map-popup__company">' + esc(m.company) + '</div>';
            h += '<a class="fn-map-popup__title" href="' + esc(m.url) + '">' + esc(m.title) + '</a>';
            h += '<div class="fn-map-popup__location">📍 ' + esc(m.location) + '</div>';
            h += '<a class="fn-map-popup__link" href="' + esc(m.url) + '">Bekijk vacature →</a>';
            h += '</div>';
            return h;
        }

        function placeMarker(map, bounds, m) {
            var lngLat = [m.lng, m.lat];
            new maplibregl.Marker({ color: '#0884CC' })
                .setLngLat(lngLat)
                .setPopup(
                    new maplibregl.Popup({ offset: 25, maxWidth: '280px' })
                        .setHTML(popupHtml(m))
                )
                .addTo(map);
            bounds.extend(lngLat);
        }

        function geocodeOne(map, bounds, markerData, done) {
            var q = encodeURIComponent(markerData.location + ', Nederland');
            fetch(
                'https://nominatim.openstreetmap.org/search?q=' + q + '&format=json&limit=1',
                { headers: { 'Accept': 'application/json' } }
            )
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data[0]) {
                    markerData.lat = parseFloat(data[0].lat);
                    markerData.lng = parseFloat(data[0].lon);
                    placeMarker(map, bounds, markerData);
                    if (!bounds.isEmpty()) {
                        map.fitBounds(bounds, { padding: 60, maxZoom: 13 });
                    }
                }
            })
            .catch(function () {})
            .then(done);
        }

        function drainQueue(map, bounds, queue, i) {
            if (i >= queue.length) return;
            geocodeOne(map, bounds, queue[i], function () {
                setTimeout(function () {
                    drainQueue(map, bounds, queue, i + 1);
                }, 1150);
            });
        }

        function init() {
            if (typeof maplibregl === 'undefined') {
                setTimeout(init, 100);
                return;
            }

            var map = new maplibregl.Map({
                container: MAP_ID,
                style: 'https://tiles.openfreemap.org/styles/liberty',
                center: [5.3, 52.3],
                zoom: 6.5,
                attributionControl: false,
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-right');

            var bounds   = new maplibregl.LngLatBounds();
            var toGeocode = [];

            JOBS.forEach(function (m) {
                if (m.lat !== null && m.lng !== null) {
                    placeMarker(map, bounds, m);
                } else {
                    toGeocode.push(m);
                }
            });

            if (!bounds.isEmpty()) {
                map.fitBounds(bounds, { padding: 60, maxZoom: 13, animate: false });
            }

            if (toGeocode.length) {
                map.on('load', function () {
                    drainQueue(map, bounds, toGeocode, 0);
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
    <?php

    return ob_get_clean();
} );
