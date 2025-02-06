<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    @foreach($get_points as $get_point)
        <p>{{ $get_point->team_no }}ー{{ $get_point->point_no }}</p>
    @endforeach
        バグあり！！
</body>
</html>