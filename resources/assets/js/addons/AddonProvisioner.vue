<template>
  <div class="modal fade provisioner" id="provision-modal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Add-on Header -->
        <div class="modal-header">
          <div class="row w-100 text-center no-gutters">
            <div class="col">
              <img :src="addon.logo" width="60" />
              <h5 class="modal-title font-weight-normal mt-2">{{ addon.name }}</h5>
            </div>
          </div>
          <button
            style="position: absolute; right: 1rem;"
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <!-- Billing Information -->
        <div class="billing-info modal-body">
          <div class="d-flex w-100 justify-content-between no-gutters pt-3 pb-3">
            <div class="col-9">
              <h4 class="plan-name mb-0">{{ plan.name }} Plan</h4>
              <span class="billing-cycle text-muted">Billed {{ plan.interval }}</span>
            </div>
            <div class="col-3 plan text-right">
              <div class="price">{{ plan.price | currency }}</div>
              <div
                class="schedule text-muted"
              >/&nbsp;{{ plan.usageType == "licensed" ? plan.interval : plan.unit }}</div>
            </div>
          </div>
          <div v-if="spark.usesTeams && teams.length > 1" class="row">
            <div class="col-md-12 form-group">
              <b class="help-block">Team</b>
              <select v-model="team.id" @change="teamChanged(team)" class="form-control">
                <option value disabled>Please select</option>
                <option
                  v-for="team in teams"
                  v-bind:key="team.id"
                  v-bind:value="team.id"
                >{{ team.name }}</option>
              </select>
            </div>
          </div>
          <!-- Request Card -->
          <div v-if="!team.stripe_id">
            <!-- Card Number -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <b class="help-block">Card Number</b>
                  <input
                    type="text"
                    class="form-control"
                    name="number"
                    data-stripe="number"
                    v-model="cardForm.number"
                    placeholder="*****************"
                    required
                    :class="{'is-invalid': cardForm.errors.has('number')}"
                  />
                  <span
                    class="invalid-feedback"
                    v-show="cardForm.errors.has('number')"
                  >{{ cardForm.errors.get('number') }}</span>
                </div>
              </div>
            </div>
            <div class="row">
              <!-- Expiration -->
              <div class="col-6 form-group">
                <b class="help-block">Expiration</b>
                <div class="row">
                  <!-- Month -->
                  <div class="col-6 pr-1">
                    <input
                      type="text"
                      class="form-control"
                      name="month"
                      placeholder="MM"
                      maxlength="2"
                      data-stripe="exp-month"
                      v-model="cardForm.month"
                    />
                  </div>
                  <!-- Year -->
                  <div class="col-6 pl-1">
                    <input
                      type="text"
                      class="form-control"
                      name="year"
                      placeholder="YYYY"
                      maxlength="4"
                      data-stripe="exp-year"
                      v-model="cardForm.year"
                    />
                  </div>
                </div>
              </div>
              <!-- Security Code -->
              <div class="col-6 form-group">
                <b class="help-block">CVC</b>
                <input
                  type="text"
                  class="form-control"
                  name="cvc"
                  data-stripe="cvc"
                  placeholder="123"
                  v-model="cardForm.cvc"
                />
              </div>
            </div>
          </div>
        </div>
        <!-- Confirm Order -->
        <div class="modal-footer">
          <button
            @click.prevent="subscribe"
            type="button"
            class="btn btn-success w-100"
            :disabled="form.busy"
          >
            <span v-if="form.busy">
              <i class="fa fa-btn fa-spinner fa-spin"></i> Provisioning...
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
  props: ["user", "teams", "currentTeam"],
  /**
   * The component's data.
   */
  data: function() {
    return {
      addon: {},
      plan: {
        features: []
      },
      team: "",
      form: new SparkForm({
        stripe_token: null,
        plan: "",
        coupon: null
      }),
      cardForm: new SparkForm({
        number: "",
        cvc: "",
        month: "",
        year: ""
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
    if (Spark.usesTeams && this.currentTeam) {
      this.team = this.currentTeam;
      this.getTeam(this.team.id);
    }
    // Set the plan
    this.form.plan = this.plan.id;
    // Listen for a plan being selected
    Bus.$on("addonPlanInstall", function(addon, plan) {
      self.addon = addon;
      self.plan = plan;
      $("#provision-modal").modal("show");
    });
  },
  methods: {
    /**
     * The user has changed the selected team.
     *
     * @param team
     */
    teamChanged: function(team) {
      this.getTeam(team.id);
    },
    /**
     * Get the team
     *
     * @param slug
     */
    getTeam: function(id) {
      var team = this.findTeamById(id);
      axios
        .get(`/settings/${Spark.teamsPrefix}/json/${team.id}`)
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
      if (!this.team.card_last_four && !this.user.card_last_four) {
        const payload = {
          number: this.cardForm.number,
          cvc: this.cardForm.cvc,
          exp_month: this.cardForm.month,
          exp_year: this.cardForm.year
        };
        Stripe.card.createToken(payload, (status, response) => {
          if (response.error) {
            this.cardForm.errors.set({ number: [response.error.message] });
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
      if (Spark.usesTeams && !Spark.teamsIdentifiedByPath) {
        var url = `/${_.get(
          Spark,
          "spark-addons.routes.api",
          "api"
        )}/settings/${Spark.teamsPrefix}/${this.team.id}/addons/${
          this.addon.id
        }/subscribe`;
      } else if (Spark.usesTeams && Spark.teamsIdentifiedByPath) {
        var url = `/${_.get(Spark, "spark-addons.routes.api", "api")}/${
          this.team.slug
        }/addons/${this.addon.id}/subscribe`;
      } else {
        var url = `/${_.get(Spark, "spark-addons.routes.api", "api")}/addons/${
          this.addon.id
        }/subscribe`;
      }
      Spark.post(url, this.form).then(response => {
        $("#provision-modal").modal("hide");
        // Handle successful provision request
        this.provisionCallback(response);
      });
    },
    /**
     * Called after a successful provision request
     */
    provisionCallback: function(response) {
      swal(
        {
          title: "Add-on Created",
          text: "Your add-on will be up and running in a few minutes.",
          type: "success",
          confirmButtonText: "Open Dashboard",
          showConfirmButton: true
        },
        function() {
          window.location = "/home";
        }
      );
    },
    /**
     * Find a team based on the id
     *
     * @param id
     */
    findTeamById: function(id) {
      return _.find(this.teams, { id: id });
    }
  }
};
</script>
