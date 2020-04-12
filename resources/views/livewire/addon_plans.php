<div class="list-group price-list mb-2">
    @foreach($plans as $plan)
        <a href="#" wire class="list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
                <div class="col-9">
                    <h5 class="mb-1">{{ $plan.name }}</h5>
                    <p class="mb-1">{{ $plan.attributes.description }}</p>
                </div>
                <div class="col-3 plan align-self-center text-right">
                    <h5 class="price mb-0">{{ $plan.price }}</h5>
                    <small class="schedule">/&nbsp;{{ $plan.usageType == "licensed" ? $plan.interval : $plan.unit }}</small>
                </div>
            </div>
        </a>
    @endforeach
    @if($selectedPlan)
        @livewire('addon-plan' ['plan' => $selectedPlan])
    @endif
</div>