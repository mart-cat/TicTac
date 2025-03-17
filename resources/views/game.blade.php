<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <title>Крестики-нолики</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body>
    <div class='container'>
        <h1 class="text-center">Сыграешь?</h1>
        <p>Игрок X: {{ $room->player_x ? 'В игре' : 'Ожидание игрока' }}</p>
        <p>Игрок O: {{ $room->player_o ? 'В игре' : 'Ожидание игрока' }}</p>

        <p>Ты играешь за: <span id="playerSymbol">{{ $playerSymbol ?? 'Неизвестно' }}</span></p>

        <p>Текущий игрок: <span id="whoPlay">{{$turn}}</span></p>


        <table class="table table-bordered">
            @foreach ($board as $row => $cols)
                <tr>
                    @foreach ($cols as $col => $cell)
                        <td class="{{ $cell === 'X' ? 'x' : ($cell === 'O' ? 'o' : '') }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
        <p>Победитель: <span id="winner"> {{$winner}}</span></p>
        <button id='resetBut'> Сбросить </button>

    </div>
</body>
<!-- Я НЕНАВИЖУ JS, НО JQ УЧИТЬ ЛЕНЬ. ГОСПОДИ ПОМОГИ -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        fetchPlayerSymbol();
        setInterval(fetchGameState, 2000);
    });

    function fetchPlayerSymbol() {
        fetch('/get-player-symbol')
            .then(response => response.json())
            .then(data => {
                document.getElementById('playerSymbol').textContent = data.player_symbol || 'Неизвестно';
            })
            .catch(error => console.error("Ошибка получения символа игрока:", error));
    }

    function fetchGameState() {
        fetch('/game-state')
            .then(response => response.json())
            .then(data => {
                updateState(data);
            })
            .catch(error => console.error("Ошибка обновления игры:", error));
    }

    function updateState(data) {
        if (!data || !data.board) return;

        document.querySelectorAll('td').forEach(cell => {
            const r = cell.getAttribute('data-row');
            const c = cell.getAttribute('data-col');
            cell.textContent = data.board[r][c];
            cell.className = data.board[r][c] === 'X' ? 'x' : (data.board[r][c] === 'O' ? 'o' : '');
        });

        document.getElementById('whoPlay').textContent = data.turn;
        document.getElementById('winner').textContent = data.winner || '';

        if (data.winner) {
            document.querySelectorAll('td').forEach(cell => cell.removeEventListener('click', makeMove));
        }
    }

    function makeMove(event) {
        const cell = event.target;
        if (cell.textContent !== '') return; // Не даем кликать на занятые клетки

        const row = cell.getAttribute('data-row');
        const col = cell.getAttribute('data-col');

        fetch('/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ row, col })
        })
        .then(response => response.json())
        .then(data => updateState(data))
        .catch(error => console.error('Ошибка при выполнении хода:', error));
    }

    document.querySelectorAll('td').forEach(cell => cell.addEventListener('click', makeMove));

    document.getElementById('resetBut').addEventListener('click', () => {
        fetch('/reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else console.error('Ошибка при сбросе игры:', data.error);
        })
        .catch(error => console.error('Ошибка при сбросе игры:', error));
    });

</script>



</html>