@extends('layouts.admin')
@section('body-title')
	@lang('equicare.hospitals')
@endsection
@section('title')
	| @lang('equicare.hospitals')
@endsection
@section('breadcrumb')
<li class="active">@lang('equicare.hospitals')</li>
@endsection

@section('content')
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
			<div class="box-header">
				<h4 class="box-title">@lang('equicare.manage_hospitals')
						@can('Create Hospitals')
							<a href="{{ route('hospitals.create') }}" class="btn btn-primary btn-flat">@lang('equicare.add_new')</a></h4>
						@endcan

				</div>

				<div class="box-body table-responsive">
					<table class="table table-striped table-bordered table-hover dataTable bottom-padding" id="data_table">
						<thead class="thead-inverse">
							<tr>
								<th class="text-center"> # </th>
								<th> @lang('equicare.name') </th>
								<th> @lang('equicare.email') </th>
								<!-- <th> @lang('equicare.user') </th> -->
								<th> @lang('equicare.slug') </th>
								<th> @lang('equicare.phone') </th>
								<th class="text-center"> No. @lang('equicare.equipment')</th>
								<!-- <th> @lang('equicare.mobile') </th> -->
								@if(Auth::user()->can('Edit Hospitals') || Auth::user()->can('Delete Hospitals'))
								<th> @lang('equicare.action')</th>
								@endif
							</tr>
						</thead>
						<tbody>
							@if (isset($hospitals))
							@php
								$count = 0;
							@endphp
							@foreach ($hospitals as $hospital)
							@php
								$count++;
							@endphp
							<tr>
							<td class="text-center"> {{ $count }} </td>
							<td> {{ ucfirst($hospital->name) }} </td>
							<td> {{  $hospital->email ?? '-' }}</td>
							<!-- <td> {{ $hospital->user ? ucfirst($hospital->user->name) : '-' }}</td> -->
							<td> {{ $hospital->slug ?? '-' }}</td>
							<td> {{ $hospital->phone_no ?? '-'}} </td>
							<td class="text-center text-bold"> {{ $hospital->equipments_count }} </td>
							<!-- <td> {{ $hospital->mobile_no ?? '-'}} </td> -->
							@if(Auth::user()->can('Edit Hospitals') || Auth::user()->can('Delete Hospitals'))
                        	<td>
								{!! Form::open(['url' => 'admin/hospitals/'.$hospital->id,'method'=>'DELETE','class'=>'form-inline']) !!}
									<a href="{{ route('hospitals.view', $hospital->id) }}" class="btn btn-primary btn-sm btn-flat" title="#"><i class="fa fa-eye"></i></a>
									@can('Edit Hospitals')
									<a href="{{ route('hospitals.edit',$hospital->id) }}" class="btn btn-warning btn-sm btn-flat" title="@lang('equicare.edit')"><i class="fa fa-edit"></i></a>
									@endcan
		                            <input type="hidden" name="id" value="{{ $hospital->id }}">
		                            @can('Delete Hospitals')
		                            <button class="btn btn-danger btn-sm btn-flat" type="submit" onclick="return confirm('@lang('equicare.are_you_sure')')" title="@lang('equicare.delete')"><span class="fa fa-trash-o" aria-hidden="true"></span></button>
		                            @endcan
		                        {!! Form::close() !!}
							</td>
							@endif
						</tr>
						@endforeach
						@endif
						</tbody>
						<tfoot>
							<tr>
								<th class="text-center"> # </th>
								<th> @lang('equicare.name') </th>
								<th> @lang('equicare.email') </th>
								<!-- <th> @lang('equicare.user') </th> -->
								<th> @lang('equicare.slug') </th>
								<th> @lang('equicare.phone') </th>
								<!-- <th> @lang('equicare.mobile') </th> -->
								<th class="text-center"> No. @lang('equicare.equipment')</th>
								@if(Auth::user()->can('Edit Hospitals') || Auth::user()->can('Delete Hospitals'))
								<th> @lang('equicare.action')</th>
								@endif
							</tr>
						</tfoot>
					</table>
				</div>

			</div>
		</div>
	</div>
@endsection