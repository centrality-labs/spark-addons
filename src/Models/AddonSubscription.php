<?php

namespace CentralityLabs\SparkAddons;

use Illuminate\Database\Eloquent\Model;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage;

class AddonSubscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'subscription_id',
        'owner_id',
        'owner_type',
        'addon_id',
        'addon_type',
        'ends_at'
    ];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['current_usage'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ends_at' => 'datetime',
    ];
    /**
     * Calculate the usage for a metered add on
     *
     * @return int
     */
    public function getCurrentUsageAttribute()
    {
        return Spark::call(CalculateMeteredUsage::class, [$this]);
    }
    /**
     * Get subscription this add-on belongs to.
     *
     * @return mixed
     */
    public function subscription()
    {
        if(Spark::canBillTeams()) {
            $subscriptionClass = config('spark-addons.subscriptionModels.team');
        } else {
            $subscriptionClass = config('spark-addons.subscriptionModels.user');
        }
        return $this->belongsTo($subscriptionClass);
    }
    /**
     * Scope a query to only include active add-ons.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ends_at')->orWhere('ends_at', '>', now()->toDateTimeString());
    }
    /**
     * Get the owner model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->morphTo('owner');
    }
    /**
     * Get the add-on model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addon()
    {
        return $this->morphTo('addon');
    }
    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }
    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return is_null($this->ends_at) || $this->onGracePeriod();
    }
    /**
     * Determine if the subscription is recurring and not on trial.
     *
     * @return bool
     */
    public function recurring()
    {
        return ! $this->onTrial() && ! $this->cancelled();
    }
    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->ends_at);
    }
    /**
     * Determine if the subscription has ended and the grace period has expired.
     *
     * @return bool
     */
    public function ended()
    {
        return $this->cancelled() && ! $this->onGracePeriod();
    }
    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }
}
