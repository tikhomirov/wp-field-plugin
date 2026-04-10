(function ($) {
    'use strict';

    if (window.WPFieldIntegrationsInitialized) {
        return;
    }
    window.WPFieldIntegrationsInitialized = true;

    const state = {
        mediaFrames: {},
    };

    function ensureMediaFrame(key, options) {
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            return null;
        }

        if (!state.mediaFrames[key]) {
            state.mediaFrames[key] = wp.media(options);
        }

        return state.mediaFrames[key];
    }

    function initColorPicker() {
        if (!$.fn || typeof $.fn.wpColorPicker !== 'function') {
            return;
        }

        $('.wp-color-picker-field').each(function () {
            const $field = $(this);
            if ($field.data('wpFieldColorInit') === true) {
                return;
            }

            $field.wpColorPicker({
                defaultColor: $field.data('default-color') || false,
                palettes: true,
            });

            $field.data('wpFieldColorInit', true);
        });
    }

    function initCodeEditor() {
        if (typeof wp === 'undefined' || !wp.codeEditor || typeof wp.codeEditor.initialize !== 'function') {
            return;
        }

        $('.wp-field-code-editor').each(function () {
            const $field = $(this);
            if ($field.data('wpFieldCodeEditorInit') === true) {
                return;
            }

            const mode = String($field.data('mode') || 'text/html');
            wp.codeEditor.initialize($field[0], {
                type: mode,
                codemirror: {
                    mode: mode,
                    lineNumbers: true,
                },
            });

            $field.hide();
            $field.data('wpFieldCodeEditorInit', true);
        });
    }

    function initWpEditor() {
        if (typeof wp === 'undefined' || !wp.editor || typeof wp.editor.initialize !== 'function') {
            return;
        }

        $('.wp-editor-area').each(function () {
            const $field = $(this);
            if ($field.data('wpFieldEditorInit') === true) {
                return;
            }

            const id = $field.attr('id');
            if (!id) {
                return;
            }

            if (document.getElementById('wp-' + id + '-wrap')) {
                $field.data('wpFieldEditorInit', true);
                return;
            }

            const settings = {
                tinymce: true,
                quicktags: true,
                mediaButtons: $field.data('media-buttons') === 1 || $field.data('media-buttons') === '1',
            };

            wp.editor.initialize(id, settings);
            $field.data('wpFieldEditorInit', true);
        });
    }

    function setImagePreview($wrapper, url) {
        let $preview = $wrapper.find('.wp-field-image-preview-wrapper');
        if (!$preview.length) {
            $preview = $('<div class="wp-field-image-preview-wrapper"></div>').appendTo($wrapper);
        }

        if (url) {
            $preview.html('<img src="' + url + '" alt="" class="wp-field-image-preview">');
        } else {
            $preview.empty();
        }
    }

    function bindMediaButtons() {
        if ($(document).data('wpFieldMediaButtonsBound') === true) {
            return;
        }
        $(document).data('wpFieldMediaButtonsBound', true);

        $(document).on('click', '.wp-field-image-button', function (event) {
            event.preventDefault();
            const $button = $(this);
            const $wrapper = $button.closest('.wp-field-image-wrapper');
            const $hidden = $wrapper.find('.wp-field-image-id');
            const $url = $wrapper.find('.wp-field-image-url');
            const fieldId = String($button.data('field-id') || 'image');

            const frame = ensureMediaFrame('image-' + fieldId, {
                title: 'Выберите изображение',
                button: { text: 'Выбрать изображение' },
                multiple: false,
                library: { type: 'image' },
            });

            if (!frame) {
                return;
            }

            frame.off('select').on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                $hidden.val(String(attachment.id || ''));
                $url.val(String(attachment.url || ''));
                setImagePreview($wrapper, String(attachment.url || ''));
            });

            frame.open();
        });

        $(document).on('click', '.wp-field-file-button, .wp-field-media-button', function (event) {
            event.preventDefault();
            const $button = $(this);
            const isMedia = $button.hasClass('wp-field-media-button');
            const wrapperClass = isMedia ? '.wp-field-media-wrapper' : '.wp-field-file-wrapper';
            const hiddenClass = isMedia ? '.wp-field-media-id' : '.wp-field-file-id';
            const urlClass = isMedia ? '.wp-field-media-url' : '.wp-field-file-url';
            const frameKeyPrefix = isMedia ? 'media' : 'file';
            const fieldId = String($button.data('field-id') || frameKeyPrefix);
            const library = String($button.data('library') || '');

            const $wrapper = $button.closest(wrapperClass);
            const $hidden = $wrapper.find(hiddenClass);
            const $url = $wrapper.find(urlClass);

            const frame = ensureMediaFrame(frameKeyPrefix + '-' + fieldId, {
                title: isMedia ? 'Выберите медиа' : 'Выберите файл',
                button: { text: isMedia ? 'Выбрать медиа' : 'Выбрать файл' },
                multiple: false,
                library: library ? { type: library } : {},
            });

            if (!frame) {
                return;
            }

            frame.off('select').on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                const value = String(attachment.id || attachment.url || '');
                const url = String(attachment.url || '');
                $hidden.val(value);
                $url.val(url);
            });

            frame.open();
        });

        $(document).on('click', '.wp-field-gallery-add, .wp-field-gallery-edit', function (event) {
            event.preventDefault();
            const $button = $(this);
            const fieldId = String($button.data('field-id') || 'gallery');
            const $wrapper = $button.closest('.wp-field-gallery-wrapper');
            const $hidden = $wrapper.find('.wp-field-gallery-ids');

            const frame = ensureMediaFrame('gallery-' + fieldId, {
                title: 'Выберите галерею',
                button: { text: 'Выбрать изображения' },
                multiple: true,
                library: { type: 'image' },
            });

            if (!frame) {
                return;
            }

            frame.off('select').on('select', function () {
                const selection = frame.state().get('selection');
                const ids = selection.map(function (attachment) {
                    return String(attachment.id);
                });
                $hidden.val(ids.join(','));
            });

            frame.open();
        });

        $(document).on('click', '.wp-field-gallery-clear', function (event) {
            event.preventDefault();
            const $wrapper = $(this).closest('.wp-field-gallery-wrapper');
            $wrapper.find('.wp-field-gallery-ids').val('');
            $wrapper.find('.wp-field-gallery-preview').empty();
        });

        $(document).on('click', '.wp-field-background-image-button', function (event) {
            event.preventDefault();
            const fieldName = String($(this).data('field-name') || '');
            if (!fieldName) {
                return;
            }

            const frame = ensureMediaFrame('background-' + fieldName, {
                title: 'Выберите фоновое изображение',
                button: { text: 'Выбрать изображение' },
                multiple: false,
                library: { type: 'image' },
            });

            if (!frame) {
                return;
            }

            frame.off('select').on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                $('input[name="' + fieldName + '[image]"]').val(String(attachment.id || ''));
            });

            frame.open();
        });
    }

    function bindIconPickerFallback() {
        if ($(document).data('wpFieldIconPickerBound') === true) {
            return;
        }
        $(document).data('wpFieldIconPickerBound', true);

        $(document).on('click', '.wp-field-icon-button', function (event) {
            event.preventDefault();
            const $button = $(this);
            const fieldId = String($button.data('field-id') || '');
            if (!fieldId) {
                return;
            }

            const $input = $('#' + fieldId);
            if (!$input.length) {
                return;
            }

            const $picker = $button.closest('.wp-field-icon-picker');
            const $modal = $picker.find('.wp-field-icon-modal');
            if (!$modal.length) {
                const current = String($input.val() || '');
                const next = window.prompt('Введите CSS-класс иконки', current);
                if (next === null) {
                    return;
                }

                $input.val(next).trigger('change');
                $picker.find('.wp-field-icon-preview').attr('class', 'wp-field-icon-preview ' + next.trim());
                return;
            }

            const positionModal = function () {
                const pickerRect = $picker[0].getBoundingClientRect();
                const modalWidth = Math.min(500, Math.max(320, pickerRect.width));
                const viewportPadding = 16;
                const left = Math.max(viewportPadding, -Math.min(0, pickerRect.left));
                const maxHeight = Math.max(220, window.innerHeight - pickerRect.bottom - 32);

                $modal.css({
                    top: (pickerRect.height + 8) + 'px',
                    left: left + 'px',
                    width: modalWidth + 'px',
                    maxHeight: maxHeight + 'px',
                });
            };

            positionModal();
            $modal.show();

            const current = String($input.val() || '');
            $modal.find('.wp-field-icon-search').val('');
            $modal.find('.wp-field-icon-grid span').show();
            if (current) {
                const $current = $modal.find('.wp-field-icon-grid span[data-icon="' + current + '"]');
                if ($current.length) {
                    $current.first().scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
                const iconClass = current.split(' ')[0] || '';
                $button.html('<span class="' + iconClass + '"></span> ' + current);
            }
        });
    }

    function initMapFallback() {
        $('.wp-field-map-wrapper').each(function () {
            const $wrapper = $(this);
            if ($wrapper.data('wpFieldMapInit') === true) {
                return;
            }

            const $mapEl = $wrapper.find('.wp-field-map').first();
            const $latHidden = $wrapper.find('.wp-field-map-lat');
            const $lngHidden = $wrapper.find('.wp-field-map-lng');
            const $latInput = $wrapper.find('.wp-field-map-lat-input');
            const $lngInput = $wrapper.find('.wp-field-map-lng-input');
            const provider = String($wrapper.data('map-provider') || 'google').toLowerCase();
            const zoom = parseInt($mapEl.data('zoom'), 10) || 12;

            const getLatLng = function () {
                const lat = parseFloat(String($latInput.val() || $latHidden.val() || $mapEl.data('center-lat') || '55.7558'));
                const lng = parseFloat(String($lngInput.val() || $lngHidden.val() || $mapEl.data('center-lng') || '37.6173'));

                return {
                    lat: Number.isFinite(lat) ? lat : 55.7558,
                    lng: Number.isFinite(lng) ? lng : 37.6173,
                };
            };

            const syncValues = function (lat, lng) {
                const latValue = String(lat);
                const lngValue = String(lng);
                $latInput.val(latValue);
                $lngInput.val(lngValue);
                $latHidden.val(latValue);
                $lngHidden.val(lngValue);
            };

            const initial = getLatLng();
            syncValues(initial.lat, initial.lng);

            if ($mapEl.length && (provider === 'leaflet' || provider === 'osm') && typeof window.L !== 'undefined' && !$mapEl.data('leafletInit')) {
                const map = window.L.map($mapEl[0]).setView([initial.lat, initial.lng], zoom);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);

                const marker = window.L.marker([initial.lat, initial.lng], { draggable: true }).addTo(map);

                marker.on('dragend', function (event) {
                    const position = event.target.getLatLng();
                    syncValues(position.lat, position.lng);
                });

                map.on('click', function (event) {
                    marker.setLatLng(event.latlng);
                    syncValues(event.latlng.lat, event.latlng.lng);
                });

                const syncMarkerFromInputs = function () {
                    const next = getLatLng();
                    marker.setLatLng([next.lat, next.lng]);
                    map.panTo([next.lat, next.lng]);
                };

                $latInput.on('change', syncMarkerFromInputs);
                $lngInput.on('change', syncMarkerFromInputs);

                $wrapper.find('.wp-field-map-geolocate').on('click', function (event) {
                    event.preventDefault();

                    if (!navigator.geolocation) {
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(function (position) {
                        syncValues(position.coords.latitude, position.coords.longitude);
                        syncMarkerFromInputs();
                    });
                });

                setTimeout(function () {
                    map.invalidateSize();
                }, 0);

                $mapEl.data('leafletInit', true);
                $wrapper.data('wpFieldMapInit', true);
                return;
            }

            if ($mapEl.length && provider === 'google' && typeof window.google !== 'undefined' && window.google.maps && !$mapEl.data('googleInit')) {
                const map = new window.google.maps.Map($mapEl[0], {
                    center: { lat: initial.lat, lng: initial.lng },
                    zoom: zoom,
                });

                const marker = new window.google.maps.Marker({
                    position: { lat: initial.lat, lng: initial.lng },
                    map: map,
                    draggable: true,
                });

                window.google.maps.event.addListener(marker, 'dragend', function () {
                    const position = marker.getPosition();
                    syncValues(position.lat(), position.lng());
                });

                window.google.maps.event.addListener(map, 'click', function (event) {
                    marker.setPosition(event.latLng);
                    syncValues(event.latLng.lat(), event.latLng.lng());
                });

                const syncGoogleFromInputs = function () {
                    const next = getLatLng();
                    const position = { lat: next.lat, lng: next.lng };
                    marker.setPosition(position);
                    map.panTo(position);
                };

                $latInput.on('change', syncGoogleFromInputs);
                $lngInput.on('change', syncGoogleFromInputs);

                $wrapper.find('.wp-field-map-geolocate').on('click', function (event) {
                    event.preventDefault();

                    if (!navigator.geolocation) {
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(function (position) {
                        syncValues(position.coords.latitude, position.coords.longitude);
                        syncGoogleFromInputs();
                    });
                });

                $mapEl.data('googleInit', true);
                $wrapper.data('wpFieldMapInit', true);
                return;
            }

            const syncToHidden = function () {
                const next = getLatLng();
                syncValues(next.lat, next.lng);
            };

            $latInput.on('input change', syncToHidden);
            $lngInput.on('input change', syncToHidden);

            $wrapper.find('.wp-field-map-geolocate').on('click', function (event) {
                event.preventDefault();

                if (!navigator.geolocation) {
                    return;
                }

                navigator.geolocation.getCurrentPosition(function (position) {
                    syncValues(position.coords.latitude, position.coords.longitude);
                });
            });

            $wrapper.data('wpFieldMapInit', true);
        });
    }

    function initAll() {
        initColorPicker();
        initCodeEditor();
        initWpEditor();
        initMapFallback();
    }

    $(document).ready(function () {
        bindMediaButtons();
        bindIconPickerFallback();
        initAll();
        $(document).on('wp-field:refresh', initAll);
    });
}(jQuery));
