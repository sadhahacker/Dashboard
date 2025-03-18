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
                <x-adminlte-datatable id="tradeTable" :heads="['ID', 'Symbol', 'Price', 'Quantity', 'Created At']" head-theme="dark"
                                      striped hoverable bordered compressed>
                </x-adminlte-datatable>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
   <script>
       document.addEventListener('DOMContentLoaded', function () {
           fetchData();
       });

       function fetchData() {
           axios.get("{{ url('api/exchange/mytrades?symbol=BNBUSDT') }}")
               .then(response => {
                   const data = response.data.data;
                   const tableBody = document.querySelector('#tradeTable tbody');

                   console.log(data)
                   // Clear existing data
                   tableBody.innerHTML = '';

                   // Populate table with new data
                   data.forEach(trade => {
                       const row = `<tr>
                                    <td>${trade.id}</td>
                                    <td>${trade.symbol}</td>
                                    <td>${trade.price}</td>
                                    <td>${trade.cost}</td>
                                    <td>${trade.datetime}</td>
                                </tr>`;
                       tableBody.innerHTML += row;
                       console.log(row)
                   });
               })
               .catch(error => {
                   console.error('Error fetching data:', error);
               });
       }
   </script>
@stop
