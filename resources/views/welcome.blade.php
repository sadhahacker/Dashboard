@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="424" text="Views" icon="fas fa-eye text-dark"
                                  theme="teal" url="#" url-text="View details" id="balance"/>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="424" text="Views" icon="fas fa-eye text-dark"
                                  theme="teal" url="#" url-text="View details"/>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="424" text="Views" icon="fas fa-eye text-dark"
                                  theme="teal" url="#" url-text="View details"/>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="424" text="Views" icon="fas fa-eye text-dark"
                                  theme="teal" url="#" url-text="View details"/>
        </div>
        <!-- ./col -->
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script type="module">
        axios.get('api/exchange/balance')
            .then(response => {
                // Handle the response data
                console.log('Balance:', response.data);
                // You can update the UI or perform other actions with the data
            })
            .catch(error => {
                // Handle any errors
                console.error('Error fetching balance:', error);
                if (error.response) {
                    console.error('Server responded with:', error.response.data);
                } else if (error.request) {
                    console.error('No response received:', error.request);
                } else {
                    console.error('Error setting up the request:', error.message);
                }
            });
    </script>
    <script type="module">
        $(document).ready(function() {
            let sBox = new _AdminLTE_SmallBox('balance');

            const updateBox = (balanceData) => {
                if (!balanceData || !balanceData.length) {
                    console.error("Invalid balance data.");
                    return;
                }

                const balanceItem = balanceData[0]; // Assuming you're handling the first item
                const text = ` ${balanceItem.walletBalance}`;
                const title = `${balanceItem.asset}`;
                const icon = 'fas fa-coins text-dark';
                const url = '#'; // Optional, can link to a detailed balance page

                const data = { text, title, icon, url };

                // Stop loading animation
                sBox.toggleLoading();

                // Update the small box
                sBox.update(data);
            };

            Echo.channel('binance-balance')
                .listen('.balance.update', (e) => {
                    console.log(e)
                    sBox.toggleLoading(); // Start loading animation
                    setTimeout(() => {
                        updateBox(e.balance); // Update the box after receiving data
                    }, 500); // Simulate a brief delay to enhance UI experience
                });
        });

    </script>
@stop
