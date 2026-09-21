@extends('admin.layout')

@section('css')
<style>
    .otp-input {
        width: 100%;
        text-align: center;
        margin-right: 5px;
    }
    /* Dark theme adjustments if not already global */
    .card {
        background-color: #2c3b41; /* Example dark bg */
        color: #fff;
    }
    .card-header {
        background-color: #222d32;
        color: #fff;
        border-bottom: 1px solid #444;
    }
    .form-control {
        background-color: #222d32;
        border: 1px solid #444;
        color: #fff;
    }
    .form-control:focus {
        background-color: #2c3b41;
        color: #fff;
        border-color: #3c8dbc;
    }
    .table {
        color: #fff;
    }
    .table-hover tbody tr:hover {
        color: #fff;
        background-color: #222d32;
    }
    .result-digit {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background-color: #3c8dbc;
        color: white;
        text-align: center;
        margin-right: 2px;
        font-weight: bold;
        font-size: 14px;
    }
    .result-digit-empty {
        background-color: #444;
        color: #888;
    }
</style>
@endsection

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Results ({{ $recentResults->count() + $pastResults->total() }})</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

			@if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success') }}

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

            <!-- Add/Edit Result Form -->
            @php
                $isEdit = request()->has('edit');
                $editData = $isEdit ? \App\Models\Results::find(request('edit')) : null;
                $action = $isEdit ? route('admin.results.update', $editData->id) : route('admin.results.store');
            @endphp

			<div class="card shadow-custom border-0 mb-4">
				<div class="card-header py-3">
                    <h6 class="mb-0">{{ $isEdit ? 'Edit Result' : 'Add New Result' }}</h6>
                    @if($isEdit)
                        <a href="{{ route('admin.results') }}" class="btn btn-sm btn-outline-light float-end" style="margin-top: -25px;">Cancel Edit</a>
                    @endif
                </div>
                <div class="card-body p-lg-4">
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        <div class="row">
                            <!-- Row 1: FD, Date, Time -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fd</label>
                                <input type="text" name="fd" class="form-control" value="{{ old('fd', $editData->fd ?? '') }}" placeholder="Enter Fd" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="result_date" class="form-control" value="{{ old('result_date', $editData->result_date ?? date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Time</label>
                                <input type="time" name="result_time" class="form-control" value="{{ old('result_time', isset($editData->result_time) ? \Carbon\Carbon::parse($editData->result_time)->format('H:i') : date('H:i')) }}" required>
                            </div>

                            <!-- Row 2: 1st, 2nd, 3rd, 4th Prizes -->
                            <!-- 1st Prize -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">1st Prize</label>
                                <div class="d-flex">
                                    @for($i=1; $i<=4; $i++)
                                        <input type="text" name="first_prize_{{$i}}" class="form-control otp-input" maxlength="1"
                                            value="{{ old('first_prize_'.$i, $editData ? substr($editData->first_prize, $i-1, 1) : '') }}" required
                                            onkeyup="moveToNext(this, 'first_prize_{{$i+1}}', '{{$i>1 ? 'first_prize_'.($i-1) : ''}}')">
                                    @endfor
                                </div>
                            </div>

                            <!-- 2nd Prize -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">2nd Prize</label>
                                <div class="d-flex">
                                    @for($i=1; $i<=4; $i++)
                                        <input type="text" name="second_prize_{{$i}}" class="form-control otp-input" maxlength="1"
                                            value="{{ old('second_prize_'.$i, $editData && $editData->second_prize ? substr($editData->second_prize, $i-1, 1) : '') }}"
                                            onkeyup="moveToNext(this, 'second_prize_{{$i+1}}', '{{$i>1 ? 'second_prize_'.($i-1) : ''}}')">
                                    @endfor
                                </div>
                            </div>

                            <!-- 3rd Prize (labeled as 2nd) -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">2nd Prize</label>
                                <div class="d-flex">
                                    @for($i=1; $i<=4; $i++)
                                        <input type="text" name="third_prize_{{$i}}" class="form-control otp-input" maxlength="1"
                                            value="{{ old('third_prize_'.$i, $editData && $editData->third_prize ? substr($editData->third_prize, $i-1, 1) : '') }}"
                                            onkeyup="moveToNext(this, 'third_prize_{{$i+1}}', '{{$i>1 ? 'third_prize_'.($i-1) : ''}}')">
                                    @endfor
                                </div>
                            </div>

                            <!-- 4th Prize (labeled as 2nd) -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">2nd Prize</label>
                                <div class="d-flex">
                                    @for($i=1; $i<=4; $i++)
                                        <input type="text" name="fourth_prize_{{$i}}" class="form-control otp-input" maxlength="1"
                                            value="{{ old('fourth_prize_'.$i, $editData && $editData->fourth_prize ? substr($editData->fourth_prize, $i-1, 1) : '') }}"
                                            onkeyup="moveToNext(this, 'fourth_prize_{{$i+1}}', '{{$i>1 ? 'fourth_prize_'.($i-1) : ''}}')">
                                    @endfor
                                </div>
                            </div>

                            <div class="col-md-12 mt-3 d-grid d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi-check-lg me-1"></i> {{ $isEdit ? 'Update Result' : 'Save Result' }}
                                </button>
                            </div>
                        </div>
                    </form>
				 </div>
			</div>

            <!-- Results List -->
			<div class="card shadow-custom border-0">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="resultsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab" aria-controls="recent" aria-selected="true">
                                <i class="bi-clock-history me-1"></i> Recent Results
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                                <i class="bi-archive me-1"></i> Past Results
                            </button>
                        </li>
                    </ul>
                </div>
				<div class="card-body p-lg-4">

                    <div class="tab-content" id="resultsTabContent">
                        
                        <!-- Recent Results Tab -->
                        <div class="tab-pane fade show active" id="recent" role="tabpanel" aria-labelledby="recent-tab">
                            <div class="table-responsive p-0">
                                <table class="table table-hover">
                                 <tbody>
                        
                                   @if (count($recentResults) > 0)
                                      <tr>
                                         <th class="active">Fd</th>
                                         <th class="active">Date</th>
                                         <th class="active">Time</th>
                                         <th class="active">1st</th>
                                         <th class="active">2nd</th>
                                         <th class="active">2nd</th>
                                         <th class="active">2nd</th>
                                         <th class="active">{{ __('admin.actions') }}</th>
                                       </tr>
                        
                                     @foreach ($recentResults as $result)
                                       <tr>
                                         <td>{{ $result->fd }}</td>
                                         <td>{{ $result->result_date }}</td>
                                         <td>{{ $result->result_time }}</td>
                                         <td>
                                            @if($result->first_prize)
                                                @foreach(str_split($result->first_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->second_prize)
                                                @foreach(str_split($result->second_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->third_prize)
                                                @foreach(str_split($result->third_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->fourth_prize)
                                                @foreach(str_split($result->fourth_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            <a href="{{ route('admin.results', ['edit' => $result->id]) }}" class="btn btn-success rounded-pill btn-sm me-2">
                                                <i class="bi-pencil"></i>
                                            </a>
                        
                                          <form method="POST" action="{{ route('admin.results.delete', $result->id) }}" accept-charset="UTF-8" class="d-inline-block align-top">
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
                            </div>
                        </div>

                        <!-- Past Results Tab -->
                        <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                            <div class="table-responsive p-0">
                                <table class="table table-hover">
                                 <tbody>
                        
                                   @if ($pastResults->total() !=  0)
                                      <tr>
                                         <th class="active">Fd</th>
                                         <th class="active">Date</th>
                                         <th class="active">Time</th>
                                         <th class="active">1st</th>
                                         <th class="active">2nd</th>
                                         <th class="active">2nd</th>
                                         <th class="active">2nd</th>
                                         <th class="active">{{ __('admin.actions') }}</th>
                                       </tr>
                        
                                     @foreach ($pastResults as $result)
                                       <tr>
                                         <td>{{ $result->fd }}</td>
                                         <td>{{ $result->result_date }}</td>
                                         <td>{{ $result->result_time }}</td>
                                         <td>
                                            @if($result->first_prize)
                                                @foreach(str_split($result->first_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->second_prize)
                                                @foreach(str_split($result->second_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->third_prize)
                                                @foreach(str_split($result->third_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            @if($result->fourth_prize)
                                                @foreach(str_split($result->fourth_prize) as $digit)
                                                    <span class="result-digit">{{ $digit }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td>
                                            <a href="{{ route('admin.results', ['edit' => $result->id]) }}" class="btn btn-success rounded-pill btn-sm me-2">
                                                <i class="bi-pencil"></i>
                                            </a>
                        
                                          <form method="POST" action="{{ route('admin.results.delete', $result->id) }}" accept-charset="UTF-8" class="d-inline-block align-top">
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
                            </div>
                            
                            @if ($pastResults->lastPage() > 1)
                                <div class="mt-3">
                                    {{ $pastResults->onEachSide(0)->links() }}
                                </div>
                            @endif
                        </div>

                    </div>
                    
				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<script>
    function moveToNext(element, nextFieldName, prevFieldName) {
        if (element.value.length >= element.maxLength) {
            var next = document.getElementsByName(nextFieldName)[0];
            if (next) next.focus();
        }
        if (element.value.length === 0 && event.key === 'Backspace') {
             var prev = document.getElementsByName(prevFieldName)[0];
             if (prev) prev.focus();
        }
    }
</script>
@endsection
