<?php $date = Carbon\Carbon::yesterday()->format('Y-m-d'); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
      <url>
         <loc>{{ url('/') }}</loc>
         <lastmod>{{$date}}</lastmod>
         <priority>0.8</priority>
      </url>

      <url>
            <loc>{{ url('members') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         <url>
            <loc>{{ url('collections') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         @foreach (App\Models\Collections::has('collectionImages')->where('type','public')->orderBy('id','desc')->get() as $collection)
            <url>
                  <loc>{{ url($collection->user()->username.'/collection', $collection->id) }}</loc>
                  <lastmod>{{$date}}</lastmod>
                  <priority>0.8</priority>
            </url>
        @endforeach

         @if (Plans::whereStatus('1')->count() != 0 && $settings->sell_option == 'on')
         <url>
            <loc>{{ url('pricing') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>
         @endif

         @if ($settings->sell_option == 'on')
         <url>
            <loc>{{ url('photos/premium') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>
         @endif

         <url>
            <loc>{{ url('featured') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         <url>
            <loc>{{ url('popular') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         <url>
            <loc>{{ url('latest') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         <url>
            <loc>{{ url('contact') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         <url>
            <loc>{{ url('categories') }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
         </url>

         @foreach (Categories::where('mode', 'on')->get() as $category)
            <url>
            <loc>{{ url('category', $category->slug) }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
            </url>
        @endforeach

        @foreach (App\Models\Subcategories::has('category')->with(['category:id,slug'])->where('mode', 'on')->get() as $subcategory)
            <url>
            <loc>{{ url('category', [$subcategory->category->slug, $subcategory->slug]) }}</loc>
            <lastmod>{{$date}}</lastmod>
            <priority>0.8</priority>
            </url>
        @endforeach

        
   
	@foreach (Pages::all() as $page)
	<url>
         <loc>{{ url('page', $page->slug) }}</loc>
         <lastmod>{{$date}}</lastmod>
         <priority>0.8</priority>
   </url>
 @endforeach
   
	@foreach (User::where('status','active')->get() as $user)
	<url>
         <loc>{{ url($user->username) }}</loc>
         <lastmod>{{$date}}</lastmod>
         <priority>0.8</priority>
   </url>
   @endforeach   
</urlset>