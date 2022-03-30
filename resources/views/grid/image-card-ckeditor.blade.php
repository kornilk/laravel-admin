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

                <div data-picture={{ json_encode($row->picture) }} data-path="{{ \Storage::disk(config('admin.upload.disk'))->url($row->original) }}" data-title="{{ $row->title }}" data-source="{{ $row->source }}" data-width="{{ $row->width }}" data-height="{{ $row->height }}" class="imageBox data-container">
                    <div class="imageBoxContent">
                        <span class="imageCont">
                            {!! $row->path !!}
                        </span>
                        <div class="imageBoxFooter">
                            <div class="title">
                                {!! $row->title !!}
                            </div>
                            
                        </div>
                    </div>
                    <div class="btn btn-small btn-success select select-item"><i class="fa fa-plus-circle" aria-hidden="true"></i></div>
                </div>
            @endforeach
        </div>

    </div>
    <div class="box-footer clearfix">
        {!! $grid->paginator() !!}
    </div>
    <!-- /.box-body -->
</div>
