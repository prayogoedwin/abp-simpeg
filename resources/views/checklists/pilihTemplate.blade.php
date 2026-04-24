<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Template Checklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0ebf8;
            font-family: 'Roboto', Arial, sans-serif;
        }

        .form-container {
            max-width: 640px;
            margin: 30px auto;
        }

        .card {
            border-radius: 8px;
            border: 1px solid #dadce0;
            margin-bottom: 12px;
        }

        .card-header-google {
            height: 10px;
            background-color: #673ab7;
            border-radius: 8px 8px 0 0;
        }

        .main-title {
            border-top: 10px solid #673ab7;
        }

        .question-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .btn-submit {
            background-color: #673ab7;
            color: white;
            border: none;
            padding: 8px 24px;
        }

        .btn-submit:hover {
            background-color: #512da8;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container form-container">
        <form action="{{ route('checklist.inputdata') }}" method="POST">
            @csrf


            <label for="template_id" class="form-label">Pilih Template</label>
            <select id="template_id" name="template_id" class="form-select border-0 border-bottom rounded-0" required>
                <option value="" disabled selected>Pilih Template</option>
                @foreach($templates as $template)
                <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary mt-3">Lanjutkan</button>
        </form>
    </div>

</body>

</html>