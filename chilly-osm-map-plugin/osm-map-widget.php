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
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'addresses',
            [
                'label' => __('Direcciones', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => get_option('osm_map_plugin_addresses', ''),
                'description' => __('Introduce cada dirección en una nueva línea.', 'elementor'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $addresses = explode("\n", $settings['addresses']);
        ?>
        <div id="osm-map" style="width: 100%; height: 500px;"></div>
        <script>
            var map = L.map('osm-map').setView([51.505, -0.09], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var geocoder = L.Control.Geocoder.nominatim();

            <?php foreach ($addresses as $address): ?>
                geocoder.geocode('<?php echo esc_js(trim($address)); ?>', function(results) {
                    if (results.length) {
                        var marker = L.marker(results[0].center).addTo(map)
                            .bindPopup('<?php echo esc_js(trim($address)); ?>')
                            .openPopup();
                    }
                });
            <?php endforeach; ?>
        </script>
        <?php
    }
}
?>
