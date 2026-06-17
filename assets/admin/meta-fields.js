/**
 * Coachman standalone meta-fields — admin UI.
 *
 * Drives repeaters (add/remove rows, incl. nested), media pickers and
 * client-side TinyMCE init for rich-text fields. No build step; relies on
 * jQuery + wp.media + wp.editor enqueued by includes/meta-fields.php.
 */
(function ($) {
    'use strict';

    /* ---------------------------------------------------------------- */
    /* Rich text (TinyMCE) — works inside dynamically added rows.        */
    /* ---------------------------------------------------------------- */

    function initEditor(textarea) {
        if (!window.wp || !wp.editor || !wp.editor.initialize || !textarea.id) {
            return;
        }
        if (window.tinymce && tinymce.get(textarea.id)) {
            return; // already initialised
        }
        wp.editor.initialize(textarea.id, {
            tinymce: {
                wpautop: true,
                toolbar1: 'bold italic underline | bullist numlist | link unlink | removeformat | undo redo'
            },
            quicktags: true,
            mediaButtons: false
        });
    }

    function removeEditor(textarea) {
        if (window.wp && wp.editor && wp.editor.remove && textarea.id) {
            try {
                wp.editor.remove(textarea.id);
            } catch (e) {}
        }
    }

    function initRichText(scope) {
        $(scope).find('.cm-richtext').each(function () {
            initEditor(this);
        });
    }

    /* ---------------------------------------------------------------- */
    /* Repeater rows.                                                     */
    /* ---------------------------------------------------------------- */

    // A monotonically increasing index per repeater so removed rows never
    // free an index for reuse (which would collide on submit). PHP reindexes
    // with array_values() on save, so gaps are harmless.
    function nextIndex(repeater) {
        var n = repeater.getAttribute('data-next-index');
        if (n === null) {
            n = repeater.querySelectorAll(':scope > .cm-rows > .cm-row').length;
        }
        n = parseInt(n, 10) || 0;
        repeater.setAttribute('data-next-index', n + 1);
        return n;
    }

    $(document).on('click', '.cm-add-row', function (e) {
        e.preventDefault();

        var repeater = $(this).closest('.cm-repeater')[0];
        var rows = repeater.querySelector(':scope > .cm-rows');
        var proto = repeater.querySelector(':scope > template.cm-row-prototype');
        if (!rows || !proto) {
            return;
        }

        var token = repeater.getAttribute('data-token');
        var index = nextIndex(repeater);
        var html = proto.innerHTML.split('{{' + token + '}}').join(index);

        var temp = document.createElement('div');
        temp.innerHTML = html;
        var newRow = temp.firstElementChild;
        if (!newRow) {
            return;
        }
        rows.appendChild(newRow);
        initRichText(newRow);
    });

    $(document).on('click', '.cm-remove-row', function (e) {
        e.preventDefault();
        var row = $(this).closest('.cm-row')[0];
        if (!row) {
            return;
        }
        $(row).find('.cm-richtext').each(function () {
            removeEditor(this);
        });
        row.parentNode.removeChild(row);
    });

    /* ---------------------------------------------------------------- */
    /* Media picker (image / file).                                      */
    /* ---------------------------------------------------------------- */

    $(document).on('click', '.cm-media-select', function (e) {
        e.preventDefault();

        var $media = $(this).closest('.cm-media');
        var type = $media.data('type'); // 'image' | 'file'
        var mime = $media.data('mime');

        var library = {};
        if (type === 'image') {
            library.type = 'image';
        } else if (mime) {
            library.type = mime;
        }

        var frame = wp.media({
            title: 'Select',
            button: { text: 'Use this' },
            multiple: false,
            library: library
        });

        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $media.find('.cm-media-id').val(att.id);

            var preview;
            if (type === 'image') {
                var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                preview = $('<img>', { src: url, alt: '' });
            } else {
                preview = $('<span class="cm-file-name"></span>').text(att.title || att.filename || ('#' + att.id));
            }
            $media.find('.cm-media-preview').empty().append(preview);
            $media.find('.cm-media-remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.cm-media-remove', function (e) {
        e.preventDefault();
        var $media = $(this).closest('.cm-media');
        $media.find('.cm-media-id').val('');
        $media.find('.cm-media-preview').empty();
        $(this).hide();
    });

    /* ---------------------------------------------------------------- */
    /* Flush TinyMCE content back to its textarea before any submit.     */
    /* ---------------------------------------------------------------- */

    $(document).on('submit', 'form', function () {
        if (window.tinymce) {
            try {
                tinymce.triggerSave();
            } catch (e) {}
        }
    });

    // The term "Add new" form submits over AJAX and is reset on success — flush
    // before the request and re-init editors once WP clears the inputs.
    $(document).on('click', '#addtag #submit', function () {
        if (window.tinymce) {
            try {
                tinymce.triggerSave();
            } catch (e) {}
        }
    });

    /* ---------------------------------------------------------------- */
    /* Init.                                                             */
    /* ---------------------------------------------------------------- */

    $(function () {
        // Editors inside <template> prototypes are inert (separate fragment) and
        // are not matched here — only live rows get initialised.
        initRichText(document);
    });

})(jQuery);
