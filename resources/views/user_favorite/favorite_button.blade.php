@if(Auth::user()->is_favoriting($micropost->id))
    {!! Form::open(['route' => ['user.unfavorite', $micropost->id], 'method' => 'delete']) !!}
        {!! Form::submit('★', ['class' => "btn  text-warning"]) !!}
    {!! Form::close() !!}
@else
    {!! Form::open(['route' => ['user.favorite', $micropost->id], 'method' => 'store']) !!}
        {!! Form::submit('☆', ['class' => "btn text-warning"]) !!}
    {!! Form::close() !!}
@endif