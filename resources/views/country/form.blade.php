@extends('panelio::layout.layout')

@section('body')
    <form method="post" action="{{ $action }}" class="form d-flex flex-column flex-lg-row" id="form">
        @csrf
        @if($mode === 'edit')
            @method('put')
        @endif
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <x-boolean-status value="{{ old('status', $country->status ?? true) }}" />
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab_general">
                    <div class="d-flex flex-column gap-7 gap-lg-10">

                        <!--begin::Information-->
                        <div class="card card-flush py-4 mb-10">
                            <div class="card-header">
                                <div class="card-title">
                                    <span class="fs-5 fw-bold">{{ trans('package-core::base.cards.proprietary_info') }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-10">
                                    <label class="form-label" for="input-name">{{ trans('location::base.form.location_country.fields.name.title') }}</label>
                                    <input type="text" name="name" id="input-name" class="form-control mb-2" placeholder="{{ trans('location::base.form.location_country.fields.name.placeholder') }}" value="{{ old('name', $country->name ?? null) }}">
                                    @error('name')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label" for="input-mobile-prefix">{{ trans('location::base.form.location_country.fields.mobile_prefix.title') }}</label>
                                    <input type="text" name="mobile_prefix" id="input-mobile-prefix" class="form-control mb-2" placeholder="{{ trans('location::base.form.location_country.fields.mobile_prefix.placeholder') }}" value="{{ old('mobile_prefix', $country->mobile_prefix ?? null) }}">
                                    @error('mobile_prefix')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label" for="input-validation">{{ trans('location::base.form.location_country.fields.validation.title') }}</label>
                                    <input type="text" name="validation" id="input-validation" class="form-control mb-2" placeholder="{{ trans('location::base.form.location_country.fields.validation.placeholder') }}" value="{{ old('validation', $country->validation ?? null) }}">
                                    @error('validation')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="input-flag">{{ trans('location::base.form.location_country.fields.flag.title') }}</label>
                                    <select name="flag" id="input-flag" class="form-select" data-control="select2" data-placeholder="{{ trans('location::base.form.location_country.fields.flag.placeholder') }}">
                                        <option></option>
                                        @foreach($flags as $flag)
                                            <option value="{{ $flag['value'] }}" data-url="assets/vendor/language/flags/{{ $flag['value'] }}" {{ old('flag', $country->flag ?? null) === $flag['value'] ? 'selected' : '' }}>{{ $flag['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('flag')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!--end::Information-->

                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
