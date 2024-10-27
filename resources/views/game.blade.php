<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <title>Крестики-нолики</title>
</head>

<body>
    <div class='container'>
        <h1 class="text-center">Сыграешь?</h1>
        <table class="table table-bordered">
            @foreach ($board as $row => $cols)
                <tr>
                    @foreach ($cols as $col => $cell)
                        <td class="{{ $cell === 'x' ? 'x' : ($cell === '0' ? 'o' : '') }}" 
                            data-row="{{ $row }}" 
                            data-col="{{ $col }}">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
</body>

</html>
