
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
        modal = createBrowseModal('imageBrowser', '/admin/images-modal/browse', function (data) {
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

class Modal {
    constructor(result) {
        this.$el = $(result);
        this.id = this.$el.attr('id');
        this.init();
    }

    init() {
        this._handleSubmit();
        this._handleReset();
        this._show();
        this._handleHide();
    }

    setButton(modalButton){
        this.$modalButton = modalButton;
        return this;
    }

    _handleSubmit() {
        var form = this.$el.find('form');
        var that = this;
        form.on('submit', function (e) {
            that.$modalButton.trigger('modelCreating');
            e.preventDefault();
            that._clearErrors();
            that._disableButtons();
            that.loading();
            var data = new FormData(this);

            form.find('.ckEditorTextarea').each(function () {
                data.append($(this).attr('name'), $(this).data('editor').getData());
            });

            
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: data,
                processData: false,
                contentType: false, 
            }).error((jqXHR, textStatus, errorThrown) => {
                this.$modalButton.trigger('modelFailed');
                swal(jqXHR.status.toString(), errorThrown, 'error');
            }).success(function (result) {
                if(result.status){
                    toastr.success(result.message);
                    that.$modalButton.data('model-id', result.modelId);

                    if (result.data) {
                        that.$modalButton.data('model-data', result.data);
                    }

                    that.$modalButton.trigger('modelCreated');
                    that.dismiss();
                }else{
                    that.$modalButton.trigger('modelValidationFailed');
                    that._handleErrors(result);
                }
            }).always(function () {
                that._enableButtons();
                that.loading(false);
            });

        });
    }

    loading(isLoading = true){
        if(isLoading){
            this.$loading = $('<div>').addClass('modal-backdrop in').append($("<div>").addClass('editableform-loading').css({
                'position': 'absolute',
                'top': '50%',
                'left': '50%',
                'z-index': '10000'
            }));
            this.$el.find('.modal-dialog').append(this.$loading);
        }else{
            if(this.$loading){
                this.$loading.remove();
            }
        }

    }

    _handleReset(){
        this.$el.find('[type="reset"]').click(()=>{
            this._clearErrors();
        });
    }

    _getButtons() {
        return this.$el.find('.btn');
    }

    _enableButtons() {
        var buttons = this._getButtons();
        buttons.each(function (id, button) {
            let $button = $(button);
            switch ($button.prop('tagName')) {
                case 'A':
                    $button.attr('href', $button.data('data-href'));
                    $button.removeAttr('data-href');
                //intentional break statement missing
                case 'BUTTON':
                    $button.removeAttr('disabled');
                    break;
            }
        });
    }

    _disableButtons() {
        var buttons = this._getButtons();
        buttons.each(function (id, button) {
            let $button = $(button);
            switch ($button.prop('tagName')) {
                case 'A':
                    $button.data('data-href', $button.attr('href'));
                    $button.attr('href', 'javascript:void(0)');
                //intentional break statement missing
                case 'BUTTON':
                    $button.attr('disabled', true);
                    break;
            }
        });

    }

    _handleHide() {
        var that = this;
        this.modal.on('hidden.bs.modal', function () {
            
            var index = modals.findIndex((element)=>{
                return element.id === that.id;
            });
            if(index > -1){
                modals.slice(index, 1);
            }
            that.modal.remove();
            if ($('body .wrapper>.modal').length > 0) $('body').addClass('modal-open');
        });
    }

    _show() {
        this._appendModal();
        this.modal = this.$el.modal({
            backdrop: 'static',
            keyboard: false
        });
    }
    _appendModal(){
        $(document).find('header.main-header').before(this.$el);
    }

    dismiss() {
        this.modal.modal('hide');
    }

    _clearErrors(){
        var formGroups = this.$el.find(".has-error");
        formGroups.each(function (id, formGroup) {
            formGroup = $(formGroup);
            formGroup.removeClass('has-error');
            formGroup.find('[for="inputError"]').each((key, inputError)=>{
                $(inputError).siblings('br').remove();
            }).remove();
        });
    }

    _handleErrors(result){
        var that = this;
        if(result.hasOwnProperty('validation')){
            var messages = result.validation;
            for (var [key, message] of Object.entries(messages)){
                var input = that.$el.find(`[name="${key}"]`);
                if(input){
                    var formInput = new FormInput(input);
                    formInput.showMessage(message);
                }
            }
        }
    }
}

class FormInput{
    constructor(input) {
        this.$el = input
        this.formGroup = this.$el.closest('.form-group');
        this.inputGroup = this.formGroup.find('.input-group');
    }

    showMessage(message){
        this.formGroup.addClass('has-error');
        if(Array.isArray(message)){
            message.forEach((error)=>{
                this.inputGroup.before(this._getErrorLabel(error))
            });
        }
    }

    _getErrorLabel(error){
        return `<label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> ${error}</label><br/>`;
    }
}