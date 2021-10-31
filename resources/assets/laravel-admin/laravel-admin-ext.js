
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
                    src: '/storage/uploads/' + data.path
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