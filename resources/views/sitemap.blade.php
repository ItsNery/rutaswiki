<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc>{{ route('cities.index') }}</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc>{{ route('map') }}</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc>{{ route('search') }}</loc>
        <priority>0.5</priority>
        <changefreq>weekly</changefreq>
    </url>
    @foreach($cities as $city)
    <url>
        <loc>{{ route('cities.show', $city) }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $city->updated_at->tz('UTC')->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($routes as $route)
    <url>
        <loc>{{ route('routes.show', [$route->city, $route]) }}</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $route->updated_at->tz('UTC')->toW3cString() }}</lastmod>
    </url>
    @endforeach
</urlset>
