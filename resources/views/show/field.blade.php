<div id="{{ $id }}" class="form-group ">
    <label class="col-sm-{{$width['label']}} control-label">{{ $label }}</label>
    <div class="col-sm-{{$width['field']}}">
        @if($wrapped)
        <div class="box box-solid box-default no-margin box-show">
            <!-- /.box-header -->
            <div class="box-body {{ $class }}">
                @if($escape)
                    {{ $content ? $content : '&nbsp;' }}
                @else
                    {!! $content ? $content : '&nbsp;' !!}
                @endif
            </div><!-- /.box-body -->
        </div>
        @else
            @if($escape)
                {{ $content }}
            @else
                {!! $content !!}
            @endif
        @endif
    </div>
</div>