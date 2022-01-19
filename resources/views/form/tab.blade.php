<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">

        @foreach($tabObj->getTabs() as $tab)
            <li {{ $tab['active'] ? 'class=active' : '' }}>
                <a href="#tab-{{ $tab['id'] }}" data-toggle="tab">
                    {{ $tab['title'] }} <i class="fa fa-exclamation-circle text-red hide"></i>
                </a>
            </li>
        @endforeach

    </ul>
    <div class="tab-content fields-group">

        @foreach($tabObj->getTabs() as $tab)
            <div class="tab-pane {{ $tab['active'] ? 'active' : '' }}" id="tab-{{ $tab['id'] }}">

                @if(empty($tab['columns']))
                    @foreach($tab['fields'] as $field)
                        {!! $field->render() !!}
                    @endforeach
                @else
                    <div class="row">
                        @foreach($tab['columns'] as $column)
                            <div class="col-lg-{{ $column->width() }}">
                                @foreach($column->fields() as $field)
                                    {!! $field->render() !!}
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

    </div>
</div>