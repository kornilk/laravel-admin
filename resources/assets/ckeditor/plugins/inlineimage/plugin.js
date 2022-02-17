/* global CKEDITOR */

CKEDITOR.plugins.add('inlineimage', {
    requires: 'widget',
    lang: ['en', 'hu'],
    icons: 'inlineimage,deleteimage,inlineimagecenter,inlineimageleft,inlineimageright,text',
    init: function (editor) {
        CKEDITOR.dialog.add('setSizeDialog', this.path + 'dialogs/size.js');
        editor.addCommand('openInlineImageBrowser', {
            requiredContent: 'img',
            exec: function (editor) {

                var browserCallback = function (response) {

                    var image_text = '';
                    var image_source = '';
                    var image_text_cont = '';

                    if (response.title !== undefined && response.title !== '' && response.title !== null)
                        image_text = '<div data-tabu="true" class="image-text article-element">' + response.title + '</div>';

                    if (response.source !== undefined && response.source !== '' && response.source !== null)
                        image_source = '<div data-tabu="true" class="image-source article-element"><div data-tabu="true" class="image-source-text article-element">Forrás: ' + response.source + '</div></div>';

                    if (image_text !== '' || image_source !== '')
                        image_text_cont = '<div data-tabu="true" class="article-image-text article-element">' + image_text + image_source + '</div>';

                    if (response.title === undefined || response.title === null) response.title = '';
                    if (response.source === undefined || response.source === null) response.source = '';

                    $img = '<img data-tabu="true" width="' + response.picture.default.width + '" height="' + response.picture.default.height + '" data-max-height="' + response.height + '" data-max-width="' + response.width + '" alt="' + response.title + '" src="' + response.picture.default.path + '" data-original="' + response.path + '" title="Forrás: ' + response.source + '"/>';

                    $sources = '';
                    
                    function reverseForIn(obj, f) {
                        var arr = [];
                        for (var key in obj) {
                          // add hasOwnPropertyCheck if needed
                          arr.push(key);
                        }
                        for (var i=arr.length-1; i>=0; i--) {
                          f.call(obj, arr[i]);
                        }
                    }
                    
                    reverseForIn(response.picture.sources, function (i) { 
                        $sources += '<source media="(min-width:'+i+'px)" width="'+response.picture.sources[i].width+'" height="'+response.picture.sources[i].height+'" srcSet="'+response.picture.sources[i].path+'"/>'
                     });
     
                    var element = CKEDITOR.dom.element.createFromHtml('<div data-tabu="true" class="article-element article-image"><div data-tabu="true" class="image-wrapper article-element"><picture>'+$sources+$img+'</picture>' + image_text_cont + '</div></div>');

                    editor.insertElement(element);
                    editor.widgets.initOn(element, 'inlineimage');
                };
                editor.config.inlineImageBrowserMethod(browserCallback);
            }
        });
        editor.addCommand('deleteInlineImage', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                element = element.remove();
            }
        });

        editor.addCommand('removeInlineImageTitle', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.remove();
            }
        });

        editor.addCommand('removeInlineImageSource', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.remove();
            }
        });

        editor.addCommand('addInlineImageTitle', {
            exec: function (editor) {

            }
        });

        //editor.addCommand('setSizeDialog', new CKEDITOR.dialogCommand('setSizeDialog'));

        editor.addCommand('setSizeDialog', {
            exec: function (editor) {
                editor.openDialog('setSizeDialog');
            }
        });

        editor.addCommand('imageToLeft', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                //element.removeClass( 'left' );
                //element.removeClass( 'right' );
                //element.addClass( 'left' );
                element = element.find('.article-image').getItem(0);
                element.removeClass('center');
                element.removeClass('left');
                element.removeClass('right');
                element.removeClass('small-right');
                element.addClass('left-out');
            }
        });

        editor.addCommand('imageToRight', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                //element.removeClass( 'left' );
                //element.removeClass( 'right' );
                //element.addClass( 'right' );
                element = element.find('.article-image').getItem(0);
                element.removeClass('center');
                element.removeClass('left');
                element.removeClass('right');
                element.removeClass('left-out');
                element.addClass('right-out');
            }
        });

        editor.addCommand('imageToCenter', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                //element.removeClass( 'left' );
                //element.removeClass( 'right' );
                element = element.find('.article-image').getItem(0);
                element.removeClass('center');
                element.removeClass('left');
                element.removeClass('right-out');

                element.removeClass('left-out');
                //element.addClass('center');
            }
        });

        editor.addCommand('imageTextFull', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                element = element.find('.article-image').getItem(0);
                element.addClass('imageTextFull');
            }
        });

        editor.addCommand('imageTextInline', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement();
                element = element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                }, true);
                element = element.find('.article-image').getItem(0);
                element.removeClass('imageTextFull');
            }
        });

        editor.ui.addButton('openInlineImageBrowser', {
            label: editor.lang.inlineimage.insertImage,
            command: 'openInlineImageBrowser',
            toolbar: 'insert'
            , icon: 'inlineimage'
        });

        editor.widgets.add('inlineimage', {
            // Widget code.
            command: 'openInlineImageBrowser',
            /*dialog: 'setSizeDialog',*/
            init: function () {
                var el = this.element;
                var maxWidth = el.getAttribute("data-width-max");
                if (maxWidth !== undefined && maxWidth !== null && maxWidth !== '') {
                    el.setStyle("max-width", maxWidth + 'px');
                }
                var minWidth = el.getAttribute("data-width-min");
                if (minWidth !== undefined && minWidth !== null && minWidth !== '') {
                    el.setStyle("min-width", minWidth + 'px');
                }

            },
            inited: function (element) {

            },
            /*data: function () {
             var el = this.element.getChild(0);
             
             },*/
            upcast: function (element) {
                return element.name === 'div' && element.hasClass('article-image');
            },
            editables: {
                text: {
                    selector: 'div.image-text',
                    allowedContent: 'strong em u sub s sup br a[*]'
                },
                source: {
                    selector: 'div.image-source-text',
                    allowedContent: 'strong em u sub s sup a[*]'
                }
            }
        });
        if (editor.contextMenu) {

            editor.addMenuGroup('imageEdit');
            editor.addMenuItem('imageItem', {
                label: editor.lang.inlineimage.removeImage,
                icon: 'deleteimage',
                command: 'deleteInlineImage',
                group: 'imageEdit'
            });

            editor.addMenuItem('removeImageItemTitle', {
                label: editor.lang.inlineimage.removeImageTitle,
                icon: 'deleteimage',
                command: 'removeInlineImageTitle',
                group: 'imageEdit'
            });

            editor.addMenuItem('addImageItemTitle', {
                label: editor.lang.inlineimage.addImageTitle,
                icon: 'inlineimage',
                command: 'addInlineImageTitle',
                group: 'imageEdit'
            });

            editor.addMenuItem('removeImageItemSource', {
                label: editor.lang.inlineimage.removeImageSource,
                icon: 'deleteimage',
                command: 'removeInlineImageSource',
                group: 'imageEdit'
            });

            editor.addMenuItem('setImageSize', {
                label: editor.lang.inlineimage.setImageSize,
                icon: 'inlineimage',
                command: 'setSizeDialog',
                group: 'imageEdit'
            });

            editor.addMenuItem('leftImageItem', {
                label: editor.lang.inlineimage.imageToLeft,
                icon: 'inlineimageleft',
                command: 'imageToLeft',
                group: 'imageEdit'
            });

            editor.addMenuItem('rightImageItem', {
                label: editor.lang.inlineimage.imageToRight,
                icon: 'inlineimageright',
                command: 'imageToRight',
                group: 'imageEdit'
            });

            editor.addMenuItem('centerImageItem', {
                label: editor.lang.inlineimage.imageToCenter,
                icon: 'inlineimagecenter',
                command: 'imageToCenter',
                group: 'imageEdit'
            });

            editor.addMenuItem('imageTextFull', {
                label: editor.lang.inlineimage.imageTextFull,
                icon: 'text',
                command: 'imageTextFull',
                group: 'imageEdit'
            });

            editor.addMenuItem('imageTextInline', {
                label: editor.lang.inlineimage.imageTextInline,
                icon: 'text',
                command: 'imageTextInline',
                group: 'imageEdit'
            });

            editor.contextMenu.addListener(function (element) {
                if (element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper') && el.getChild(0).hasClass('article-image'));
                }, true)) {
                    var menu = {};

                    if (element.getAscendant(function (el) {
                        return (typeof el.hasClass === 'function' && el.hasClass('image-text'));
                    }, true)) {
                        menu.removeImageItemTitle = CKEDITOR.TRISTATE_OFF;
                    }

                    if (element.getAscendant(function (el) {
                        return (typeof el.hasClass === 'function' && el.hasClass('image-source-text'));
                    }, true)) {
                        menu.removeImageItemSource = CKEDITOR.TRISTATE_OFF;
                    }

                    var widgetSelection = element.getAscendant(function (el) {
                        return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                    }, true);
                    var imageTitle = widgetSelection.findOne('.image-text');

                    if (imageTitle === null) {
                        //menu.addImageItemTitle = CKEDITOR.TRISTATE_OFF;
                    }

                    menu.imageItem = CKEDITOR.TRISTATE_OFF;
                    menu.setImageSize = CKEDITOR.TRISTATE_OFF;
                    menu.leftImageItem = CKEDITOR.TRISTATE_OFF;
                    menu.rightImageItem = CKEDITOR.TRISTATE_OFF;
                    menu.centerImageItem = CKEDITOR.TRISTATE_OFF;

                    element = element.getAscendant(function (el) {
                        return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper'));
                    }, true);
                    element = element.find('.article-image').getItem(0);

                    if (widgetSelection.find('.article-image').getItem(0).hasClass('imageTextFull')) {
                        menu.imageTextInline = CKEDITOR.TRISTATE_OFF;
                    } else {
                        menu.imageTextFull = CKEDITOR.TRISTATE_OFF;
                    }

                    return menu;
                }

            });
        }

    }
});