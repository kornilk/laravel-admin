
if (typeof Object.size === 'undefined') {
    Object.size = function (obj) {
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key))
                size++;
        }
        return size;
    };
}

if (typeof Array.isArray === 'undefined') {
    Array.isArray = function (obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    };
}

function json_encode(value, default_value) {
    try {
        return JSON.stringify(value);
    }
    catch (err) {
        if (default_value === undefined) {
            return null;
        }
        else {
            try {
                return JSON.stringify(default_value);
            }
            catch (err) {
                return "'" + default_value + "'";
            }
        }
    }
}

function json_decode(json, default_value) {
    if (default_value === undefined) default_value = null;

    if (json === null) return default_value;

    try {
        return JSON.parse(json);
    }
    catch (err) {

        return default_value;
    }
}

function fixedEncodeURIComponent(str) {
    return encodeURIComponent(str).replace(/[!'()*]/g, function (c) {
        return '%' + c.charCodeAt(0).toString(16);
    });
}

function createBrowseModalLoad($url, callback, modal) {
    $.ajax({
        url: $url,
    }).done(function (data) {
        modal.find('.modal-body').html(data);
        modal.find('.grid-popup-link').magnificPopup({ "type": 'image' });
        
        modal.find('a[data-form="modal"]:not([data-form-event-attached])').each((key, element)=>{
            var $modalButton = $(element);
            $modalButton.attr('data-form-event-attached', true);
        }).click(function (e) {
            e.preventDefault();
            var modalButton = $(e.target);
            if(!modalButton.attr('disabled')){
                $.ajax({
                    url: modalButton.attr('href'),
                    method: 'GET'
                }).success(function (result) {
                    var modal = new Modal(result);
                    modals.push(modal);
                    modal.setButton(modalButton);
                });
            }
        }).on('modelCreated', (e) => {
            var data = $(e.target).data('model-data');
            callback(data);
            modal.modal('hide');
        });
    
    });
}

function createBrowseModal(id, url, callback) {
    
    modal = $('<div class="modal fade in" id="'+id+'" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg" role="document"><div class="modal-content" style="border-radius: 5px;"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Képek</h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Mégsem</button></div></div></div></div>');

    var lastModal = $('body .wrapper>.modal:last');

    if (lastModal.length > 0) {
        lastModal.after(modal);
    } else {
        $('body .wrapper').prepend(modal);
    }   

    modal.off().on('show.bs.modal', function (e) {
        createBrowseModalLoad(url, callback, modal);
    }).on('hidden.bs.modal', function (e) {
        $('#'+id+'').remove();
        if ($('body .wrapper>.modal').length > 0) $('body').addClass('modal-open');
    }).on('click', 'tr', function (e) {
        $(this).find('input.select').iCheck('toggle');
        e.preventDefault();
    }).on('submit', '.box-header form', function (e) {
        createBrowseModalLoad($(this).attr('action')+'?'+$(this).serialize(), callback, modal);
        return false;
    }).on('change', 'select.grid-per-pager', function (e) {
        createBrowseModalLoad($(this).val(), callback, modal);
        return false;
    }).on('click', '.page-item a, .filter-box a, a.fa-sort, a.fa-sort-amount-desc, a.fa-sort-amount-asc', function (e) {
        createBrowseModalLoad($(this).attr('href'), callback, modal);
        e.preventDefault();
    }).on('submit', '.box-header form', function (e) {
        createBrowseModalLoad($(this).attr('action')+'&'+$(this).serialize(), callback, modal);
        return false;
    }).on('click', '.select-item', function () {
        var itemConteiner = $(this).hasClass('data-container') ? $(this) : $(this).closest('.data-container');
        callback(itemConteiner.data());
        modal.modal('hide');
    });

    return modal;
}

function browse_images(callback) {
    var modal = $('#imageBrowser');

    if (modal.length === 0) {
        modal = createBrowseModal('imageBrowser', ckeditorImageBrowseUrl, function (data) {
            callback(
                {
                    description: data.title,
                    source: data.source,
                    max_width: data.width,
                    max_height: data.height,
                    src: '/storage/' + data.path
                }
            );
        });
    }

    modal.modal('show');
}

$('body').on('click', '.grid-popup-link', function(e){
            
    if (!$(this).data('magnif')){
        $(this).data('magnif', true);
        $(this).magnificPopup({"type":'image'});
        e.preventDefault();
        $(this).trigger('click');
        $(document).off('focusin' );
    }
});

$('.sidebar-menu .treeview').each(function () {
    if ($(this).first('.treeview-menu').find('li').length === 0) $(this).remove();
});

class ObjectResize {

	constructor(selector, maxWidth, checkMaxHeight){

        this.maxWidth = maxWidth;
        this.checkMaxHeight = checkMaxHeight ? true : false;
        if (this.maxWidth === 'number') this.maxWidth = parseInt(this.maxWidth);
		this.selectedItems = document.querySelectorAll(selector);
		this._initItems();
		window.addEventListener('resize', evt => this._onResize(evt));
		this._onResize();

	}

	_initItems() {

		this.items = [];

		for (let index = 0; index < this.selectedItems.length; ++index) {

			let iWidth = parseInt(this.selectedItems[index].getAttribute('width'));
			if (isNaN(iWidth)) iWidth = this.selectedItems[index].offsetWidth;

			let iHeight = parseInt(this.selectedItems[index].getAttribute('height'));
			if (isNaN(iHeight)) iHeight = this.selectedItems[index].offsetHeight;

			if (!isNaN(iWidth) && !isNaN(iHeight)) {

				iWidth = parseInt(iWidth, 10);
				iHeight = parseInt(iHeight, 10);

				this.items.push({
					item: this.selectedItems[index],
					ratio: iWidth / iHeight,
					oWidth: iWidth
				});

			}
		}

	}

	_onResize(evt) {
		if (this.width !== window.innerWidth){
			this.width = window.innerWidth;
			this._calcNewSizes();
		}
	}

	_calcNewSizes(){
		for (let index in this.items){
			let parent = this.items[index].item.parentElement;
			let parentWidth = parent.offsetWidth;
			let parentPaddingLeft = parseInt(window.getComputedStyle(parent, null).getPropertyValue('padding-left'))
            let parentPaddingRight = parseInt(window.getComputedStyle(parent, null).getPropertyValue('padding-right'))

            let parentHeight = parent.offsetHeight;
			let parentPaddingTop = parseInt(window.getComputedStyle(parent, null).getPropertyValue('padding-top'))
            let parentPaddingBottom = parseInt(window.getComputedStyle(parent, null).getPropertyValue('padding-bottom'))

			parentWidth = parentWidth - parseFloat(parentPaddingLeft);
            parentWidth = parentWidth - parseFloat(parentPaddingRight);

            parentHeight = parentHeight - parseFloat(parentPaddingTop);
            parentHeight = parentHeight - parseFloat(parentPaddingBottom);
            
            if (typeof this.maxWidth === 'number' && parentWidth > this.maxWidth) parentWidth = this.maxWidth;

            let height = Math.round(parentWidth / this.items[index].ratio);
            let width = parentWidth;

            if (this.checkMaxHeight) {
                if (parentHeight < height) {
                    let diff = parentHeight / height;
                    height = parentHeight;
                    width = width * diff;
                }
            }

			this.items[index].item.setAttribute('width', width);
			this.items[index].item.setAttribute('height', height);

		}
	}
}
