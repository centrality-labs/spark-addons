@extends('spark::layouts.app')
@section('scripts')
    @if (Spark::billsUsingStripe())
        <script src="https://js.stripe.com/v2/"></script>
    @else
        <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
    @endif
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>{{ $addon->name }}</h1>
                <h4>{{ $addon->description }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
                <addon-plans :addon='<?php echo json_encode($addon); ?>'></addon-plans>
            </div>
            <div class="col-md-5">
                <addon-plan-preview
                        :addon='<?php echo json_encode($addon); ?>'
                        :teams="teams"
                        :current-team="currentTeam">
                </addon-plan-preview>
            </div>
        </div>
    </div>
    <addon-provisioner :user="user"
                       :teams="teams"
                       :current-team="currentTeam">
    </addon-provisioner>
@endsection
