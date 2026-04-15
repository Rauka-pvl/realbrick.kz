<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'title' => 'Кирпич ручной формовки: Почему это выбор будущего?',
                'excerpt' => 'Разбираем, почему текстура и геометрия ручной формовки остаются актуальными в современной архитектуре.',
                'topic' => 'Производство',
                'published_at' => Carbon::parse('2026-04-04 10:00:00'),
                'sort_order' => 1,
            ],
            [
                'title' => '5 трендов в дизайне фасадов 2024 года',
                'excerpt' => 'Разбираем тенденции текстур и колористики для современных фасадов.',
                'topic' => 'Дизайн фасадов',
                'published_at' => Carbon::parse('2026-04-03 10:00:00'),
                'sort_order' => 2,
            ],
            [
                'title' => 'Лофт в интерьере: используем кирпичную фактуру',
                'excerpt' => 'Как правильно сочетать акцентную стену из кирпича со светом и металлом.',
                'topic' => 'Интерьеры',
                'published_at' => Carbon::parse('2026-04-02 10:00:00'),
                'sort_order' => 3,
            ],
            [
                'title' => 'История Real Brick: От идеи до первого шоурума',
                'excerpt' => 'Ключевые этапы развития бренда и первые реализованные проекты.',
                'topic' => 'История',
                'published_at' => Carbon::parse('2026-04-01 10:00:00'),
                'sort_order' => 4,
            ],
            [
                'title' => 'Секреты баварской кладки',
                'excerpt' => 'Тонкости подбора оттенков кирпича для выразительного рисунка фасада.',
                'topic' => 'Дизайн фасадов',
                'published_at' => Carbon::parse('2026-03-31 10:00:00'),
                'sort_order' => 5,
            ],
            [
                'title' => 'Экологичность материалов',
                'excerpt' => 'Почему керамика остаётся надёжным и безопасным решением на годы.',
                'topic' => 'Интерьеры',
                'published_at' => Carbon::parse('2026-03-30 10:00:00'),
                'sort_order' => 6,
            ],
            [
                'title' => 'Архитектурная подсветка фасада',
                'excerpt' => 'Как свет раскрывает глубину кирпичной текстуры в вечернее время.',
                'topic' => 'Дизайн фасадов',
                'published_at' => Carbon::parse('2026-03-29 10:00:00'),
                'sort_order' => 7,
            ],
            [
                'title' => 'Как выбрать фактуру кирпича под проект',
                'excerpt' => 'Сравниваем гладкую и рельефную фактуру для разных типов объектов.',
                'topic' => 'Производство',
                'published_at' => Carbon::parse('2026-03-28 10:00:00'),
                'sort_order' => 8,
            ],
        ];

        foreach ($rows as $row) {
            BlogPost::updateOrCreate(
                ['slug' => Str::slug($row['title'])],
                [
                    'title' => $row['title'],
                    'excerpt' => $row['excerpt'],
                    'content' => null,
                    'topic' => $row['topic'],
                    'image_url' => null, // временно: показываем "Нету Фото"
                    'published_at' => $row['published_at'],
                    'is_published' => true,
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}

