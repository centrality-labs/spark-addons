<template>
    <div class="modal fade provisioner" id="provision-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Add-on Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <div class="row text-center">
                        <div class="col-md-12">
                            <img :src="addon.logo" width="60">
                            <h4 class="modal-title font-weight-bold">{{ addon.name }}</h4>
                        </div>
                    </div>
                </div>
                <!-- Billing Information -->
                <div class="billing-info modal-body">
                    <div class="row">
                        <div class="col-xs-9">
                            <h4 class="plan-name">{{ plan.name }} Plan</h4>
                            <span class="billing-cycle text-muted">Billed {{ plan.interval }}</span>
                        </div>
                        <div class="col-xs-3 plan">
                            <div class="price">{{ plan.price | currency }}</div>
                            <div class="schedule">/&nbsp;{{ plan.interval == "monthly" ? "month" : "year" }}</div>
                        </div>
                    </div>
                    <div v-if="teams.length > 1" class="row">
                        <div class="col-md-12 form-group m-b-none">
                            <b class="help-block m-b-none">Team</b>
                            <select v-model="team.slug" @change="teamChanged(team)" class="form-control input-medium">
                                <option value="" disabled>Please select</option>
                                <option v-for="team in teams" v-bind:value="team.slug">{{ team.name }}</option>
                            </select>
                        </div>
                    </div>
                    <!-- Request Card -->
                    <div v-if="!team.card_last_four" class="row m-t-xs p-b-sm">
                        <div class="col-md-12">
                            <!-- Card Number -->
                            <div class="row">
                                <div class="col-xs-12 form-group m-b-xs" :class="{'has-error': cardForm.errors.has('number')}">
                                    <b class="help-block m-b-none">Card Number</b>
                                    <input type="text" class="form-control input-medium m-t-none m-b-none" name="number" data-stripe="number" v-model="cardForm.number" placeholder="*****************" required="">
                                    <span class="help-block" v-show="cardForm.errors.has('number')">{{ cardForm.errors.get('number') }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Expiration -->
                                <div class="col-xs-6 form-group m-b-xs">
                                    <b class="help-block m-b-none">Expiration</b>
                                    <div class="row">
                                        <!-- Month -->
                                        <div class="col-xs-6 p-r-xs">
                                            <input type="text" class="form-control input-medium m-t-none m-b-none" name="month"
                                                   placeholder="MM" maxlength="2" data-stripe="exp-month" v-model="cardForm.month">
                                        </div>
                                        <!-- Year -->
                                        <div class="col-xs-6 p-l-xs">
                                            <input type="text" class="form-control input-medium m-t-none m-b-none" name="year"
                                                   placeholder="YYYY" maxlength="4" data-stripe="exp-year" v-model="cardForm.year">
                                        </div>
                                    </div>
                                </div>
                                <!-- Security Code -->
                                <div class="col-xs-6 form-group m-b-xs">
                                    <b class="help-block m-b-none">CVC</b>
                                    <input type="text" class="form-control input-medium m-t-none m-b-none" name="cvc" data-stripe="cvc" v-model="cardForm.cvc">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Confirm Order -->
                <div class="modal-footer">
                    <button @click.prevent="subscribe" type="button" class="btn btn-success col-xs-12" :disabled="form.busy">
                        <span v-if="form.busy">
                            <i class="fa fa-btn fa-spinner fa-spin"></i>Provisioning...
                        </span>
                        <span v-else>Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['teams', 'currentTeam'],
        /**
         * The component's data.
         */
        data: function () {
            return {
                addon: {},
                plan: {
                    features: [],
                },
                team: "",
                form: new SparkForm({
                    stripe_token: null,
                    plan: '',
                    coupon: null,
                    team: "",
                    environment: "",
                }),
                cardForm: new SparkForm({
                    number: '',
                    cvc: '',
                    month: '',
                    year: '',
                })
            };
        },
        /**
         * Prepare the component.
         */
        mounted: function() {
            var self = this;
            Stripe.setPublishableKey(Spark.stripeKey);
            // Select the current team
            if(this.currentTeam) {
                this.team = this.currentTeam;
                this.getTeam(this.team.slug);
            }
            // Set the plan
            this.form.plan = this.plan.id;
            // Listen for a plan being selected
            Bus.$on('addonPlanInstall', function (addon, plan) {
                self.addon = addon;
                self.plan = plan;
                $("#provision-modal").modal('show');
            });
        },
        methods: {
            /**
             * The user has changed the selected team.
             *
             * @param team
             */
            teamChanged: function(team) {
                this.getTeam(team.slug);
            },
            /**
             * Get the team
             *
             * @param slug
             */
            getTeam: function(slug) {
                var team = this.findTeamBySlug(slug);
                axios.get(`/${Spark.pluralTeamString}/${team.id}`)
                        .then(response => {
                    this.team = response.data;
            });
            },
            /**
             * Subscribe to the specified add-on plan.
             */
            subscribe: function() {
                // Prepare the form
                this.form.plan = this.plan.id;
                this.form.startProcessing();
                this.cardForm.errors.forget();
                if(!this.team.card_last_four) {
                    const payload = {
                        number: this.cardForm.number,
                        cvc: this.cardForm.cvc,
                        exp_month: this.cardForm.month,
                        exp_year: this.cardForm.year,
                    };
                    Stripe.card.createToken(payload, (status, response) => {
                        if (response.error) {
                        this.cardForm.errors.set({number: [
                            response.error.message
                        ]});
                        this.form.busy = false;
                    } else {
                        this.form.stripe_token = response.id;
                        this.provision();
                    }
                });
                } else {
                    this.provision();
                }
            },
            /**
             * Create add-on subscription.
             */
            provision: function() {
                Spark.post(`/api/${this.team.slug}/addons/${this.addon.id}/subscribe`, this.form)
                        .then(function(response) {
                            $("#provision-modal").modal('hide');
                            swal({
                                title: 'Add-on Created',
                                text: 'Your add-on will be up and running in a few minutes.',
                                type: 'success',
                                showConfirmButton: false,
                            });
                            // Redirect to your new add-on
                        });
            },
            /**
             * Find a team based on the slug
             *
             * @param slug
             */
            findTeamBySlug: function(slug) {
                return _.find(this.teams, {slug: slug})
            }
        },
    }
</script>
