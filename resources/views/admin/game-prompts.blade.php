@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Game Prompts ({{$data->total()}})</span>
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

              @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="bi bi-exclamation-triangle me-1"></i> Please check the form below for errors
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                  </button>
              </div>
              @endif

            <!-- Add New Prompt Form -->
			<div class="card shadow-custom border-0 mb-4">
				<div class="card-header bg-white py-3">
                    <h6 class="mb-0">Add New Prompt</h6>
                </div>
                <div class="card-body p-lg-4">
                    <form method="POST" action="{{ route('admin.game_prompts.store') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-1 col-md-2">
                                <label class="form-label">Ser</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" placeholder="0">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Prompt</label>
                                <input type="text" name="prompt" class="form-control @error('prompt') is-invalid @enderror" placeholder="Enter game prompt..." required>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Num Start</label>
                                <input type="text" name="number_start" class="form-control @error('number_start') is-invalid @enderror" placeholder="Start">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Num End</label>
                                <input type="text" name="number_end" class="form-control @error('number_end') is-invalid @enderror" placeholder="End">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">First Limit</label>
                                <input type="number" step="0.01" name="first_limit" class="form-control @error('first_limit') is-invalid @enderror" placeholder="e.g. 1000">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Second Limit</label>
                                <input type="number" step="0.01" name="second_limit" class="form-control @error('second_limit') is-invalid @enderror" placeholder="e.g. 500">
                            </div>
                            <div class="col-lg-auto">
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi-plus-lg me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
				 </div>
			</div>

            <!-- Prompts List -->
			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($data->total() !=  0)
                 <tr>
                     <th class="active">Ser</th>
                     <th class="active">Prompt</th>
                     <th class="active">Start</th>
                     <th class="active">End</th>
                     <th class="active">First Limit</th>
                     <th class="active">Second Limit</th>
                     <th class="active">{{ __('admin.status') }}</th>
                     <th class="active">{{ __('admin.actions') }}</th>
                   </tr>

                 @foreach ($data as $prompt)
                   <tr>
                     <td>{{ $prompt->sort_order }}</td>
                     <td>{{ $prompt->prompt }}</td>
                     <td>{{ $prompt->number_start }}</td>
                     <td>{{ $prompt->number_end }}</td>
                     <td>{{ is_null($prompt->first_limit) ? '-' : number_format($prompt->first_limit, 2) }}</td>
                     <td>{{ is_null($prompt->second_limit) ? '-' : number_format($prompt->second_limit, 2) }}</td>
                     <td><span class="badge bg-{{ $prompt->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($prompt->status) }}</span></td>
                     <td>
                        <button type="button" class="btn btn-success rounded-pill btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editModal{{$prompt->id}}">
                            <i class="bi-pencil"></i>
                        </button>

                      <form method="POST" action="{{ route('admin.game_prompts.delete', $prompt->id) }}" accept-charset="UTF-8" class="d-inline-block align-top">
                        @csrf
                        <button class="btn btn-danger rounded-pill btn-sm actionDelete" type="button"><i class="bi-trash-fill"></i></button>
                        </form>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{$prompt->id}}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Prompt</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.game_prompts.update') }}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $prompt->id }}">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Ser</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ $prompt->sort_order }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Prompt</label>
                                                <input type="text" name="prompt" class="form-control" value="{{ $prompt->prompt }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Number Start</label>
                                                    <input type="text" name="number_start" class="form-control" value="{{ $prompt->number_start }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Number End</label>
                                                    <input type="text" name="number_end" class="form-control" value="{{ $prompt->number_end }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">First Limit</label>
                                                    <input type="number" step="0.01" name="first_limit" class="form-control" value="{{ $prompt->first_limit }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Second Limit</label>
                                                    <input type="number" step="0.01" name="second_limit" class="form-control" value="{{ $prompt->second_limit }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-dark">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

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

       @if ($data->lastPage() > 1)
       {{ $data->onEachSide(0)->links() }}
     @endif
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection
