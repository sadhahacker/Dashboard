@extends('adminlte::page')

@section('title', 'Trade')

@section('content_header')
    <h1>Trade</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div class="header-title">
                <h4 class="card-title">Bootstrap Datatables</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive iq-tabel-data">
                <table id="tradeTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        td:last-child {text-align:center;}
    </style>
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let table = $('#tradeTable').DataTable({
                ajax: {
                    url: "{{ url('api/exchange/mytrades?symbol=BNBUSDT') }}",
                    dataSrc: function(json) {
                        $('#custom-loading').parent().parent().remove(); // Remove loading row
                        return json.data.map(function(trade) {
                            return [
                                trade.id,
                                trade.symbol,
                                trade.price,
                                trade.datetime
                            ];
                        });
                    },
                    beforeSend: function() {
                        // Loading row is already present.
                    },
                    error: function(xhr, error, thrown) {
                        $('#custom-loading').html("Failed to load trades. Please try again later.");
                        $('.dataTables_processing').hide();
                    }
                },
                columns: [
                    { title: "ID" },
                    { title: "Symbol" },
                    { title: "Price" },
                    { title: "Datetime" }
                ],
                ordering: true,
                processing: false, // Disable default DataTable processing indicator
                serverSide: false, // Disable server-side processing
                paging: true, // Enable pagination
                searching: true, // Enable search
                info: true, // Enable info display
            });
        });
    </script>
@stop
