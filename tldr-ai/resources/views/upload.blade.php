<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Document</title>
</head>
<body>
    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    <form action="/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="document" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
