<?php
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Chillypills_OSM_Map_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'chillypills_osm_map_widget';
    }

    public function get_title() {
        return 'Chillypills Open Street Map Widget';
    }

    public function get_icon() {
        return 'https://chillypills.com/dist/app/images/favicon/favicon-196x196.png';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Pestaña de Ubicaciones
        $this->start_controls_section(
            'locations_section',
            [
                'label' => __('Ubicaciones', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'address',
            [
                'label' => __('Dirección', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Introduce una dirección', 'elementor'),
            ]
        );

        $repeater->add_control(
            'manual_coordinates',
            [
                'label' => __('Introducir coordenadas manualmente', 'elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'elementor'),
                'label_off' => __('No', 'elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $repeater->add_control(
            'latitude',
            [
                'label' => __('Latitud', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition' => [
                    'manual_coordinates' => 'yes',
                ],
                'default' => '',
                'placeholder' => __('Introduce la latitud', 'elementor'),
            ]
        );

        $repeater->add_control(
            'longitude',
            [
                'label' => __('Longitud', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition' => [
                    'manual_coordinates' => 'yes',
                ],
                'default' => '',
                'placeholder' => __('Introduce la longitud', 'elementor'),
            ]
        );

        $this->add_control(
            'locations',
            [
                'label' => __('Ubicaciones', 'elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ address }}}',
            ]
        );

        $this->end_controls_section();

        // Pestaña de Estilo de Mapa
        $this->start_controls_section(
            'map_style_section',
            [
                'label' => __('Estilo del Mapa', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'map_style',
            [
                'label' => __('Estilo de Mapbox', 'elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'streets-v11' => 'Streets',
                    'outdoors-v11' => 'Outdoors',
                    'light-v10' => 'Light',
                    'dark-v10' => 'Dark',
                    'satellite-v9' => 'Satellite',
                    'satellite-streets-v11' => 'Satellite Streets',
                ],
                'default' => 'streets-v11',
            ]
        );

        $this->end_controls_section();

        // Pestaña de Token Mapbox
        $this->start_controls_section(
            'mapbox_token_section',
            [
                'label' => __('Token Mapbox', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'mapbox_token',
            [
                'label' => __('Token de Mapbox', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'pk.eyJ1IjoiYWx2YXJvcHVjaGUiLCJhIjoiY2xjcTlqa3ZtMDFnNzNwbnB5ejR6NzQ4bCJ9.j2hplYkteh3PWHeDXxbs_Q',
                'description' => __('Introduce tu token de Mapbox.', 'elementor'),
            ]
        );

        $this->add_control(
            'mapbox_token_status',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div id="mapbox-token-status"></div>',
                'content_classes' => 'elementor-control-field',
            ]
        );

        $this->add_control(
            'disclaimer',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p>Este plugin es propiedad única y exclusiva de Chillypills Comunicación S.L., y su uso está restringido a proyectos aprobados por Chillypills.</p>',
                'content_classes' => 'elementor-control-field',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $map_style = $settings['map_style'];
        $locations = $settings['locations'];
        $mapbox_token = $settings['mapbox_token'];
        ?>
        <div id="osm-map" style="width: 100%; height: 500px;"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var map = L.map('osm-map').setView([51.505, -0.09], 13);

                L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/<?php echo esc_js($map_style); ?>/tiles/{z}/{x}/{y}?access_token=<?php echo esc_js($mapbox_token); ?>', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a>',
                    tileSize: 512,
                    zoomOffset: -1,
                }).addTo(map);

                var geocoder = L.Control.Geocoder.nominatim();

                <?php foreach ($locations as $location): ?>
                    <?php if ($location['manual_coordinates'] === 'yes'): ?>
                        var marker = L.marker([<?php echo esc_js($location['latitude']); ?>, <?php echo esc_js($location['longitude']); ?>]).addTo(map)
                            .bindPopup('<?php echo esc_js($location['address']); ?>')
                            .openPopup();
                    <?php else: ?>
                        geocoder.geocode('<?php echo esc_js($location['address']); ?>', function(results) {
                            if (results.length) {
                                var marker = L.marker(results[0].center).addTo(map)
                                    .bindPopup('<?php echo esc_js($location['address']); ?>')
                                    .openPopup();
                            }
                        });
                    <?php endif; ?>
                <?php endforeach; ?>
            });
        </script>
        <?php
    }

    protected function _content_template() {
        ?>
        <#
        var map_style = settings.map_style;
        var locations = settings.locations;
        var mapbox_token = settings.mapbox_token;
        #>
        <div id="osm-map" style="width: 100%; height: 500px;"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var map = L.map('osm-map').setView([51.505, -0.09], 13);

                L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/{{{ map_style }}}/tiles/{z}/{x}/{y}?access_token={{{ mapbox_token }}}', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a>',
                    tileSize: 512,
                    zoomOffset: -1,
                }).addTo(map);

                var geocoder = L.Control.Geocoder.nominatim();

                _.each(locations, function(location) {
                    if (location.manual_coordinates === 'yes') {
                        var marker = L.marker([location.latitude, location.longitude]).addTo(map)
                            .bindPopup(location.address)
                            .openPopup();
                    } else {
                        geocoder.geocode(location.address, function(results) {
                            if (results.length) {
                                var marker = L.marker(results[0].center).addTo(map)
                                    .bindPopup(location.address)
                                    .openPopup();
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }
}
?>
