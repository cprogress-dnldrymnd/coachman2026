/**
 * Coachman 2026 — native Gutenberg block registrations (build-less).
 *
 * Editor-friendly replacements for the Carbon Fields `Block::make()` blocks in
 * includes/post-meta.php. Leaf blocks preview live via ServerSideRender; the
 * tabs/swiper container blocks use native InnerBlocks. Frontend HTML is produced
 * by the PHP render callbacks in includes/gutenberg-blocks.php.
 *
 * No build step: everything comes from the `wp.*` globals enqueued as script
 * dependencies. Attribute names must stay in sync with the PHP registration.
 */
(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;

	var blockEditor = wp.blockEditor;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var useBlockProps = blockEditor.useBlockProps;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;

	var components = wp.components;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var ToggleControl = components.ToggleControl;
	var SelectControl = components.SelectControl;
	var FormTokenField = components.FormTokenField;
	var ColorPalette = components.ColorPalette;
	var Button = components.Button;

	var ServerSideRender = wp.serverSideRender;
	var __ = wp.i18n.__;

	var CATEGORY = 'coachman';
	var data = window.coachmanBlocks || {};

	// Term option lists localised from PHP (each item: { value, label }).
	var caravanModels = data.caravanModels || [];
	var motorhomeModels = data.motorhomeModels || [];
	var campervanModels = data.campervanModels || [];

	/* ----------------------------------------------------------------- */
	/* Shared helpers                                                     */
	/* ----------------------------------------------------------------- */

	function currentPostId() {
		try {
			return wp.data.select('core/editor').getCurrentPostId();
		} catch (e) {
			return 0;
		}
	}

	// Small label shown at the top of a block in the editor canvas.
	function editorLabel(text) {
		return el(
			'div',
			{
				style: {
					font: '600 11px/1.4 sans-serif',
					textTransform: 'uppercase',
					letterSpacing: '0.5px',
					color: '#757575',
					marginBottom: '8px'
				}
			},
			text
		);
	}

	/**
	 * Searchable, token-based multi-select for taxonomy models.
	 *
	 * The block attribute stores an array of string term IDs, but FormTokenField
	 * works in display labels, so we map IDs <-> labels on the way in and out.
	 * Free-typed tokens that don't match a known model are discarded.
	 *
	 * options: [ { value: '12', label: 'Acadia' }, ... ]
	 * selectedIds: array of string term IDs
	 */
	function modelTokenField(label, options, selectedIds, onChange) {
		var labelByValue = {};
		var valueByLabel = {};
		options.forEach(function (o) {
			labelByValue[o.value] = o.label;
			valueByLabel[o.label] = o.value;
		});

		var selectedLabels = (selectedIds || []).map(function (id) {
			return labelByValue[id] || String(id);
		});

		return el(FormTokenField, {
			label: label,
			value: selectedLabels,
			suggestions: options.map(function (o) { return o.label; }),
			__experimentalExpandOnFocus: true,
			__experimentalShowHowTo: false,
			onChange: function (tokens) {
				var ids = [];
				tokens.forEach(function (t) {
					if (valueByLabel.hasOwnProperty(t)) {
						ids.push(valueByLabel[t]);
					}
				});
				onChange(ids);
			}
		});
	}

	/**
	 * Leaf block whose preview is rendered server-side (ServerSideRender).
	 *
	 * opts: { title, attributes, usePostContext, inspector(props) -> element|null }
	 */
	function registerServerBlock(name, opts) {
		registerBlockType(name, {
			apiVersion: 3,
			title: opts.title,
			category: CATEGORY,
			icon: opts.icon || 'screenoptions',
			attributes: opts.attributes || {},
			// Current-post-context blocks read the Query Loop's per-iteration
			// post (postId/postType) so previews match the post being looped,
			// not the template being edited.
			usesContext: opts.usePostContext ? ['postId', 'postType'] : undefined,
			supports: { html: false },
			edit: function (props) {
				var blockProps = useBlockProps();
				var ssrProps = { block: name, attributes: props.attributes };
				if (opts.usePostContext) {
					var ctxPostId = props.context && props.context.postId;
					ssrProps.urlQueryArgs = { post_id: ctxPostId || currentPostId() };
				}
				var inspector = opts.inspector ? opts.inspector(props) : null;
				return el(
					Fragment,
					{},
					inspector ? el(InspectorControls, {}, inspector) : null,
					el('div', blockProps, el(ServerSideRender, ssrProps))
				);
			},
			// Dynamic block — markup comes from the PHP render_callback.
			save: function () {
				return null;
			}
		});
	}

	/**
	 * Container block edited through native InnerBlocks; PHP wraps the saved
	 * inner content on the frontend.
	 *
	 * opts: { title, attributes, allowedBlocks, template, templateLock,
	 *         orientation, label, inspector(props) -> element|null }
	 */
	function registerContainerBlock(name, opts) {
		registerBlockType(name, {
			apiVersion: 3,
			title: opts.title,
			category: CATEGORY,
			icon: opts.icon || 'layout',
			parent: opts.parent || undefined,
			attributes: opts.attributes || {},
			supports: { html: false },
			edit: function (props) {
				var blockProps = useBlockProps({
					style: {
						border: '1px dashed #c3c4c7',
						borderRadius: '4px',
						padding: '12px'
					}
				});
				var innerProps = {};
				if (opts.allowedBlocks) {
					innerProps.allowedBlocks = opts.allowedBlocks;
				}
				if (opts.template) {
					innerProps.template = opts.template;
				}
				if (opts.templateLock !== undefined) {
					innerProps.templateLock = opts.templateLock;
				}
				if (opts.orientation) {
					innerProps.orientation = opts.orientation;
				}
				var inspector = opts.inspector ? opts.inspector(props) : null;
				return el(
					Fragment,
					{},
					inspector ? el(InspectorControls, {}, inspector) : null,
					el(
						'div',
						blockProps,
						opts.label ? editorLabel(opts.label) : null,
						el(InnerBlocks, innerProps)
					)
				);
			},
			// Dynamic block — persist inner blocks so PHP receives $content.
			save: function () {
				return el(InnerBlocks.Content);
			}
		});
	}

	/**
	 * Dynamic block with no fields and no inner blocks (fixed markup). Shows a
	 * simple labelled placeholder in the editor.
	 */
	function registerPlaceholderBlock(name, opts) {
		registerBlockType(name, {
			apiVersion: 3,
			title: opts.title,
			category: CATEGORY,
			icon: opts.icon || 'marker',
			parent: opts.parent || undefined,
			attributes: opts.attributes || {},
			supports: { html: false },
			edit: function (props) {
				var blockProps = useBlockProps({
					style: {
						border: '1px dashed #c3c4c7',
						borderRadius: '4px',
						padding: '12px',
						textAlign: 'center'
					}
				});
				var inspector = opts.inspector ? opts.inspector(props) : null;
				return el(
					Fragment,
					{},
					inspector ? el(InspectorControls, {}, inspector) : null,
					el('div', blockProps, editorLabel(opts.title))
				);
			},
			save: function () {
				return null;
			}
		});
	}

	/* ----------------------------------------------------------------- */
	/* Icon                                                               */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/icon', {
		title: __('Icon', 'glossop-caravans'),
		icon: 'star-filled',
		attributes: {
			iconId: { type: 'number', default: 0 },
			iconColor: { type: 'string', default: '' },
			iconAlignment: { type: 'string', default: '' },
			iconWidth: { type: 'string', default: '' },
			iconHeight: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Icon settings', 'glossop-caravans'), initialOpen: true },
				el(MediaUploadCheck, {},
					el(MediaUpload, {
						onSelect: function (media) {
							set({ iconId: media.id });
						},
						allowedTypes: ['image'],
						value: a.iconId,
						render: function (o) {
							return el(Fragment, {},
								el(Button, {
									variant: 'secondary',
									onClick: o.open
								}, a.iconId ? __('Replace icon', 'glossop-caravans') : __('Select icon', 'glossop-caravans')),
								a.iconId
									? el(Button, {
										variant: 'tertiary',
										isDestructive: true,
										onClick: function () { set({ iconId: 0 }); }
									}, __('Remove', 'glossop-caravans'))
									: null
							);
						}
					})
				),
				el('p', { style: { margin: '12px 0 4px' } }, __('Color', 'glossop-caravans')),
				el(ColorPalette, {
					value: a.iconColor,
					colors: [
						{ name: 'Green', color: '#45c324' },
						{ name: 'Black', color: '#000000' },
						{ name: 'White', color: '#ffffff' }
					],
					onChange: function (color) {
						set({ iconColor: color || '' });
					}
				}),
				el(SelectControl, {
					label: __('Alignment', 'glossop-caravans'),
					value: a.iconAlignment,
					options: [
						{ label: __('Default', 'glossop-caravans'), value: '' },
						{ label: __('Center', 'glossop-caravans'), value: 'text-center' },
						{ label: __('Left', 'glossop-caravans'), value: 'text-start' },
						{ label: __('Right', 'glossop-caravans'), value: 'text-end' }
					],
					onChange: function (v) { set({ iconAlignment: v }); }
				}),
				el(TextControl, {
					label: __('Width', 'glossop-caravans'),
					value: a.iconWidth,
					onChange: function (v) { set({ iconWidth: v }); }
				}),
				el(TextControl, {
					label: __('Height', 'glossop-caravans'),
					value: a.iconHeight,
					onChange: function (v) { set({ iconHeight: v }); }
				})
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Video Gallery                                                      */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/video-gallery', {
		title: __('Video Gallery', 'glossop-caravans'),
		icon: 'video-alt3'
	});

	/* ----------------------------------------------------------------- */
	/* Tabs                                                               */
	/* ----------------------------------------------------------------- */

	registerContainerBlock('coachman/tabs-navigation', {
		title: __('Tabs Navigation', 'glossop-caravans'),
		icon: 'editor-justify',
		label: __('Tabs Navigation', 'glossop-caravans'),
		allowedBlocks: ['coachman/tabs-navigation-item'],
		template: [['coachman/tabs-navigation-item']],
		attributes: {
			tabId: { type: 'string', default: '' },
			isSwiper: { type: 'boolean', default: false },
			direction: { type: 'string', default: '' },
			tabStyle: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Navigation settings', 'glossop-caravans'), initialOpen: true },
				el(TextControl, {
					label: __('Tab ID', 'glossop-caravans'),
					value: a.tabId,
					onChange: function (v) { set({ tabId: v }); }
				}),
				el(ToggleControl, {
					label: __('Is Swiper', 'glossop-caravans'),
					checked: !!a.isSwiper,
					onChange: function (v) { set({ isSwiper: v }); }
				}),
				el(SelectControl, {
					label: __('Direction', 'glossop-caravans'),
					value: a.direction,
					options: [
						{ label: __('Default', 'glossop-caravans'), value: '' },
						{ label: __('Horizontal', 'glossop-caravans'), value: 'flex-row' },
						{ label: __('Vertical', 'glossop-caravans'), value: 'flex-column' }
					],
					onChange: function (v) { set({ direction: v }); }
				}),
				el(SelectControl, {
					label: __('Style', 'glossop-caravans'),
					value: a.tabStyle,
					options: [
						{ label: __('Default', 'glossop-caravans'), value: '' },
						{ label: __('Style 1', 'glossop-caravans'), value: 'style-1' },
						{ label: __('Style 2', 'glossop-caravans'), value: 'style-2' }
					],
					onChange: function (v) { set({ tabStyle: v }); }
				})
			);
		}
	});

	registerContainerBlock('coachman/tabs-navigation-item', {
		title: __('Tabs Navigation Item', 'glossop-caravans'),
		icon: 'editor-justify',
		label: __('Tab Item', 'glossop-caravans'),
		parent: ['coachman/tabs-navigation'],
		allowedBlocks: ['core/paragraph', 'core/image'],
		attributes: {
			tabItemId: { type: 'string', default: '' },
			noSubmenu: { type: 'boolean', default: false }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Tab item settings', 'glossop-caravans'), initialOpen: true },
				el(TextControl, {
					label: __('Tab Item ID', 'glossop-caravans'),
					value: a.tabItemId,
					onChange: function (v) { set({ tabItemId: v }); }
				}),
				el(ToggleControl, {
					label: __('No Submenu', 'glossop-caravans'),
					help: __('Adds the "no--submenu" class to this item.', 'glossop-caravans'),
					checked: !!a.noSubmenu,
					onChange: function (v) { set({ noSubmenu: v }); }
				})
			);
		}
	});

	registerContainerBlock('coachman/tabs-content', {
		title: __('Tabs Content', 'glossop-caravans'),
		icon: 'media-document',
		label: __('Tabs Content', 'glossop-caravans'),
		allowedBlocks: ['coachman/tabs-content-item'],
		template: [['coachman/tabs-content-item']],
		attributes: {
			tabId: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Content settings', 'glossop-caravans'), initialOpen: true },
				el(TextControl, {
					label: __('Tab ID', 'glossop-caravans'),
					value: a.tabId,
					onChange: function (v) { set({ tabId: v }); }
				})
			);
		}
	});

	registerContainerBlock('coachman/tabs-content-item', {
		title: __('Tabs Content Item', 'glossop-caravans'),
		icon: 'media-document',
		label: __('Tab Content Item', 'glossop-caravans'),
		parent: ['coachman/tabs-content'],
		attributes: {
			tabContentId: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Content item settings', 'glossop-caravans'), initialOpen: true },
				el(TextControl, {
					label: __('Tab ID', 'glossop-caravans'),
					value: a.tabContentId,
					onChange: function (v) { set({ tabContentId: v }); }
				})
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Swiper                                                             */
	/* ----------------------------------------------------------------- */

	registerContainerBlock('coachman/swiper', {
		title: __('Swiper', 'glossop-caravans'),
		icon: 'images-alt2',
		label: __('Swiper', 'glossop-caravans'),
		allowedBlocks: [
			'coachman/swiper-wrapper',
			'coachman/swiper-pagination',
			'coachman/swiper-navigation'
		],
		template: [['coachman/swiper-wrapper']],
		attributes: {
			swiperId: { type: 'string', default: '' },
			enableAutoplay: { type: 'boolean', default: false },
			autoplayDelay: { type: 'number', default: 3000 },
			disableOnInteraction: { type: 'boolean', default: false },
			spaceBetween: { type: 'string', default: '' },
			slidesPerView: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				Fragment,
				{},
				el(
					PanelBody,
					{ title: __('Swiper settings', 'glossop-caravans'), initialOpen: true },
					el(TextControl, {
						label: __('Swiper ID', 'glossop-caravans'),
						value: a.swiperId,
						onChange: function (v) { set({ swiperId: v }); }
					}),
					el(TextControl, {
						label: __('Space between', 'glossop-caravans'),
						type: 'number',
						value: a.spaceBetween,
						onChange: function (v) { set({ spaceBetween: v }); }
					}),
					el(TextControl, {
						label: __('Slides per view', 'glossop-caravans'),
						help: __('Number, or "auto".', 'glossop-caravans'),
						value: a.slidesPerView,
						onChange: function (v) { set({ slidesPerView: v }); }
					})
				),
				el(
					PanelBody,
					{ title: __('Autoplay', 'glossop-caravans'), initialOpen: false },
					el(ToggleControl, {
						label: __('Enable autoplay', 'glossop-caravans'),
						checked: !!a.enableAutoplay,
						onChange: function (v) { set({ enableAutoplay: v }); }
					}),
					a.enableAutoplay
						? el(TextControl, {
							label: __('Delay (ms)', 'glossop-caravans'),
							type: 'number',
							value: a.autoplayDelay,
							onChange: function (v) { set({ autoplayDelay: parseInt(v, 10) || 0 }); }
						})
						: null,
					a.enableAutoplay
						? el(ToggleControl, {
							label: __('Disable on interaction', 'glossop-caravans'),
							checked: !!a.disableOnInteraction,
							onChange: function (v) { set({ disableOnInteraction: v }); }
						})
						: null
				)
			);
		}
	});

	registerContainerBlock('coachman/swiper-wrapper', {
		title: __('Swiper Wrapper', 'glossop-caravans'),
		icon: 'images-alt2',
		label: __('Swiper Wrapper', 'glossop-caravans'),
		parent: ['coachman/swiper'],
		allowedBlocks: ['coachman/swiper-slide'],
		template: [['coachman/swiper-slide']]
	});

	registerContainerBlock('coachman/swiper-slide', {
		title: __('Swiper Slide', 'glossop-caravans'),
		icon: 'images-alt2',
		label: __('Swiper Slide', 'glossop-caravans'),
		parent: ['coachman/swiper-wrapper']
	});

	// Shared "Pagination & navigation style" inspector for the pagination /
	// navigation child blocks. The parent Swiper block reads this style off
	// whichever child carries it.
	function swiperStyleInspector(props) {
		var a = props.attributes;
		var set = props.setAttributes;
		return el(
			PanelBody,
			{ title: __('Style', 'glossop-caravans'), initialOpen: true },
			el(SelectControl, {
				label: __('Pagination & navigation style', 'glossop-caravans'),
				value: a.style,
				options: [
					{ label: __('Default', 'glossop-caravans'), value: '' },
					{ label: __('Style 2', 'glossop-caravans'), value: 'style-2' }
				],
				onChange: function (v) { set({ style: v }); }
			})
		);
	}

	registerPlaceholderBlock('coachman/swiper-pagination', {
		title: __('Swiper Pagination', 'glossop-caravans'),
		icon: 'marker',
		parent: ['coachman/swiper'],
		attributes: { style: { type: 'string', default: '' } },
		inspector: swiperStyleInspector
	});

	registerPlaceholderBlock('coachman/swiper-navigation', {
		title: __('Swiper Navigation', 'glossop-caravans'),
		icon: 'controls-forward',
		parent: ['coachman/swiper'],
		attributes: { style: { type: 'string', default: '' } },
		inspector: swiperStyleInspector
	});

	/* ----------------------------------------------------------------- */
	/* Listing Models                                                     */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/listing-models', {
		title: __('Listing Models', 'glossop-caravans'),
		icon: 'grid-view',
		attributes: {
			isSwiper: { type: 'boolean', default: false },
			displayModelLayouts: { type: 'boolean', default: false },
			caravanModels: { type: 'array', default: [] },
			motorhomeModels: { type: 'array', default: [] },
			campervanModels: { type: 'array', default: [] }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Listing settings', 'glossop-caravans'), initialOpen: true },
				el(ToggleControl, {
					label: __('Is Swiper', 'glossop-caravans'),
					checked: !!a.isSwiper,
					onChange: function (v) { set({ isSwiper: v }); }
				}),
				el(ToggleControl, {
					label: __('Display Model Layouts', 'glossop-caravans'),
					checked: !!a.displayModelLayouts,
					onChange: function (v) { set({ displayModelLayouts: v }); }
				}),
				modelTokenField(
					__('Caravan models', 'glossop-caravans'),
					caravanModels,
					a.caravanModels,
					function (v) { set({ caravanModels: v }); }
				),
				modelTokenField(
					__('Motorhome models', 'glossop-caravans'),
					motorhomeModels,
					a.motorhomeModels,
					function (v) { set({ motorhomeModels: v }); }
				),
				modelTokenField(
					__('Campervan models', 'glossop-caravans'),
					campervanModels,
					a.campervanModels,
					function (v) { set({ campervanModels: v }); }
				),
				el('p', { style: { fontSize: '11px', color: '#757575' } },
					__('Type to search, then click a model to add it. Click ✕ on a tag to remove.', 'glossop-caravans'))
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Listing Title / Feature / Buttons (use current post context)      */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/listing-title', {
		title: __('Listing Title', 'glossop-caravans'),
		icon: 'heading',
		usePostContext: true
	});

	registerServerBlock('coachman/listing-feature', {
		title: __('Listing Feature', 'glossop-caravans'),
		icon: 'list-view',
		usePostContext: true
	});

	registerServerBlock('coachman/listing-buttons', {
		title: __('Listing Buttons', 'glossop-caravans'),
		icon: 'button',
		usePostContext: true
	});

	/* ----------------------------------------------------------------- */
	/* Video Tour Carousel                                                */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/video-tour-carousel', {
		title: __('Video Tour Carousel', 'glossop-caravans'),
		icon: 'format-video',
		attributes: {
			postType: { type: 'string', default: '' },
			modelId: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;

			// Term list to offer depends on the chosen post type.
			var modelsByType = {
				caravan: caravanModels,
				motorhome: motorhomeModels,
				campervan: campervanModels
			};
			var modelOptions = [{ label: __('— Select a model —', 'glossop-caravans'), value: '' }]
				.concat((modelsByType[a.postType] || []).map(function (o) {
					return { label: o.label, value: o.value };
				}));

			return el(
				PanelBody,
				{ title: __('Video tour settings', 'glossop-caravans'), initialOpen: true },
				el(SelectControl, {
					label: __('Post type', 'glossop-caravans'),
					value: a.postType,
					options: [
						{ label: __('— Select a post type —', 'glossop-caravans'), value: '' },
						{ label: __('Caravan', 'glossop-caravans'), value: 'caravan' },
						{ label: __('Motorhome', 'glossop-caravans'), value: 'motorhome' },
						{ label: __('Campervan', 'glossop-caravans'), value: 'campervan' }
					],
					// Reset the model when the post type (and so its taxonomy) changes.
					onChange: function (v) { set({ postType: v, modelId: '' }); }
				}),
				a.postType
					? el(SelectControl, {
						label: __('Model', 'glossop-caravans'),
						value: a.modelId,
						options: modelOptions,
						onChange: function (v) { set({ modelId: v }); }
					})
					: null
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Model Technical Details                                            */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/model-technical-details', {
		title: __('Model Technical Details', 'glossop-caravans'),
		icon: 'media-spreadsheet',
		attributes: {
			buttonText: { type: 'string', default: 'View all technical details' },
			modelId: { type: 'string', default: '' }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var modelOptions = [{ label: __('— Select a model —', 'glossop-caravans'), value: '' }]
				.concat(caravanModels.map(function (o) {
					return { label: __('Caravan', 'glossop-caravans') + ': ' + o.label, value: o.value };
				}))
				.concat(motorhomeModels.map(function (o) {
					return { label: __('Motorhome', 'glossop-caravans') + ': ' + o.label, value: o.value };
				}));
			return el(
				PanelBody,
				{ title: __('Technical details settings', 'glossop-caravans'), initialOpen: true },
				el(TextControl, {
					label: __('Button Text', 'glossop-caravans'),
					value: a.buttonText,
					onChange: function (v) { set({ buttonText: v }); }
				}),
				el(SelectControl, {
					label: __('Model', 'glossop-caravans'),
					value: a.modelId,
					options: modelOptions,
					onChange: function (v) { set({ modelId: v }); }
				})
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Partner                                                            */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/partner', {
		title: __('Partner', 'glossop-caravans'),
		icon: 'businessperson',
		usePostContext: true,
		attributes: {
			showLogo: { type: 'boolean', default: true },
			showWebsite: { type: 'boolean', default: true }
		},
		inspector: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el(
				PanelBody,
				{ title: __('Partner settings', 'glossop-caravans'), initialOpen: true },
				el(ToggleControl, {
					label: __('Show logo', 'glossop-caravans'),
					checked: !!a.showLogo,
					onChange: function (v) { set({ showLogo: v }); }
				}),
				el(ToggleControl, {
					label: __('Show website link', 'glossop-caravans'),
					checked: !!a.showWebsite,
					onChange: function (v) { set({ showWebsite: v }); }
				})
			);
		}
	});

	/* ----------------------------------------------------------------- */
	/* Event Date                                                         */
	/* ----------------------------------------------------------------- */

	registerServerBlock('coachman/event-date', {
		title: __('Event Date', 'glossop-caravans'),
		icon: 'calendar-alt',
		usePostContext: true
	});
})(window.wp);
