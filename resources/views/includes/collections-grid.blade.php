@foreach ($data as $collection)

@php
$image = $collection->collectionImages->take(3);
$totalImages = $collection->collectionImages->count();
@endphp

<div class="col-md-4 mb-3 text-truncate">
  <a href="{{ url($collection->creator->username.'/collection', $collection->id) }}"
    class="position-relative text-decoration-none">
    <div class="wrap-collection">
      <div class="grid-collection">
        <div class="collection-1">
          @if (isset($image[0]))
          <img role="presentation" class="img-collection"
            src="{{ url('files/preview/'.$image[0]->stockCollection->first()->resolution, $image[0]->stockCollection->first()->name)}}?size=medium&type={{ $image[0]->images->item_for_sale }}">
          @endif
        </div><!-- collection-1 -->

        <div class="collection-right">
          <div class="collection-2">
            @if (isset($image[1]))
            <img role="presentation" class="img-collection"
              src="{{ url('files/preview/'.$image[1]->stockCollection->first()->resolution, $image[1]->stockCollection->first()->name)}}?size=medium&type={{ $image[1]->images->item_for_sale }}">
            @endif
          </div>

          <div class="collection-2">
            @if (isset($image[2]))
            <img role="presentation" class="img-collection"
              src="{{ url('files/preview/'.$image[2]->stockCollection->first()->resolution, $image[2]->stockCollection->first()->name)}}?size=medium&type={{ $image[2]->images->item_for_sale }}">
            @endif
          </div>
        </div>

      </div><!-- grid-collection -->
    </div><!-- wrap-collection -->
    <span class="collection-title text-dark mb-1">
      @if ($collection->type == 'private') <i class="fa fa-lock me-1 padlock" data-bs-toggle="tooltip"
        data-bs-placement="top" title="{{__('misc.private')}}"></i> @endif {{$collection->title}}
    </span>

    <small class="d-block w-100 text-muted">
      {{ $totalImages }} {{ trans_choice('misc.images_plural', $totalImages) }} - {{ __('misc.by') }}
      <strong>{{$collection->creator->username}}</strong>
    </small>
  </a>
</div><!-- col-3-->
@endforeach

@if ($data->count() != 0)
<div class="container-paginator" id="linkPagination">
  {{ $data->links() }}
</div>
@endif