<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = [
        'url',
        'page_title',
        'visit_count',
    ];

    /**
     * Incrementar el contador de visitas para una URL específica
     */
    public static function addVisit(string $url, ?string $pageTitle = null): void
    {
        $pageView = self::firstOrCreate(
            ['url' => $url],
            ['page_title' => $pageTitle, 'visit_count' => 0]
        );

        $pageView->increment('visit_count');
    }

    /**
     * Obtener el contador de visitas para una URL específica
     */
    public static function getVisitCount(string $url): int
    {
        $pageView = self::where('url', $url)->first();
        return $pageView ? $pageView->visit_count : 0;
    }
}
