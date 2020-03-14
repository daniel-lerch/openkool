/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.stylesSet.add( 'my_styles', [
	{ name: 'Marker', element: 'mark'}
] );

CKEDITOR.editorConfig = function( config ) {
	config.language = kOOL.language;
	config.toolbar = 'AdminToolbar';

	config.extraPlugins = 'image,richcombo,listblock,floatpanel,panel,stylescombo';

	config.toolbar_AdminToolbar =
		[
			{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
			{ name: 'basicstyles', items : [ 'Bold','Italic','-','RemoveFormat' ] },
			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Blockquote' ] },
			{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
			{ name: 'images', items : [ 'Image' ] },
			{ name: 'styles', items: [ 'Styles' ] }
		];

	config.removeDialogTabs = 'link:advanced;image:advanced';

	config.stylesSet = 'my_styles';
};

CKEDITOR.on('dialogDefinition', function(ev) {
	var c = ev.editor.lang.common;
	var b = ev.editor.lang.link;

	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;

	if (dialogName == 'link') {
		//REMOVE NOT REQUIRED TABS

		var targetTab = dialogDefinition.getContents('target');
		var targetTabLt = targetTab.elements[0].children[0];
		targetTabLt['items']=[[c.notSet,"notSet"],[c.targetNew,"_blank"]];

		var infoTab = dialogDefinition.getContents('info');
		var infoTabLt = infoTab.elements[0];
		infoTabLt['items']=[[b.toUrl,"url"],[b.toEmail,"email"]];

		//Remove anchor link, only keep url and email
		var linkOptions = infoTab.get('linkType');
		linkOptions['items'] = [['URL', 'url'], ['E-Mail', 'email']];
	}
});
