(function ($) {
    'use strict';

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
            wp.codeEditor.initialize($field, {
                codemirror: {
                    mode,
                    lineNumbers: true,
                },
            });

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

            wp.editor.initialize(id, {
                tinymce: true,
                quicktags: true,
                mediaButtons: $field.data('media-buttons') === 1 || $field.data('media-buttons') === '1',
            });

            const editorWrap = document.getElementById('wp-' + id + '-wrap');
            if (editorWrap) {
                editorWrap.classList.add('tmce-active');
            }

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

            const $latHidden = $wrapper.find('.wp-field-map-lat');
            const $lngHidden = $wrapper.find('.wp-field-map-lng');
            const $latInput = $wrapper.find('.wp-field-map-lat-input');
            const $lngInput = $wrapper.find('.wp-field-map-lng-input');

            const syncToHidden = function () {
                $latHidden.val(String($latInput.val() || '').trim());
                $lngHidden.val(String($lngInput.val() || '').trim());
            };

            syncToHidden();

            $latInput.on('input change', syncToHidden);
            $lngInput.on('input change', syncToHidden);

            $wrapper.find('.wp-field-map-geolocate').on('click', function (event) {
                event.preventDefault();

                if (!navigator.geolocation) {
                    return;
                }

                navigator.geolocation.getCurrentPosition(function (position) {
                    const lat = String(position.coords.latitude);
                    const lng = String(position.coords.longitude);
                    $latInput.val(lat);
                    $lngInput.val(lng);
                    syncToHidden();
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
