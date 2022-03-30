
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
        }).on('formResponse', (e) => {
            var data = $(e.target).data('model-data');
            callback(data);
            modal.modal('hide');
        });
    
    });
}

function createBrowseModal(id, url, callback, title) {
    
    modal = $('<div class="modal fade in" id="'+id+'" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg" role="document"><div class="modal-content" style="border-radius: 5px;"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">'+title+'</h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Mégsem</button></div></div></div></div>');

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
            callback(data);
        }, 'Képek');
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


window.mobileCheck = function() {
    let check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
  };

//ADMIN LTE

$(".sidebar-menu li a").on('click', function() {
    if (window.mobileCheck() && ($(this).attr('href') !== '' && $(this).attr('href') !== '#')) $('.sidebar-toggle').click();
});