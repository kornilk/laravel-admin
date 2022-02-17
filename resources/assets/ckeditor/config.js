/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

 CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others'},
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
    ];
    
	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript,Anchor,Table,Image';
    config.extraPlugins = 'inlineimage,insertobject,widget,justify,magicline',
    config.resize_dir = 'both',
    config.resize_minWidth = 300,
    config.skin = 'moonocolor',
        
    config.inlineImageBrowserMethod = browse_images,
    
	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';
    
    //config.forcePasteAsPlainText = true;
    config.fillEmptyBlocks = false;

    CKEDITOR.on('dialogDefinition', function (ev)
    {
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;
        if (dialogName == 'link')
        {
            // FCKConfig.DefaultLinkTarget = '_blank'
            // Get a reference to the "Target" tab.
            var targetTab = dialogDefinition.getContents('target');
            // Set the default value for the URL field.
            var targetField = targetTab.get('linkTargetType');
            targetField[ 'default' ] = '_blank';
        }
    });

    config.baseFloatZIndex = 1050,
    config.disableNativeSpellChecker = false,
    config.magicline_tabuList = ['data-tabu'];
    config.allowedContent = {
        'p': {
            styles: ['text-align']
        },
        oel: {
            attributes: ['data-*', 'class', 'contenteditable'],
            classes: 'insertobject'
        },
        'em u sub s sup ul li ol blockquote b strong figure figcaption picture': true,
        img: {
            attributes: ['!src', 'class', 'id', 'style', 'width', 'height', 'alt', 'title', 'data-*']
        },
        source: {
            attributes: ['srcset', 'media', 'width', 'height', 'data-*']
        },
        video: {
            attributes: ['class', 'id', 'style', 'width', 'height', 'autoplay', 'controls', 'muted', 'loop', 'preload']
        },
        a: {
            attributes: ['!href', 'class', 'id', 'style', 'target']
        },
        div: {
            styles: ['text-align', 'text-indent'],
            classes: ['!article-element', 'left', 'right', 'center', 'article-image', 'inlineimage', 'article-image-text', 'image-text', 'image-source', 'image-wrapper', 'article-image-text', 'image-source-label', 'image-source-text', 'clearfix', 'article-object', 'highlighted', 'left-out', 'right-out', 'imageTextFull'],
            attributes: ['data-*', 'class', 'contenteditable']
        },
        span: {
            classes: ['!article-element'],
            attributes: ['data-*', 'class']
        },
        script: {
            attributes: ['!type', '!objscript']
        }
    },
    config.fillEmptyBlocks = false
};
