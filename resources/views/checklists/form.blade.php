<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $checklist->nama }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0ebf8;
            padding-top: 50px;
        }

        .form-header {
            border-top: 10px solid #673ab7;
            border-radius: 8px 8px 0 0;
        }

        .card {
            border-radius: 8px;
            border: 1px solid #dadce0;
            margin-bottom: 15px;
        }

        .form-title {
            font-size: 32px;
            font-weight: 400;
        }

        .form-label {
            font-weight: 500;
            font-size: 16px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
                @endif

                <div class="card shadow-sm form-header">
                    <div class="card-body p-4">
                        <h1 class="form-title">{{ $checklist->nama }}</h1>
                        <p class="text-muted">Instansi: {{ $checklist->instansi->nama ?? '-' }}</p>
                        <hr>
                        <small class="text-danger">* Menunjukkan pertanyaan yang wajib diisi</small>
                    </div>
                </div>

                <form action="{{ route('checklist.submit', $checklist->id) }}" method="POST">
                    @csrf

                    @foreach($details as $detail)
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <label class="form-label d-block">{{ $detail->label }} <span class="text-danger">*</span></label>

                            @php
                            // Pecah string options menjadi array (jika ada)
                            $options = array_map('trim', explode(',', $detail->options));
                            @endphp

                            @if($detail->type == 'text')
                            <input type="text" name="answers[{{ $detail->id }}]" class="form-control border-0 border-bottom rounded-0 px-0" placeholder="Jawaban teks singkat" required>

                            @elseif($detail->type == 'number')
                            <input type="number" name="answers[{{ $detail->id }}]" class="form-control border-0 border-bottom rounded-0 px-0" style="width: 200px;" placeholder="Angka" required>

                            @elseif($detail->type == 'date')
                            <input type="date" name="answers[{{ $detail->id }}]" class="form-control border-0 border-bottom rounded-0 px-0" style="width: 200px;" required>

                            @elseif($detail->type == 'select')
                            <select name="answers[{{ $detail->id }}]" class="form-select border-0 border-bottom rounded-0" required>
                                <option value="" disabled selected>Pilih</option>
                                @foreach($options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>

                            @elseif($detail->type == 'radio')
                            @foreach($options as $opt)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="answers[{{ $detail->id }}]" id="radio_{{ $loop->parent->index }}_{{ $loop->index }}" value="{{ $opt }}" required>
                                <label class="form-check-label" for="radio_{{ $loop->parent->index }}_{{ $loop->index }}">{{ $opt }}</label>
                            </div>
                            @endforeach

                            @elseif($detail->type == 'checkbox')
                            @foreach($options as $opt)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="answers[{{ $detail->id }}][]" id="check_{{ $loop->parent->index }}_{{ $loop->index }}" value="{{ $opt }}">
                                <label class="form-check-label" for="check_{{ $loop->parent->index }}_{{ $loop->index }}">{{ $opt }}</label>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="background-color: #673ab7; border: none;">Kirim</button>
                        <button type="reset" class="btn btn-link text-decoration-none text-muted">Kosongkan formulir</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>

</html>