<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>


<p>team_no : {{ $team_no }}</p>
@foreach ($get_points as $get_point)
    <p>id : {{ $get_point->id }}</p>
    <p>team_no : {{ $get_point->team_no }}</p>
    <p>point_no : {{ $get_point->point_no }}</p>
    <p>point_image : {{ $get_point->photo_filename }}</p>
    <hr>
  @endforeach

  <a href="{{ route('set_point') }}">戻る</a>
</body>
</html>