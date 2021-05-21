<template>
    <div class="row" v-if="payment_method == 2">
        <div class="col-md-12">
            <div class="d-block">
                <input type="text" id="name" class="form-control" :placeholder="trans('em.name')" name="name" v-model="name" v-validate="'required'">
            </div>

            <div id="card-element" class="d-block" ></div>
                
            <div id="card-errors" class="d-block text-danger" v-if="stripeError.length > 0">{{stripeError}}</div>
            
            <div class="d-block">
                <span v-show="errors.has('name')" class="help text-danger">{{ errors.first('name') }}</span>
            </div>  
        </div>
    </div>
</template>

<script>
import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';

// Or as a directive
Vue.directive('mask', VueMask.VueMaskDirective);

export default {
    
    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            // stripe object
            stripe : null,

            //elements
            card  : '',
            name  : '',

            // errors
            stripeError: '',
            
            // stripe keys
            stripePublishableKey: stripe_publishable_key,
            clientSecret        : stripe_secret_key,

            // validation
            valid: false,
        }

        
    },

    props: [
        'payment_method',
        'bookings_data',
    ],

    methods: {

        // ==================== Stripe ====================

        // create token
        createStripeToken() {
            
            let vm = this;

            // prepare form data for post request
            let post_url  = route('eventmie.bookings_book_tickets');

            // stripeSecretKey
            let clientSecret = this.clientSecret;

            // card holder name
            const cardHolderName = this.name;

            // card details
            let cardElement  = this.card;
            
            // check card valid or not then create token
            this.stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            ).then((result) => {
                if (result.error) {
    
                    vm.showNotification('error',  result.error.message);
                    // reload page   
                    setTimeout(function() {
                        location.reload(true);
                    }, 1500);

                } else {
                    vm.clearElementsInputs(); 
                    
                    var promise = new Promise(function(resolve, reject) { 
                        console.log(result.setupIntent);
                        vm.bookings_data.append('setupIntent', JSON.stringify(result.setupIntent));
                        resolve(true); 
                    });
                    
                     promise 
                    .then(function(successMessage) { 
                        
                        axios.post(post_url, vm.bookings_data)
                        .then(res => {
                            if(res.data.status && res.data.message != '' && typeof(res.data.message) != "undefined") {
                                vm.showNotification('success', res.data.message);
                                // close popup
                                vm.$parent.close();
                            }
                            else if(!res.data.status && res.data.message != '' && res.data.url != '' && typeof(res.data.url) != "undefined"){
                                
                                vm.showNotification('error', res.data.message);
                                // close popup
                                vm.$parent.close();
                                // reload page   
                                setTimeout(function() {
                                    location.reload(true);
                                }, 1500);
                            }

                            if(res.data.url != '' && res.data.status && typeof(res.data.url) != "undefined") {
                                setTimeout(() => {
                                    window.location.href = res.data.url;    
                                }, 1000);
                            }
                        })
                        .catch(error => {
                            vm.$parent.disable = false;
                            let serrors = Vue.helpers.axiosErrors(error);
                            if (serrors.length) {
                                vm.$parent.serverValidate(serrors);
                            }
                        });
                        
                    }, function(errorMessage) { 
                        console.log(errorMessage); 
                    });
                    
                
                }
            });

        }, 

         // validate data on form submit
        stripeCheckout(event) {
            this.listenForErrors();
            this.$validator.validateAll().then((result) => {
                if (result && this.valid) {
                    this.clearErrors();
                    this.createStripeToken();              
                }
                else{
                    
                    // hide loader
                    Swal.close();

                    // when user try submit from direct then set error 
                    if(this.stripeError.length <= 0 && !this.valid)
                        this.stripeError = "Invalid Card! All fields are required.";
                        

                    this.$parent.disable = false;
                }
            });
        }, 

        // setup stripe
        setUpStripe() {
            if (window.Stripe === undefined) {
                alert('Stripe V3 library not loaded!');
            } else {
                const stripe = window.Stripe(this.stripePublishableKey);
                this.stripe  = stripe;

                const elements = stripe.elements();
                this.card = elements.create('card', {
                        style: {
                            base: {
                                iconColor: '#666EE8',
                                color: '#31325F',
                                lineHeight: '40px',
                                fontWeight: 300,
                                fontFamily: 'Helvetica Neue',
                                fontSize: '15px',

                                '::placeholder': {
                                    color: '#CFD7E0',
                                },
                            },
                        }
                });

                this.card.mount('#card-element');
                
                //validations                
                this.listenForErrors();
            }
        },

        // immediate error messages
        listenForErrors() {
            const vm = this;

            this.card.on('change', function(event) {
                console.log(event);
                if (event.complete) {
                    // success when complete form
                    vm.valid = true;
                    
                } else if (event.error) {
                    //error 
                    vm.valid = false;
                    vm.stripeError = event.error.message;
                    
                } else {
                    // error clear but not complete form
                    vm.valid = false;
                    vm.stripeError = '';
                    
                }
            });

            
        },

        // clear inputs
        clearElementsInputs() {
            this.card.clear();
            
        },

        // clear inputs
        clearErrors() {
            this.stripeError = '';
        },
    
    },

     watch: {
        valid: function () {
            //clear error messages when card is completed
            if(this.valid)
                this.clearErrors();
        },
        
    },


    mounted() {
        this.setUpStripe();

    }   
}
</script>

<style>
#card-element {
    margin-top: 1rem;
    height: 50px;
    padding: 0.4rem 2rem;
    border-radius: 4px;
    border: 2px solid #e4e4e4;
    transition: all .15s ease-in-out;
}
</style>