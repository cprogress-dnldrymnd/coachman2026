/**
 * Coachman standalone meta-fields — admin UI.
 *
 * Drives repeaters (add/remove/reorder rows, incl. nested), media pickers and
 * client-side TinyMCE init for rich-text fields. No build step; relies on
 * jQuery + jQuery UI Sortable + wp.media + wp.editor enqueued by includes/meta-fields.php.
 */
(function ($) {
    'use strict';

    /* ---------------------------------------------------------------- */
    /* Rich text (TinyMCE) — works inside dynamically added rows.        */
    /* ---------------------------------------------------------------- */

    /**
     * Initializes a single TinyMCE editor instance on a given textarea.
     * * @param {HTMLTextAreaElement} textarea The target textarea element.
     */
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

    /**
     * Removes a TinyMCE editor instance from a given textarea.
     * * @param {HTMLTextAreaElement} textarea The target textarea element.
     */
    function removeEditor(textarea) {
        if (window.wp && wp.editor && wp.editor.remove && textarea.id) {
            try {
                wp.editor.remove(textarea.id);
            } catch (e) {}
        }
    }

    /**
     * Finds and initializes all rich-text editors within a specific DOM scope.
     * * @param {Document|HTMLElement} scope The DOM container to search within.
     */
    function initRichText(scope) {
        $(scope).find('.cm-richtext').each(function () {
            // Skip editors inside collapsed rows — TinyMCE mis-sizes hidden textareas.
            if ($(this).closest('.cm-row--collapsed').length) {
                return;
            }
            initEditor(this);
        });
    }

    /* ---------------------------------------------------------------- */
    /* Repeater rows.                                                     */
    /* ---------------------------------------------------------------- */

    /**
     * Generates a monotonically increasing index per repeater so removed rows 
     * never free an index for reuse (which would collide on submit). PHP reindexes
     * with array_values() on save, so gaps are harmless.
     * * @param {HTMLElement} repeater The repeater container element.
     * @return {number} The next available index.
     */
    function nextIndex(repeater) {
        var n = repeater.getAttribute('data-next-index');
        if (n === null) {
            n = repeater.querySelectorAll(':scope > .cm-rows > .cm-row').length;
        }
        n = parseInt(n, 10) || 0;
        repeater.setAttribute('data-next-index', n + 1);
        return n;
    }

    /**
     * Handles dynamic injection of new repeater rows using the <template> prototype.
     */
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
        setRowCollapsed(newRow, false);
        initRichText(newRow);
        initSortable(newRow);
    });

    /**
     * Handles the removal of an existing repeater row, stripping TinyMCE instances first.
     */
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

    /**
     * Collapse / expand a repeater row. Collapsed rows are one-line headers —
     * much easier to drag-sort when many stocks are present.
     *
     * @param {HTMLElement} row
     * @param {boolean} collapsed
     */
    function setRowCollapsed(row, collapsed) {
        var $row = $(row);
        var $toggle = $row.children('.cm-row-handle').find('.cm-row-toggle');
        $row.toggleClass('cm-row--collapsed', collapsed);
        $toggle.attr('aria-expanded', collapsed ? 'false' : 'true');
        if (collapsed) {
            $row.children('.cm-row-body').find('.cm-richtext').each(function () {
                removeEditor(this);
            });
        } else {
            initRichText($row.children('.cm-row-body')[0]);
        }
    }

    $(document).on('click', '.cm-row-toggle, .cm-row-title', function (e) {
        e.preventDefault();
        var row = $(this).closest('.cm-row')[0];
        if (!row) {
            return;
        }
        setRowCollapsed(row, !$(row).hasClass('cm-row--collapsed'));
    });

    /**
     * Keep the handle title in sync with the row's header field as the user types.
     */
    $(document).on('input change', '.cm-row-body input, .cm-row-body textarea, .cm-row-body select', function () {
        var $row = $(this).closest('.cm-row');
        var header = $row.attr('data-header');
        if (!header) {
            return;
        }
        var name = this.name || '';
        var suffix = '[' + header + ']';
        if (name.slice(-suffix.length) !== suffix) {
            return;
        }
        var text = (this.value || '').toString().trim();
        $row.children('.cm-row-handle').find('.cm-row-title').text(text || '(untitled)');
    });

    /**
     * Enables drag-and-drop reordering on every repeater's direct rows.
     * Nested repeaters get their own sortable instance. Form field names keep
     * their indices; PHP's array_values() on save preserves DOM / POST order.
     *
     * @param {Document|HTMLElement} scope Container to search within.
     */
    function initSortable(scope) {
        $(scope).find('.cm-rows').each(function () {
            var $rows = $(this);
            if ($rows.data('ui-sortable')) {
                return;
            }
            $rows.sortable({
                items: '> .cm-row',
                handle: '.cm-row-drag',
                axis: 'y',
                cursor: 'move',
                opacity: 0.85,
                placeholder: 'cm-row-placeholder',
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                start: function (e, ui) {
                    // TinyMCE breaks when its textarea is moved in the DOM.
                    ui.item.find('.cm-richtext').each(function () {
                        removeEditor(this);
                    });
                    ui.placeholder.height(Math.max(ui.item.outerHeight(), 36));
                },
                stop: function (e, ui) {
                    if (!ui.item.hasClass('cm-row--collapsed')) {
                        initRichText(ui.item[0]);
                    }
                }
            });
        });
    }

    /* ---------------------------------------------------------------- */
    /* Media picker (image / file).                                      */
    /* ---------------------------------------------------------------- */

    /**
     * Triggers the single-selection WP Media Frame.
     */
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

    /**
     * Clears the selected single media item.
     */
    $(document).on('click', '.cm-media-remove', function (e) {
        e.preventDefault();
        var $media = $(this).closest('.cm-media');
        $media.find('.cm-media-id').val('');
        $media.find('.cm-media-preview').empty();
        $(this).hide();
    });

    /* ---------------------------------------------------------------- */
    /* Gallery picker (multiple images).                                  */
    /* ---------------------------------------------------------------- */

    /**
     * Initializes and handles the native WordPress media frame for the custom gallery field.
     * Enables multi-selection, preview rendering, and ID extraction for storage.
     */
    $(document).on('click', '.cm-gallery-select', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $container = $btn.closest('.cm-gallery');
        var $input = $container.find('.cm-gallery-ids');
        var $preview = $container.find('.cm-gallery-preview');
        var currentIds = $input.val() ? $input.val().split(',') : [];

        var frame = wp.media({
            title: wp.media.view.l10n.addMedia || 'Select Images',
            button: { text: wp.media.view.l10n.insertIntoPost || 'Use these images' },
            multiple: true,
            library: { type: 'image' }
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var ids = [];
            $preview.empty();

            attachments.forEach(function(att) {
                ids.push(att.id);
                var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                var itemHtml = '<div class="cm-gallery-item" data-id="' + att.id + '">' +
                    '<img class="cm-gallery-thumb" src="' + url + '" alt="" />' +
                    '<button type="button" class="cm-gallery-remove-item" title="' + (wp.media.view.l10n.remove || 'Remove') + '">&times;</button>' +
                    '</div>';
                $preview.append(itemHtml);
            });

            $input.val(ids.join(','));
            $container.find('.cm-gallery-clear').show();
        });

        frame.on('open', function() {
            var selection = frame.state().get('selection');
            currentIds.forEach(function(id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });
        });

        frame.open();
    });

    /**
     * Handles individual image removal from the gallery preview and hidden input string.
     */
    $(document).on('click', '.cm-gallery-remove-item', function(e) {
        e.preventDefault();
        
        var $item = $(this).closest('.cm-gallery-item');
        var $container = $item.closest('.cm-gallery');
        var $input = $container.find('.cm-gallery-ids');
        var idToRemove = $item.data('id').toString();

        var ids = $input.val().split(',').filter(function(id) {
            return id !== idToRemove;
        });

        $input.val(ids.join(','));
        $item.remove();

        if (ids.length === 0) {
            $container.find('.cm-gallery-clear').hide();
        }
    });

    /**
     * Clears all images from the gallery field and resets the hidden data input.
     */
    $(document).on('click', '.cm-gallery-clear', function(e) {
        e.preventDefault();
        
        var $container = $(this).closest('.cm-gallery');
        $container.find('.cm-gallery-ids').val('');
        $container.find('.cm-gallery-preview').empty();
        $(this).hide();
    });

    /* ---------------------------------------------------------------- */
    /* Flush TinyMCE content back to its textarea before any submit.     */
    /* ---------------------------------------------------------------- */

    /**
     * Flushes editors before standard form submission.
     */
    $(document).on('submit', 'form', function () {
        if (window.tinymce) {
            try {
                tinymce.triggerSave();
            } catch (e) {}
        }
    });

    /**
     * The term "Add new" form submits over AJAX and is reset on success — flush
     * before the request and re-init editors once WP clears the inputs.
     */
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
        initSortable(document);
    });

})(jQuery);