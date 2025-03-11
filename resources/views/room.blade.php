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
        <!-- Кнопки для создания комнаты и присоединения -->
        <button id="createRoomBtn">Создать комнату</button>
        <button id="joinRoomBtn">Присоединиться к комнате</button>

        <!-- Поп-ап окно для создания комнаты -->
        <div id="createRoomModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeCreateRoom">&times;</span>
                <h2>Создать комнату</h2>
                <form id="createRoomForm" action="{{ route('create.room') }}" method="POST">
                    @csrf
                    <input type="text" id="createRoomName" name="name" placeholder="Имя комнаты" 
                        value="{{ old('createRoomName', session('createRoomName')) }}" required>
                    <br><br>
                    @error('createRoomName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <input type="password" id="createRoomPassword" name="password" placeholder="Пароль" 
                        value="{{ old('createRoomPassword', session('createRoomPassword')) }}" required>
                    <br><br>
                    @error('createRoomPassword')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <button type="submit">Создать</button>
                </form>
            </div>
        </div>

        <!-- Поп-ап окно для присоединения к комнате -->
        <div id="joinRoomModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeJoinRoom">&times;</span>
                <h2>Присоединиться к комнате</h2>
                <form id="joinRoomForm" action="{{ route('join.room') }}" method="POST">
                    @csrf

                    <input type="text" id="joinRoomName" name="name" placeholder="Имя комнаты" 
                        value="{{ old('joinRoomName', session('joinRoomName')) }}" required>
                    <br><br>
                    @error('joinRoomName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <input type="password" id="joinRoomPassword" name="password" placeholder="Пароль" 
                        value="{{ old('joinRoomPassword', session('joinRoomPassword')) }}" required>
                    <br><br>
                    @error('joinRoomPassword')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    @error('createRoomName')
    <div class="error">{{ $message }}</div>
@enderror
@error('createRoomPassword')
    <div class="error">{{ $message }}</div>
@enderror

                    <button type="submit">Присоединиться</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    document.getElementById('createRoomForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    let formData = new FormData(this);

    fetch("{{ route('create.room') }}", {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect; // Перенаправление в игру
        } else {
            alert("Ошибка: " + (data.error || "Неизвестная ошибка"));
        }
    })
    .catch(error => console.error("Ошибка:", error));
});
document.getElementById('joinRoomForm').addEventListener('submit', function(event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("{{ route('join.room') }}", {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => response.json()) // Преобразуем в JSON
    .then(data => {
        console.log("Ответ сервера:", data); // Вывод в консоль для проверки

        if (data.success) {
            window.location.href = data.redirect; // Перенаправление на игру
        } else {
            alert("Ошибка: " + (data.error || "Неизвестная ошибка"));
        }
    })
    .catch(error => console.error("Ошибка:", error));
});

</script>
</html>
