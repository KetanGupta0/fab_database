<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Warning</title>
</head>
<body>
    @if(Session::has('images'))
        {{Session::get('images')}}
    @endif
    <h1>Test Page</h1>

    <form method="post" action="{{ url('imageUpload') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="image">Choose an image to upload:</label>
            <input type="file" class="form-control-file" id="image" name="simage[]" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</body>
</html>