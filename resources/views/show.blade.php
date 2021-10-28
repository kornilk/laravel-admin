<div class="row detail-container">
    <div class="col-md-12">
        {!! $panel !!}
    </div>

    <div class="col-md-12">
        @foreach($relations as $relation)
            {!!  $relation->render() !!}
        @endforeach
    </div>
</div>