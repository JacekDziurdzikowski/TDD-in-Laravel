<template>
    <section id="app" class="section">

        <h1 class="title is-1" v-text="form.formName"></h1>

        <div class="columns">
            <div class="column">
                <form id="form">

                    <div class="field">
                        <label class="label">Email</label>
                        <div class="control">
                            <input class="input" name="email" type="text" v-model="form.email" />
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Card number</label>
                        <div class="control">
                            <input class="input" name="card_no" type="text" v-model="form.cardNo" />
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Card cvc</label>
                        <div class="control">
                            <input class="input" name="card_cvc" type="text" v-model="form.cardCvc" />
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Card expiry date</label>
                        <div class="control">
                            <input class="input" name="card_exp_month" type="text" v-model="form.cardExpMonth" />
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Card expiry year</label>
                        <div class="control">
                            <input class="input" name="card_exp_year" type="text" v-model="form.cardExpYear" />
                        </div>
                    </div>


                    <div class="field">
                        <label class="label">How many tickets you want to buy?</label>
                        <div class="control">
                            <h4 class="title is-4">
                                {{form.ticketsQuantity}} / 80
                            </h4>
                            <input type="range" name="quantity" min="0" max="100" v-model="form.ticketsQuantity" />
                        </div>
                    </div>

                    <input class="button is-primary margin-bottom" type="submit" @click="submit" />
                </form>

                <transition name="fade" mode="out-in">
                    <article class="message is-primary" v-show="showSubmitFeedback">
                        <div class="message-header">
                            <p>Fake Send Status:</p>
                        </div>
                        <div class="message-body">
                            Successfully Submitted!
                        </div>
                    </article>
                </transition>
            </div>

            <div class="column">
                <h5>
                    JSON
                </h5>
                <pre><code>{{form}}</code></pre>
            </div>
        </div>

    </section>
</template>

<script>
export default {

    mounted() {
        console.log('Component mounted.')
    },

    data() {
        return {
            form: {
                formName: "Buy tickets",
                email: "",
                cardNo: "",
                cardCvc: "",
                cardExpMonth: "",
                cardExpYear: "",
                ticketsQuantity: 0
            },
            showSubmitFeedback: false,
            concertId: '1',
            apiUrl: 'http://localhost:8000',

        }
    },

    computed: {

        orderPath() {
            return '/concerts/'+this.concertId+'/orders'
        },
    },


    methods: {
        submit() {
            this.showSubmitFeedback = true;
            let formData = new FormData(document.getElementById("form"));
            window.axios.post(this.orderPath, formData)
                .then(response => {
                    alert('Bilety udało się zakupić');
                })
                .catch(error => {
                    alert(JSON.stringify(error));
                })
                .finally(() => {
                    this.showSubmitFeedback = false;
                })
        }
    }
}
</script>

<style>
    .margin-bottom {
        margin-bottom: 15px;
    }

    .fade-enter, .fade-leave-active {
        opacity: 0;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
</style>
