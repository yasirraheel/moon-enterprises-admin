@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('admin.subcategories') }} ({{$totalSubcategoriesCategories}})</span>

			<a href="{{ url('panel/admin/subcategories/add') }}" class="btn btn-sm btn-dark float-lg-end mt-1 mt-lg-0">
				<i class="bi-plus-lg"></i> {{ __('misc.add_new') }}
			</a>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

			@if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($totalSubcategoriesCategories !=  0)
                  <tr>
                     <th class="active">ID</th>
                     <th class="active">{{ __('admin.name') }}</th>
                     <th class="active">{{ __('misc.category') }}</th>
                     <th class="active">{{ __('admin.start_date') }}</th>
                     <th class="active">{{ __('admin.start_time') }}</th>
                     <th class="active">{{ __('admin.close_date') }}</th>
                     <th class="active">{{ __('admin.close_time') }}</th>
                     <th class="active">{{ __('admin.status') }}</th>
                     <th class="active">{{ __('admin.actions') }}</th>
                   </tr>

                 @foreach ($subcategories as $subcategory)
                   <tr>
                     <td>{{ $subcategory->id }}</td>
                     <td>{{ $subcategory->name ?? '-' }}</td>
                     <td>{{ $subcategory->category->name ?? '-' }}</td>
                     <td>{{ $subcategory->start_date ? \Carbon\Carbon::parse($subcategory->start_date)->format('M d, Y') : '-' }}</td>
                     <td>{{ $subcategory->start_time ? \Carbon\Carbon::parse($subcategory->start_time)->format('H:i') : '-' }}</td>
                     <td>{{ $subcategory->close_date ? \Carbon\Carbon::parse($subcategory->close_date)->format('M d, Y') : '-' }}</td>
                     <td>{{ $subcategory->close_time ? \Carbon\Carbon::parse($subcategory->close_time)->format('H:i') : '-' }}</td>
                     <td><span class="badge bg-{{ $subcategory->mode == 'on' ? 'success' : 'danger' }}">{{ ucfirst($subcategory->mode) }}</span></td>
                     <td>
                       <a href="{{ url('panel/admin/subcategories/edit/').'/'.$subcategory->id }}" class="btn btn-success rounded-pill btn-sm me-2">
                         <i class="bi-pencil"></i>
                       </a>

                      <form method="POST" action="{{ url('panel/admin/subcategories/delete', $subcategory->id) }}" accept-charset="UTF-8" class="d-inline-block align-top">
                        @csrf
                        <button class="btn btn-danger rounded-pill btn-sm actionDelete" type="button"><i class="bi-trash-fill"></i></button>
                        </form>

										</td>
                   </tr><!-- /.TR -->
                   @endforeach

									@else
										<h5 class="text-center p-5 text-muted fw-light m-0">{{ __('misc.no_results_found') }}</h5>
									@endif

								</tbody>
								</table>
							</div><!-- /.box-body -->

				 </div><!-- card-body -->
 			</div><!-- card  -->

       @if ($subcategories->lastPage() > 1)
       {{ $subcategories->onEachSide(0)->links() }}
     @endif
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection
