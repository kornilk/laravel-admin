<div class="relation-selectable-{{ $name }} {{ $viewClass['form-group'] }} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{ $id }}" class="{{ $viewClass['label'] }} control-label">{{ $label }}</label>

    <div class="{{ $viewClass['field'] }}">

        @include('admin::form.error')

        <input type="hidden" name="{{$name}}[]" />
    
        <div class="belongs-relation-select">
            <select class="form-control {{ $class }}" style="width: 100%;" name="{{ $name }}[]" multiple="multiple" {!! $attributes !!}>
                @if ($groups)
                    @foreach ($groups as $group)
                        <optgroup label="{{ $group['label'] }}">
                            @foreach ($group['options'] as $select => $option)
                                <option value="{{ $select }}" {{ in_array($select, (array)old($column, $value)) ?'selected':'' }}>{{ $option }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @else
                    @foreach ($options as $select => $option)
                        <option value="{{ $select }}" {{ in_array($select, (array)old($column, $value)) ?'selected':'' }}>{{ $option }}</option>
                    @endforeach
                @endif
            </select>
            @if($modalButton)
            <div class="belongs-relation-select-button">
                {!! $modalButton !!}
            </div>
            @endif
           
        </div>

        @include('admin::form.help-block')

    </div>
</div>
