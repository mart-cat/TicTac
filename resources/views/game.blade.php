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
        <p>Игрок X: {{$room->player_x ?? 'Ожидание игрока'}}</p>
        <p>Игрок O: {{$room->player_o ?? 'Ожидание игрока'}}</p>

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
        <button id='resetBut'> Сбросить <button>
    </div>
</body>
<!-- Я НЕНАВИЖУ JS, НО JQ УЧИТЬ ЛЕНЬ. ГОСПОДИ ПОМОГИ -->
<script>


    function updateState(data) {
        const board = data.board;
        const whoPlay = data.turn; // теперь сюда передаем текущего игрока
        const winner = data.winner;

        // Обновляем таблицу
        document.querySelectorAll('td').forEach(cell => {
            const r = cell.getAttribute('data-row');
            const c = cell.getAttribute('data-col');
            cell.textContent = board[r][c];
            cell.className = board[r][c] === 'X' ? 'x' : (board[r][c] === 'O' ? 'o' : '');
        });

        // Обновляем состояние текущего игрока
        document.getElementById('whoPlay').textContent = whoPlay;

        // Если есть победитель, показываем его
        document.getElementById('winner').textContent = winner || '';

        // Блокируем все клетки, если есть победитель
        if (winner) {
            document.querySelectorAll('td').forEach(cell => {
                cell.removeEventListener('click', Move); // Убираем обработчик клика, если победитель есть
            });
        }
    }


    function Move(event) {
    const cell = event.target;
    const row = cell.getAttribute('data-row');
    const col = cell.getAttribute('data-col');

    // Отправляем запрос на сервер, чтобы выполнить ход
    fetch('/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ row: row, col: col })
    })
    .then(response => response.json())
    .then(data => {
        // Обновляем состояние доски на фронтенде
        updateState(data);
    })
    .catch(error => {
        console.error('Ошибка при выполнении хода:', error);
    });
}



document.querySelectorAll('td').forEach(cell => {
    cell.addEventListener('click', Move);
});

    // Обработчик для кнопки сброса игры
    document.getElementById('resetBut').addEventListener('click', () => {
        fetch('/reset', {
            method: 'GET', // Отправляем запрос на сброс игры
        })
            .then(() => {
                location.reload(); // Перезагружаем страницу, чтобы начать новую игру
            })
            .catch(error => {
                console.error('Произошла ошибка при сбросе игры:', error);
            });
    });

    document.addEventListener("DOMContentLoaded", function () {
        fetch('/get-player-symbol') // Новый маршрут, который мы сейчас добавим
            .then(response => response.json())
            .then(data => {
                document.getElementById('playerSymbol').textContent = data.player_symbol || 'Неизвестно';
            })
            .catch(error => console.error("Ошибка получения символа игрока:", error));
    });
</script>

</html>