
define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbSuggest = new Class({
        Extends   : FbElement,
        initialize: function (element, options) {
            this.setPlugin('fabrikSuggest');
            this.parent(element, options);

            this.removeLabel();

            var button = document.getElementById('suggest_button'), self = this;
            button.onclick = function () {
                self.cloneRow();
            }

        },
        removeLabel: function () {
            var field, fieldChilds, child, i;
            field = document.getElementsByClassName('fb_el_' + this.options.element)[0];
            fieldChilds = field.childNodes;

            for (i=0; i<fieldChilds.length; i++) {
                child = fieldChilds[i];
                if ((child.nodeName !== '#text') && (child.nodeName !== '#comment')) {
                    if (child.className === 'fabrikLabel') {
                        child.remove();
                    }
                }
            }
        },
        insertLoading: function () {
            var page = document.getElementById('g-page-surround');
            var body = document.getElement('body');
            body.style.overflow = 'hidden';

            var loading = document.createElement('div');
            loading.setAttribute('id', 'suggest_loading');
            loading.setAttribute('style', 'position: absolute;\n' +
                '  top: 0px;\n' +
                '  left: 0px;\n' +
                '  z-index: 100;\n' +
                '  background-color: #000;\n' +
                '  opacity: 0.5;\n' +
                '  width: 100%;\n' +
                '  height: 100%;\n' +
                '  display: block;' +
                '  text-align: center;');
            var img = document.createElement('img');
            img.setAttribute('src', 'https://static.wixstatic.com/media/9bd62a_40c8fc9e5a794e9fad1655b05c3d7e0c~mv2.gif');
            img.setAttribute('id', 'suggest_loading_img');
            img.setAttribute('style', 'position: fixed; top: 40%; left: 42%; z-index: 1000;');

            loading.appendChild(img);
            page.appendChild(loading);
        },
        cloneRow: function () {
            var self = this;
            jQuery.ajax ({
                url: Fabrik.liveSite + 'index.php',
                method: "POST",
                data: {
                    'option': 'com_fabrik',
                    'format': 'raw',
                    'task': 'plugin.pluginAjax',
                    'plugin': 'suggest',
                    'method': 'cloneRow',
                    'g': 'element',
                    'formId': this.options.formId,
                    'rowId': this.options.rowId,
                    'listId': this.options.listId,
                    'situacaoName': this.options.situacaoName,
                    'situacaoValue': this.options.situacaoValue,
                    'idMasterName': this.options.idMasterName,
                    'listNameReview': this.options.listNameReview,
                    'contribuidor': this.options.contribuidor,
                    'userId': this.options.userId,
                    'creator': this.options.creator,
                    'creatorValue': this.options.creatorValue
                },
                beforeSend: function () {
                    self.insertLoading();
                }
            }).done (function (data) {
                window.location.href = data;
            });
        }
    });

    return window.FbSuggest;
});