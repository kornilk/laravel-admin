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
		{ name: 'paragraph',   groups: [ 'align', 'bidi' ] },
    ];
    
	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript,Anchor,Table,Image';
    config.extraPlugins = 'justify,wordcount,notification',
    config.resize_dir = 'both',
    config.resize_minWidth = 300,
    config.height = 100,
    config.skin = 'moonocolor',
    config.wordcount = {
        showWordCount: false,
        showParagraphs: false,
        showCharCount: true,
        maxCharCount: 1000 //element.data('word-max')
    },
        
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
        'em u sub s sup b strong span': true,
        a: {
            attributes: ['!href', 'class', 'id', 'style', 'target']
        },
        div: {
            styles: ['text-align', 'text-indent'],
            classes: ['left', 'right', 'center'],
            attributes: ['class']
        }
    },
    config.fillEmptyBlocks = false
};