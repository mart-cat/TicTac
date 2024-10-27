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
        <p >Текущий игрок: <span id="whoPlay">{{$whoPlay}}</span></p>

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
        <p>Победитель: <span id="winner"> {{$winner}}</span></p>
        <button id='resetBut'> Сбросить <button>
    </div>
</body>
<!-- Я НЕНАВИЖУ JS, НО JQ УЧИТЬ ЛЕНЬ. ГОСПОДИ ПОМОГИ --> 
<script>
    let gameOver = false;
    document.querySelectorAll('td').forEach(cell => {
        cell.addEventListener('click', () => {
            
            if (gameOver) return; //выгоняем нафиг если true

            const row = cell.getAttribute('data-row');
            const col = cell.getAttribute('data-col');

            // Отправляем информацию о ходе на сервер
            fetch('/move', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Говорим, что отправляем данные в формате JSON
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Защита от атак
                },
                body: JSON.stringify({ row, col }) // Превращаем наши данные в строку
            })
            .then(response => {
                // Если что-то пошло не так, показываем ошибку
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json(); // Превращаем ответ в JSON
            })
            .then(data => {
                const board = data.board; 
                const whoPlay = data.whoPlay; 
                const winner = data.winner; 

                // Обновляем каждую ячейку на доске
                document.querySelectorAll('td').forEach(cell => {
                    const r = cell.getAttribute('data-row'); // Получаем номер строки
                    const c = cell.getAttribute('data-col'); // Получаем номер столбца
                    cell.textContent = board[r][c]; // Заполняем ячейку символом (X или O)
                    // Меняем класс ячейки для стилизации
                    cell.className = board[r][c] === 'X' ? 'x' : (board[r][c] === 'O' ? 'o' : '');
                    //. ? 'x' : ...:  тернарный оператор "если... то... иначе".
                });


                document.getElementById('whoPlay').textContent = whoPlay; 
                document.getElementById('winner').textContent = winner || ''; 


                if (winner) {
                    gameOver = true; 
                }
            })
            .catch(error => {
                console.error('Произошла ошибка:', error); 
            });
        });
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
</script>


</html>