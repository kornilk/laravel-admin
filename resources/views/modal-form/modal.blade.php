<div id="{{$id}}" class="modal fade" role="dialog">
    {!! Encore\Admin\Facades\Modal::style() !!}
    @yield('content')
    {!! Encore\Admin\Facades\Modal::script() !!}
</div>
