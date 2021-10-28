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
        <div class="row imageBoxContainer">

            @foreach ($grid->rows() as $row)

                <div class="imageBox">
                    <div class="imageBoxContent">
                        <span class="imageCont">
                            {!! $row->path !!}
                        </span>
                        <div class="imageBoxFooter">
                            <div class="title">
                                {!! $row->title !!}
                            </div>
                            <div class="actions">
                                {!! $row->column('__row_selector__') !!}
                                <span class="pull-right">
                                    {!! $row->column('__actions__') !!}
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
