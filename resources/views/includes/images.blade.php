<div id="{{ isset($imgFeatured) ? 'imagesFlexFeatured' : 'imagesFlex' }}" class="flex-images d-block">
	@foreach ($images as $image)

		@php
			$colors = explode(",", $image->colors);
			$color = $colors[0];

			if ($image->extension == 'png') {
				$background = 'background: url('.url('public/img/pixel.gif').') repeat center center #e4e4e4;';
			}  else {
				$background = 'background-color: #'.$color.';';
			}

			$stockImage = $image->stock->where('type', 'medium')->first();
			$resolution = explode('x', Helper::resolutionPreview($stockImage->resolution));
			$newWidth   = $resolution[0];
			$newHeight  = $resolution[1];
		@endphp


<a class="item hovercard" data-w="{{$newWidth}}" data-h="{{$newHeight}}" href="{{ url('photo', $image->id ) }}/{{str_slug($image->title)}}" style="{{$background}}">
	@if ($image->item_for_sale == 'sale')
		<small class="premium-crown">
			<i class="fa fa-crown text-warning" title="{{__('misc.premium')}}"></i>
		</small>
	@endif

		<!-- hover-content -->
		<span class="hover-content">
			<span class="text-truncate title-hover-content" title="{{$image->title}}">
				@if ($image->featured == 'yes') <i class="bi bi-award" title="{{__('misc.featured')}}"></i> @endif
				</span>

			<div class="sub-hover d-flex align-items-center">
				<div class="flex-shrink-0">
				  <div class="bg-light d-flex align-items-center justify-content-center rounded-circle avatarUser" style="width: 32px; height: 32px;">
				    <i class="bi bi-person text-muted"></i>
				  </div>
				</div>
				<div class="flex-grow-1 ms-3 text-truncate">
					<span class="d-block w-100 text-truncate">{{$image->title}}</span>
					<span class="me-2 d-block w-100 text-truncate">{{ __('misc.by') }} {{$image->author->name ?: $image->author->username}}</span>
				</div>

			  </div>

		</span><!-- hover-content -->

			<img alt="{{ $image->title }}" @if (! request()->ajax())class="previewImage d-none"@endif sizes="580px" srcset="{{ url('files/preview/'.$stockImage->resolution, $stockImage->name)}}?size=small&type={{ $image->item_for_sale }} 280w, {{ url('files/preview/'.$stockImage->resolution, $stockImage->name)}}?size=medium&type={{ $image->item_for_sale }} 480w" src="{{ url('files/preview/'.$stockImage->resolution, $stockImage->name) }}" />
		</a>
		@endforeach
	</div><!-- flex-images -->

	@if ($images->count() && request()->ajax())
	@include('includes.pagination-links')
@endif
