<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Калькулятор рассрочки</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://api.bitrix24.com/api/v1"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

       {{-- Laravel Mix - CSS File --}}
       {{-- <link rel="stylesheet" href="{{ mix('css/perspektivakrym.css') }}"> --}}

    </head>
    <body>
        <div style="padding: 10px">
            @yield('content')
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
        {{-- Локальные JS файлы --}}
        {{-- <script src="{{ asset('js/datepicker_ru.js') }}"></script> --}}
        {{-- <script src="{{ asset('js/perspektivakrym/app.js?v=1.3.2') }}"></script> --}}
        
        <script>
        $(document).ready(function() {
            // Обработчик для кнопки "Пересчитать"
            $('.recalculate').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно подтверждения
                $('#recalculate').modal('show');
            });
            
            // Обработчик для кнопки "Добавить плановый платеж"
            $('.add-plan-pay').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно добавления платежа
                $('#add-plan-pay').modal('show');
            });
            
            // Обработчик для кнопки "Заморозить все"
            $('.freeze-all').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно заморозки
                $('#block-plan-pay').modal('show');
            });
            
            // Обработчик для кнопки "Разморозить все"
            $('.unfreeze-all').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно разморозки
                $('#unblock-plan-pay').modal('show');
            });
            
            // Обработчик для кнопки "Основной график"
            $('.get-pdf-land').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно для PDF
                $('#get-pdf-land').modal('show');
            });
            
            // Обработчик для кнопки "График по подряду"
            $('.get-pdf-contract').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно для PDF
                $('#get-pdf-contract').modal('show');
            });
        });
        </script>
    </body>
</html>
