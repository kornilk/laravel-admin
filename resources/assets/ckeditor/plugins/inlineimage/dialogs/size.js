CKEDITOR.dialog.add('setSizeDialog', function (editor) {
    return {
        title: editor.lang.inlineimage.setImageSize,
        minWidth: 400,
        minHeight: 50,
        lang: ['en', 'hu'],
        onOk: function () {
            this.commitContent();
        },
        onShow: function () {
            this.setupContent();
        },
        contents: [
            {
                id: 'tab-basic',
                label: editor.lang.inlineimage.widthPercent,
                elements: [
                    /*{
                     type: 'text',
                     maxLength: 3,
                     id: 'im_mwp',
                     label: editor.lang.inlineimage.widthPercent,
                     setup: function () {
                     var el = editor.getSelection().getStartElement().getChild(0);
                     var cw = el.getAttribute("data-width-percent");
                     if (cw === undefined || cw === null) cw = '';
                     this.setValue(cw);
                     },
                     commit: function () {
                     
                     var el = editor.getSelection().getStartElement().getChild(0);
                     var cw = this.getValue();
                     
                     if (cw !== undefined && cw !== null && cw !== '') {
                     
                     cw = parseInt(this.getValue(), 10);
                     if (cw > 100) cw = 100;
                     if (cw < 1) cw = 1;
                     
                     }
                     
                     if (cw !== undefined && cw !== null && cw !== '') {
                     el.setAttribute("data-width-percent", cw);
                     el.style.maxWidth = 'max-width:' + cw + '%;';
                     }
                     else {
                     el.removeAttribute("data-width-percent");
                     el.style.maxWidth = '';
                     }
                     
                     }
                     },*/
                    {
                        type: 'text',
                        maxLength: 8,
                        id: 'im_mwmax',
                        label: editor.lang.inlineimage.widthMaxSize,
                        setup: function () {
                            var el = editor.getSelection().getStartElement().getChild(0);
                            var cw = el.getAttribute("data-width-max");
                            if (cw === undefined || cw === null) cw = '';
                            this.setValue(cw);
                        },
                        commit: function () {

                            var el = editor.getSelection().getStartElement().getChild(0);
                            var cw = this.getValue();

                            if (cw !== undefined && cw !== null && cw !== '') {

                                cw = parseInt(this.getValue(), 10);
                                if (cw < 1) cw = 1;

                            }

                            if (cw !== undefined && cw !== null && cw !== '') {
                                el.setAttribute("data-width-max", cw);
                                el.setStyle("max-width", cw + 'px');
                            }
                            else {
                                el.removeAttribute("data-width-max");
                                el.removeStyle("max-width");
                            }

                        }
                    },
                    {
                        type: 'text',
                        maxLength: 8,
                        id: 'im_mwmin',
                        label: editor.lang.inlineimage.widthMinSize,
                        setup: function () {
                            var el = editor.getSelection().getStartElement().getChild(0);
                            var cw = el.getAttribute("data-width-min");
                            if (cw === undefined || cw === null) cw = '';
                            this.setValue(cw);
                        },
                        commit: function () {

                            var el = editor.getSelection().getStartElement().getChild(0);
                            var cw = this.getValue();

                            if (cw !== undefined && cw !== null && cw !== '') {

                                cw = parseInt(this.getValue(), 10);
                                if (cw < 1) cw = 1;

                            }

                            if (cw !== undefined && cw !== null && cw !== '') {
                                el.setAttribute("data-width-min", cw);
                                el.setStyle("min-width", cw + 'px');
                            }
                            else {
                                el.removeAttribute("data-width-min");
                                el.removeStyle("min-width");
                            }

                        }
                    }
                ]
            }
        ]
    };
});
