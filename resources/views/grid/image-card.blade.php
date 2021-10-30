<div class="box">
    @if (isset($title))
        <div class="box-header with-border">
            <h3 class="box-title"> {{ $title }}</h3>
        </div>
    @endif

    <div class="box-header with-border">
        <div class="pull-right">
            {!! $grid->renderExportButton() !!}
            {!! $grid->renderCreateButton() !!}
        </div>
        <span>
            {!! $grid->renderHeaderTools() !!}
        </span>
    </div>

    {!! $grid->renderFilter() !!}

    <!-- /.box-header -->
    <div class="box-body">
        <div class="row imageBoxContainer selectable-container" id="{{ $grid->tableID }}">
            @if($grid->rows()->isEmpty() && $grid->showDefineEmptyPage())
                @include('admin::grid.empty-grid', [
                    'notTable' => true
                ])
            @endif
            @foreach ($grid->rows() as $row)

                <div class="imageBox cursor-pointer selectable-item" {!! $row->getRowAttributes() !!}>
                    <div class="imageBoxContent">
                        <span class="imageCont">
                            {!! $row->path !!}
                        </span>
                        <div class="imageBoxFooter">
                            <div class="title">
                                {!! $row->title !!}
                            </div>
                            <div class="actions">
                                <span class="pull-right">
                                    {!! $row->column('__actions__') !!}
                                    <span class="column-__modal_selector__">{!! $row->column('__modal_selector__') !!}</span>
                                    {!! $row->column('__remove__') !!}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
    <div class="box-footer clearfix">
        {!! $grid->paginator() !!}
    </div>
    <!-- /.box-body -->
</div>
