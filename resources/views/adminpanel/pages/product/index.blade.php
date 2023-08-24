@extends('adminpanel.layout.website')
<!-- ================================== EXTEND TITLE AND META TAGS ============================= -->
@section('title-meta')
    <title>Inventory | Product List</title>
    <meta name="description" content="this is description">
@endsection
<!-- ====================================== EXTRA CSS LINKS ==================================== -->
@section('other-css')
    <link href="{{ asset('adminpanel') }}/css/plugins/dataTables/datatables.min.css" rel="stylesheet">
@endsection
<!-- ======================================== BODY CONTENT ====================================== -->
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2>{{config('app.name')}}</h2>
        <ol class="breadcrumb">
            <li class="active">
                <a href="{{route('products.index')}}">All Products</a>
            </li>

            @foreach ($categories as $category)
            <li>
                <a>{{$category->name}}</a>
            </li>
            @endforeach

        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">

        @foreach ($products as $product)

        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-content product-box">

                    <div class="product-imitation">
                        [ INFO ]
                    </div>
                    <div class="product-desc">
                        <span class="product-price">
                            Rs. {{$product->sale_price}}
                        </span>
                        <small class="text-muted">{{$product->category->name}}</small>
                        <a href="#" class="product-name"> {{$product->name}}</a>



                        <div class="small m-t-xs">
                            {{$product->note}}
                        </div>
                        <div class="m-t text-righ">

                            <a href="#" class="btn btn-xs btn-outline btn-primary">Info <i class="fa fa-long-arrow-right"></i> </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach







    </div>





</div>
@endsection
<!-- ======================================== FOOTER PAGE SCRIPT ======================================= -->
@section('other-script')

@endsection
