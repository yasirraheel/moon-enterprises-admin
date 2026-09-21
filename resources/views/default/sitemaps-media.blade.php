<?php $date = Carbon\Carbon::yesterday()->format('Y-m-d'); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

  @foreach (Images::whereHas('stock', function($q) {
    $q->where('type', 'medium');
  })
    ->select(['id', 'title', 'thumbnail'])->where('status','active')
    ->latest()
    ->take(50000)
    ->get() 
    as $response)
  <url>
    <loc>{{ url('photo', [$response->id, str_slug($response->title)]) }}</loc>
    <image:image>
      <image:loc>{{ url('files/preview/'.$response->stock[1]->resolution, $response->stock[1]->name) }}</image:loc>
    </image:image>
    <lastmod>{{$date}}</lastmod>
    <priority>0.8</priority>
  </url>
@endforeach
</urlset>