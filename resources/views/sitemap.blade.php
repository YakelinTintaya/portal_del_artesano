{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Página de Inicio (Home) -->
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Catálogo General de Obras Artesanales -->
    <url>
        <loc>{{ route('catalogo.index') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Obras Artesanales Individuales -->
    @foreach($productos as $prod)
        <url>
            <loc>{{ route('catalogo.show', $prod->id) }}</loc>
            <lastmod>{{ $prod->updated_at ? $prod->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
            @if($prod->imagen_url)
                <image:image>
                    <image:loc>{{ $prod->imagen_url }}</image:loc>
                    <image:title>{{ $prod->nombre_prod }}</image:title>
                    <image:caption>{{ $prod->descripcion ?? $prod->nombre_prod }}</image:caption>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>
