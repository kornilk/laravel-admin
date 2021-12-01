/* global CKEDITOR */

CKEDITOR.plugins.add('insertobject', {
    requires: 'widget',
    lang: ['en', 'hu'],
    icons: 'insertobject,deleteobject',
    init: function (editor) {
        CKEDITOR.dialog.add('insertobjectDialog', this.path + 'dialogs/inserobject.js');
        editor.widgets.add('insertobject', {
            // Widget code.
            button: editor.lang.insertobject.insertObject,
            requiredContent: 'div',
            dialog: 'insertobjectDialog',
            template: '<div class="article-object article-element"><div class="article-element" data-json="">' + editor.lang.insertobject.objectBox + '</div></div>',
            init: function () {
                this.setData('JSON', this.element.getChild(0).getAttribute("data-json"));
            },
            data: function () {
                if (this.data.JSON)
                    this.element.getChild(0).setAttribute("data-json", this.data.JSON);
            },
            upcast: function (element) {
                return element.name === 'div' && element.hasClass('article-object');
            }
        });
        editor.addCommand('deleteObject', {
            exec: function (editor) {
                var selection = editor.getSelection();
                var element = selection.getStartElement().remove();
            }
        });
        if (editor.contextMenu) {

            editor.addMenuGroup('objectEdit');
            editor.addMenuItem('objectItem', {
                label: editor.lang.insertobject.removeObject,
                icon: 'deleteobject',
                command: 'deleteObject',
                group: 'objectEdit'
            });
            editor.contextMenu.addListener(function (element) {
                if (element.getAscendant(function (el) {
                    return (typeof el.hasClass === 'function' && el.hasClass('cke_widget_wrapper') && el.getChild(0).hasClass('article-object'));
                }, true)) {
                    return {objectItem: CKEDITOR.TRISTATE_OFF};
                }
            });
        }

    }
});