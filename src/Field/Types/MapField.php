<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class MapField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'map');
    }

    public function zoom(int $zoom): static
    {
        return $this->attribute('zoom', $zoom);
    }

    /**
     * @param  array{lat?: float|int|string, lng?: float|int|string}  $center
     */
    public function center(array $center): static
    {
        return $this->attribute('center', $center);
    }

    public function apiKey(string $apiKey): static
    {
        return $this->attribute('api_key', $apiKey);
    }

    public function provider(string $provider): static
    {
        return $this->attribute('provider', $provider);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $center = $this->normalizeCenter($this->getAttribute('center', []));
        $zoomAttr = $this->getAttribute('zoom', 12);
        $zoom = max(1, is_numeric($zoomAttr) ? (int) $zoomAttr : 12);
        $provider = strtolower($this->attributeString('provider', 'google'));
        $apiKey = $this->attributeString('api_key');

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s-lat">%s</label>', $id, esc_html($label));
        }

        $html .= sprintf(
            '<div class="wp-field-map-wrapper" data-map-provider="%s" data-api-key="%s">',
            esc_attr($provider),
            esc_attr($apiKey),
        );

        $html .= sprintf('<input type="hidden" name="%s[lat]" value="%s" class="wp-field-map-lat">', $name, esc_attr($value['lat']));
        $html .= sprintf('<input type="hidden" name="%s[lng]" value="%s" class="wp-field-map-lng">', $name, esc_attr($value['lng']));

        $html .= '<div class="wp-field-map-coordinates">';
        $html .= sprintf('<input type="text" id="%s-lat" value="%s" class="regular-text wp-field-map-lat-input" placeholder="Latitude">', $id, esc_attr($value['lat']));
        $html .= sprintf('<input type="text" id="%s-lng" value="%s" class="regular-text wp-field-map-lng-input" placeholder="Longitude">', $id, esc_attr($value['lng']));
        $html .= sprintf('<button type="button" class="button wp-field-map-geolocate">%s</button>', esc_html__('Use current location', 'wp-field'));
        $html .= '</div>';

        $html .= sprintf(
            '<div class="wp-field-map" data-zoom="%d" data-center-lat="%s" data-center-lng="%s" style="height:400px;width:100%%;"></div>',
            $zoom,
            esc_attr($center['lat']),
            esc_attr($center['lng']),
        );

        if ($provider === 'google' && $apiKey === '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html__('Google Maps API key required', 'wp-field'));
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $normalized = $this->normalizeValue($value);

        return [
            'lat' => $this->sanitizeCoordinate($normalized['lat'], -90, 90),
            'lng' => $this->sanitizeCoordinate($normalized['lng'], -180, 180),
        ];
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);

        return $this->isValidCoordinateOrEmpty($normalized['lat'], -90, 90)
            && $this->isValidCoordinateOrEmpty($normalized['lng'], -180, 180);
    }

    /**
     * @return array{lat: string, lng: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return ['lat' => '', 'lng' => ''];
        }

        return [
            'lat' => isset($value['lat']) && is_scalar($value['lat']) ? (string) $value['lat'] : '',
            'lng' => isset($value['lng']) && is_scalar($value['lng']) ? (string) $value['lng'] : '',
        ];
    }

    /**
     * @return array{lat: string, lng: string}
     */
    private function normalizeCenter(mixed $center): array
    {
        if (! is_array($center)) {
            return ['lat' => '55.7558', 'lng' => '37.6173'];
        }

        $lat = isset($center['lat']) && is_scalar($center['lat']) ? (string) $center['lat'] : '55.7558';
        $lng = isset($center['lng']) && is_scalar($center['lng']) ? (string) $center['lng'] : '37.6173';

        return [
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    private function sanitizeCoordinate(string $value, float $min, float $max): string
    {
        $value = trim(sanitize_text_field($value));
        if ($value === '' || ! is_numeric($value)) {
            return '';
        }

        $number = (float) $value;
        if ($number < $min || $number > $max) {
            return '';
        }

        return (string) $number;
    }

    private function isValidCoordinateOrEmpty(string $value, float $min, float $max): bool
    {
        if ($value === '') {
            return true;
        }

        if (! is_numeric($value)) {
            return false;
        }

        $number = (float) $value;

        return $number >= $min && $number <= $max;
    }
}
