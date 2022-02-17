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

            @php
                $picture = config('image.ckeditorPicture');
                
                if (!$picture) {
                    $thumbs = [];
                    $picture = [];

                    $configThumbs = config('image.thumbnails');

                    foreach ($configThumbs as $key => $value) {
                        if (!is_null($value[1])) continue;
                        $thumbs[$value[0]] = $key;
                    }
                    ksort($thumbs);
                    $minSize = array_key_first($thumbs);
                    $picture['default'] = reset($thumbs);

                    unset($thumbs[$minSize]);

                    $sources = [];

                    foreach ($thumbs as $size => $name){
                        $sources[$minSize+1] = $name;
                        $minSize = $size;
                    }

                    $picture['sources'] = $sources;

                    $maxSize = $configThumbs[$picture['sources'][array_key_last($picture['sources'])]][0];

                    if ($maxSize < config('image.maxSize')) {
                        $picture['sources'][$maxSize+1] = '';
                    }

                }

                function getImage($thumb = '', $row) {
                        if (empty($thumb)) {

                            return [
                                'path' => \Storage::disk(config('admin.upload.disk'))->url($row->original),
                                'width' => $row->width,
                                'height' => $row->height,
                            ];

                        } else {
                            $formats = json_decode($row->formats);
                            $return = $formats->{$thumb};
                            $return->path = \Storage::disk(config('admin.upload.disk'))->url($return->path);
                            return $return;
                        }
                    }
            @endphp

            @foreach ($grid->rows() as $row)
                
                @php

                    $p = [
                        'default' => getImage($picture['default'], $row),
                        'sources' => [],
                    ];
                  
                    foreach ($picture['sources'] as $key => $value){
                        $p['sources'][$key] = getImage($value, $row);
                    }
                   
                @endphp

                <div data-picture={{ json_encode($p) }} data-path="{{ \Storage::disk(config('admin.upload.disk'))->url($row->original) }}" data-title="{{ $row->title }}" data-source="{{ $row->source }}" data-width="{{ $row->width }}" data-height="{{ $row->height }}" class="imageBox data-container">
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
