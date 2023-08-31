@extends('adminpanel.layout.master')
<!-- ================================== EXTEND TITLE AND META TAGS ============================= -->
@section('title-meta')
<title>{{config('app.name')}} | Dashboard</title>
<meta name="description" content="this is description">
@endsection
<!-- ====================================== EXTRA CSS LINKS ==================================== -->
@section('other-css')
<link href="{{asset('adminpanel')}}/css/plugins/dataTables/datatables.min.css" rel="stylesheet">
@endsection
<!-- ======================================== BODY CONTENT ====================================== -->
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2 >List of Product Invoices History</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li>
                <a>Product</a>
            </li>
            <li class="active">
                <strong> Show</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">

        <div class="ibox-content">
            <form method="post" class="form-horizontal" action="{{ route('admin.product.update', $product->id) }}"
                enctype="multipart/form-data">
                @csrf
                <div class="form-group">

                    <label class="col-sm-2 control-label">Name</label>

                    <div class="col-sm-4">
                        <input type="text" class="form-control" disabled name="name" required
                            value="{{ $product->name }}">

                    </div>

                    <label class="col-sm-2 control-label">Images</label>

                    <div class="col-sm-4">
                        <input type="file" class="form-control" disabled name="images[]" multiple="multiple">
                    </div>


                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Category</label>

                    <div class="col-sm-4">
                        <select class="form-control" disabled name="product_category_id" required>
                            <option selected disabled>Select</option>
                            @foreach ($categories as $category)
                                @if ($product->product_category_id == $category->id)
                                    <option selected value="{{ $category->id }}">{{ $category->name }} </option>
                                @endif
                                <option value="{{ $category->id }}">{{ $category->name }} </option>
                            @endforeach

                        </select>
                    </div>

                    <label class="col-sm-2 control-label">Opening Qty</label>

                    <div class="col-sm-4">
                        <input type="number" min="0" disabled class="form-control" name="opening_qty" required
                            value="{{ $product->opening_qty }}">
                    </div>


                </div>



                <div class="form-group">
                    <label class="col-sm-2 control-label">Cost Price</label>

                    <div class="col-sm-4">
                        <input type="number" min="0" disabled class="form-control" name="cost_price" required
                            value="{{ $product->cost_price }}">
                    </div>

                    <label class="col-sm-2 control-label">Sale Price</label>

                    <div class="col-sm-4">
                        <input type="number" min="0" disabled class="form-control" name="sale_price" required
                            value="{{ $product->sale_price }}">
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Brand</label>

                    <div class="col-sm-4">
                        <input type="text" disabled class="form-control" name="brand" value="{{ $product->brand }}">
                    </div>

                    <label class="col-sm-2 control-label">Colors</label>

                    <div class="col-sm-4">
                        <input type="text" disabled class="form-control" name="colors"
                            value="{{ $product->colors }}">
                    </div>


                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-4">
                        <textarea disabled name="description" id="" cols="50" rows="5">{{ $product->description }}</textarea>
                    </div>

                    <label class="col-sm-2 control-label">Remaining QTY</label>
                    <div class="col-sm-4">
                        <label class="col-sm-2 control-label">{{$product->available_qty}}</label>
                    </div>

                </div>




            </form>
        </div>

        <br>

    </div>

    <div class="row">


        <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>All the Packages are listed here..</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-wrench"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#">Config option 1</a>
                        </li>
                        <li><a href="#">Config option 2</a>
                        </li>
                    </ul>
                    <a class="close-link">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">

                <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example" >
            <thead>
            <tr>
                <th>No.</th>
                <th>Date</th>
                <th>Qty</th>
                <th>Sale/Purchase Price</th>
                <th>Amount</th>
                <th>Invoice</th>
                <th>Inv. Type</th>
                <th>Customer/Vendor</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @php
                $counter = 1;
            @endphp

            @foreach($invoices as $invoice)
            <tr class="gradeX" id="row-{{ $invoice->id }}">
                <td>{{ $counter }}</td>
                <td class="center">{{ date('d-m-Y', strtotime($invoice->issue_date)) }}
                </td>
                <td class="center">{{ $invoice->sale_quantity }}</td>
                @if ($invoice->type == 'Sale')
                    <td class="center">{{ $invoice->sale_price }}</td>
                @else
                    <td class="center">{{ $invoice->purchase_price }}</td>
                @endif

                @if ($invoice->type == 'Sale')
                    <td class="center">
                        {{ $invoice->sale_price * $invoice->sale_quantity }}</td>
                @else
                    <td class="center">
                        {{ $invoice->purchase_price * $invoice->sale_quantity }}</td>
                @endif

                <td class="center">{{ sprintf('%04d', $invoice->id) }}</td>
                <td class="center">{{ $invoice->type }}</td>

                @if ($invoice->type == 'Sale')
                    <td class="center">{{ $invoice->customer->name }}</td>
                @else
                    <td class="center">{{ $invoice->vendor->name }}</td>
                @endif
                <td class="center">{{ $invoice->createdBy->name }}</td>

                <td>
                    {{-- <a href="{{route('admin.purchase_invoice.show', $invoice->id)}}">
    <small class="label label-warning"><i class="fa"></i>View Invoice</small>
</a> --}}
                </td>
            </tr>

                @php
                    $counter = $counter + 1;
                @endphp
            @endforeach


            </tbody>
            <tfoot>
            <tr>
                <th>No.</th>
                <th>Date</th>
                <th>Qty</th>
                <th>Sale/Purchase Price</th>
                <th>Amount</th>
                <th>Invoice</th>
                <th>Inv. Type</th>
                <th>Customer/Vendor</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
            </tfoot>
            </table>
                </div>

            </div>
        </div>
    </div>
    </div>
</div>

@endsection
<!-- ======================================== FOOTER PAGE SCRIPT ======================================= -->
@section('other-script')
<!-- Page-Level Scripts -->

<script>
    $(document).ready(function(){
        $('.dataTables-example').DataTable({
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [

                {extend: 'print',
                    customize: function (win){
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '10px');

                        $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                }
                }
            ]

        });

        /* Init DataTables */
        var oTable = $('#editable').DataTable();

        /* Apply the jEditable handlers to the table */
        oTable.$('td').editable( '../example_ajax.php', {
            "callback": function( sValue, y ) {
                var aPos = oTable.fnGetPosition( this );
                oTable.fnUpdate( sValue, aPos[0], aPos[1] );
            },
            "submitdata": function ( value, settings ) {
                return {
                    "row_id": this.parentNode.getAttribute('id'),
                    "column": oTable.fnGetPosition( this )[2]
                };
            },

            "width": "90%",
            "height": "100%"
        } );


    });

    function fnClickAddRow() {
        $('#editable').dataTable().fnAddData( [
            "Custom row",
            "New row",
            "New row",
            "New row",
            "New row" ] );

    }
</script>
<script>
    function deleteSaleInvoice(id) {
    swal({

        title: "You really want to delete ？", // You really want to delete ?
        text: "Your will not be able to recover this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "No, Cancel",
        closeOnConfirm: false,
        closeOnCancel: false
        },
        function (isConfirm) {
        if (isConfirm) {
            $.ajax({
                method: 'GET',
                url: "{{ route('admin.sale_invoice.destroy', '') }}/" + id,
                success: function(response) {
                    console.log(response);
                    if(response.success){
                        swal("Deleted!", "News has been deleted.", "success");
                        $("#row-"+id).remove();
                    }
                    else if(response.error){
                        swal("Coudnt Found!", "News not Found", "error");
                    }
                    else{
                        swal("Error!", "Not Authorize | Logical Error", "error");
                    }
                },
                error: function (response){
                    console.log(response);
                    swal("Error!", "Cannot delete !", "error");
                }
            });
        }
        else {
            swal("Cancelled", "News is safe :)", "error");
        }

    });
    }
</script>


@endsection
