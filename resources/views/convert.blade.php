<form action="{{route('formatter')}}" method="POST" enctype="multipart/form-data">
@csrf
<div>
    @if(\Illuminate\Support\Facades\Session::has('message'))
        {{\Illuminate\Support\Facades\Session::get('message')}}
    @endif
        <div>
        <button type="submit">Procesar</button>
    </div>
</div>
</form>
