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
        <p id="whoPlay">Текущий игрок: {{$whoPlay}}</p>

        <table class="table table-bordered">
            @foreach ($board as $row => $cols)
                <tr>
                    @foreach ($cols as $col => $cell)
                        <td class="{{ $cell === 'X' ? 'x' : ($cell === 'O' ? 'o' : '') }}" 
                            data-row="{{ $row }}" 
                            data-col="{{ $col }}">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>

        <button id='resetBut'> Сбросить <button>
    </div>
</body>
<!-- Я НЕНАВИЖУ JS, НО JQ УЧИТЬ ЛЕНЬ. ГОСПОДИ ПОМОГИ --> 
<script>
document.querySelectorAll('td').forEach(cell => {
    cell.addEventListener('click', () => {
        const row = cell.getAttribute('data-row');
        const col = cell.getAttribute('data-col');

        fetch('/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ row, col })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Инет фигня');
            }
            return response.json();
        })
        .then(data => {
            const board = data.board;
            const whoPlay = data.whoPlay;

            document.querySelectorAll('td').forEach(cell => {
                const r = cell.getAttribute('data-row');
                const c = cell.getAttribute('data-col');
                cell.textContent = board[r][c];
            });

        })
        .catch(error => {
            console.error('Братан, тут такое дело:', error);
        });
    });
});

document.getElementById('resetBut').addEventListener('click', () =>{
    fetch('/reset', {
        method: 'GET',
        })
    .then (() => {
        location.reload();
    })
})
</script>

</html>