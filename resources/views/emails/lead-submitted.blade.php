<!doctype html>
<html lang="ru">
    <body>
        <h3>Новая заявка с сайта Real Brick</h3>

        <p>
            <strong>Имя:</strong>
            {{ $lead->name }}
        </p>
        <p>
            <strong>Телефон:</strong>
            {{ $lead->phone }}
        </p>
        @if (!empty($lead->comment))
            <p>
                <strong>Комментарий:</strong>
                {{ $lead->comment }}
            </p>
        @endif

        <p style="color:#666">
            Дата: {{ $lead->created_at?->format('d.m.Y H:i') }}
        </p>
    </body>
</html>

