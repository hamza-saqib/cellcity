@extends('adminpanel.layout.master')
<!-- ================================== EXTEND TITLE AND META TAGS ============================= -->
@section('title-meta')
    <title>Inventory | Product Create</title>
    <meta name="description" content="this is description">
@endsection
<!-- ====================================== EXTRA CSS LINKS ==================================== -->
@section('other-css')
@endsection
<!-- ======================================== BODY CONTENT ====================================== -->
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Create Product</h2>
            <ol class="breadcrumb">
                <li>
                    <a href="{{ route('admin.home') }}">Home</a>
                </li>
                <li>
                    <a>Product</a>
                </li>
                <li class="active">
                    <strong>Create</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    {{-- @error('product_category_id')
        <div class="alert alert-danger" style="margin-top: 20px">Please Select Category</div>
    @enderror
    @error('name')
        <div class="alert alert-danger" style="margin-top: 20px">Please Enter Name</div>
    @enderror --}}


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">

            <div class="ibox-content">
                <form method="post" class="form-horizontal" action="{{ route('admin.product.store') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-group @error('code') has-error @enderror">

                        <label class="col-sm-2 control-label">Unique Code</label>

                        <div class="col-sm-4 ">
                            <input type="text" class="form-control" name="code" required value="{{old('code')}}">
                        </div>
                        @error('code')
                            <span class="help-block m-b-none">{{$message}}</span>
                        @enderror

                    </div>

                    <div class="form-group">

                        <label class="col-sm-2 control-label">Name</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="name" required value="{{old('name')}}">

                        </div>

                        <label class="col-sm-2 control-label">Images</label>

                        <div class="col-sm-4">
                            <input type="file" class="form-control" name="images[]" multiple="multiple">
                        </div>


                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Category</label>

                        <div class="col-sm-4 @error('product_category_id') has-error @enderror">
                            <select class="form-control" name="product_category_id" required>
                                <option selected disabled>Select</option>
                                @foreach ($categories as $category)

                                    <option value="{{ $category->id }}">{{ $category->name }} </option>
                                @endforeach

                            </select>
                            @error('product_category_id')
                                <span class="help-block m-b-none">Select Category</span>
                            @enderror
                        </div>

                        <label class="col-sm-2 control-label">Opening Qty</label>

                        <div class="col-sm-4">
                            <input type="number" min="0" class="form-control" name="opening_qty" value="{{old('opening_qty') ? old('opening_qty') : 0}}" value="0" required>
                        </div>


                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Model</label>

                        <div class="col-sm-4 @error('product_model_id') has-error @enderror">
                            <select class="form-control" name="product_model_id" required>
                                <option selected disabled>Select</option>
                                @foreach ($models as $model)

                                    <option value="{{ $model->id }}">{{ $model->name }} </option>
                                @endforeach

                            </select>
                            @error('product_model_id')
                                <span class="help-block m-b-none">Select Model</span>
                            @enderror
                        </div>


                    </div>


                    <div class="form-group">
                        <label class="col-sm-2 control-label">Cost Price</label>

                        <div class="col-sm-4">
                            <input type="number" min="0"  class="form-control" value="{{old('cost_price') ? old('cost_price') : 0 }}" name="cost_price" required>
                        </div>

                        <label class="col-sm-2 control-label">Sale Price</label>

                        <div class="col-sm-4">
                            <input type="number" min="0" class="form-control" value="{{old('sale_price') ? old('sale_price') : 0}}" name="sale_price" required>
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Brand</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" value="{{old('brand')}}" name="brand">
                        </div>

                        <label class="col-sm-2 control-label">Colors</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" value="{{old('colors')}}" name="colors">
                        </div>


                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Note</label>
                        <div class="col-sm-4">
                            <textarea name="description" id="" cols="50" rows="5">{{old('description')}}</textarea>
                        </div>

                    </div>


                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">Create Product</button>
                        </div>
                    </div>
                </form>
            </div>
            <br>

        </div>
    </div>

@endsection
<!-- ======================================== FOOTER PAGE SCRIPT ======================================= -->
@section('other-script')
    <script>
        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
    </script>

<script>
    var Success = `{{\Session::has('success')}}`;
    var Error = `{{\Session::has('error')}}`;

    if (Success) {
        setTimeout(function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 7000
            };
            toastr.success('Success Message', `{{\Session::get('success')}}`);

        }, 1200);
    }
    else if(Error){
        setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 4000
                };
                toastr.error('Failure Message', `{{\Session::get('error')}}`);

            }, 1200);
    }
</script>
@endsection
