/**
 * Text editor
 *
 * @package Hestia
 */

/* global tinyMCE */
/* global wp */

/* exported WPEditorWidget */
var WPEditorWidget = {

	/**
	 * Current content id
	 *
	 * @var string Current content id.
	 */
    contentId: '',

	/**
	 * Z index for Overlay
	 *
	 * @var int Z index for Overlay.
	 */
	wpFullOverlayOriginalZIndex: 0,

	/**
	 * Visible or not
	 *
	 * @var bool Visible or not.
	 */
	isVisible: false,

	keyPressCount: 0,

	init: function ( contentId ) {
		this.contentId = contentId;
		return this;
    },

	run: function ( editorWidget ) {
        editorWidget.toggleEditor();
        editorWidget.updateCustomizeControl();
    },

	/**
	 * Show/Hide editor
	 */
	toggleEditor: function(){
		if ( this.isVisible === true ) {
			this.hideEditor();
		} else {
			this.showEditor( this.contentId );
		}
	},

	/**
	 * Show the editor
	 *
	 * @param contentId
	 */
	showEditor: function(contentId) {
		this.isVisible = true;
		var overlay    = jQuery( '.wp-full-overlay' );

		jQuery( 'body.wp-customizer #wp-editor-widget-container' ).fadeIn( 100 ).animate( {'bottom':'0'} );

		this.wpFullOverlayOriginalZIndex = parseInt( overlay.css( 'zIndex' ) );
		overlay.css( { zIndex: 49000 } );

		this.setEditorContent( contentId );
	},

	/**
	 * Hide editor
	 */
	hideEditor: function() {
		this.isVisible = false;
		jQuery( 'body.wp-customizer #wp-editor-widget-container' ).animate( {'bottom':'-650px'} ).fadeOut();
		jQuery( '.wp-full-overlay' ).css( { zIndex: this.wpFullOverlayOriginalZIndex } );
	},

	/**
	 * Set editor content
	 */
	setEditorContent: function(contentId) {
		var editor  = tinyMCE.get( 'wpeditorwidget' );
		var content = jQuery( '#' + contentId ).val();

		if (typeof editor === 'object' && editor !== null) {
			editor.setContent( content );
		}
		jQuery( '#wpeditorwidget' ).val( content );
	},

    updateCustomizeControl: function () {
        var editor  = tinyMCE.get( 'wpeditorwidget' );
		var th = this;
        editor.on('NodeChange KeyUp', function (e) {
        	if( e.type === 'keyup'){
                th.keyPressCount++;
        		if( th.keyPressCount > 5){
        			th.doUpdate(editor);
                    th.keyPressCount = 0;
				}
			} else {
                th.doUpdate(editor);
                th.keyPressCount = 0;
			}

        });

    },

	doUpdate: function ( editor ) {
        var content = editor.getContent();
        var contentField = jQuery( '#' + this.contentId );
        contentField.val( content );
        contentField.trigger('change');
    }

};

jQuery( window ).load(function () {

	var editor;

	/**
	 * This handles the click form customizer control.
	 */
	jQuery(document).on('click','.edit-content-button',function (event) {
		event.preventDefault();
        if( typeof editor === 'undefined'  ){
            var editorId = jQuery(this).data('editor-id');
            if( typeof editorId !== 'undefined' ) {
                editor = WPEditorWidget.init(editorId);
            	WPEditorWidget.run(editor);
			}
		} else {
			WPEditorWidget.run(editor);
		}
	});

	/**
	 * Toggle editor when the user clicks on customizer shortcut.
	 */
	var customize = wp.customize;
	customize.previewer.bind(
		'trigger-open-editor', function( data ) {
            if( typeof data !== 'undefined'){
				if( typeof editor !== 'undefined'  ){
					editor.toggleEditor();
				} else {
                    editor = WPEditorWidget.init(data);
                    WPEditorWidget.run(editor);
				}
            }
		}
	);

	/**
	 * Hide the editor if the user clicks on back button to exit about panel.
	 */
	jQuery( '.customize-section-back' ).on(
		'click',function(){
			if( typeof editor !== 'undefined' ){
				editor.hideEditor();
			}
		}
	);

	/**
	 * Focus menu when the user clicks on customizer shortcut of the menu.
	 */
	customize.previewer.bind(
		'trigger-focus-menu', function() {
			wp.customize.section( 'menu_locations' ).focus();
		}
	);
});
