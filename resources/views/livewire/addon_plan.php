<div class="card card-light-border panel-price">
    <div class="card-header">
        <div class="media">
            <div class="media-body">
                <small class="text-muted font-weight-normal">{{ $addon.name }}</small>
                <h4 class="plan-name">{{ $plan.name }}</h4>
            </div>
            <div class="media-right">
                <img class="media-object" :src="addon.logo" :alt="addon.name" width="45">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p>{{ $plan.attributes.description }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <ul class="plan-features">
                    @foreach( $features as $feature)
                        <li v-for="feature in plan.features"><i class="fa fa-check"></i> {{ $feature }}</li>
                    @ednforeach
                </ul>
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-md-12">
                <div class="plan-price d-flex w-100 justify-content-between">
                    <h4>{{ $plan.price }}  <small>/ {{ $plan.usageType == "licensed" ? $plan.interval : $plan.unit }}</small></h4>
                    @if($plan.trail_days > 0)
                        <h5 class="pull-right"><small><b>Free {{ $plan.trialDays }} Day Trial</b></small></h5>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mt-3">
                <!-- <button v-if="spark.userId" @click="openProvisionerModal" class="btn btn-success btn-lg w-100"></button>
                <button v-else @click="openLogin" class="btn btn-success btn-lg w-100">Login to Install</button> -->
            </div>
        </div>
    </div>
</div>