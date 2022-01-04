<div class="form-group {{ $hide ? 'hidden' : '' }}">
    <label class="col-sm-2 control-label"> {{$label}}</label>
    <div class="col-sm-8">
        @include($presenter->view())
    </div>
</div>