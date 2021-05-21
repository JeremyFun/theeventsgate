<template>
            <form @submit.prevent="sendEmail">
                <div class="mb-3" onclick="cons">
                    <label for="exampleFormControlInput1" class="form-label">{{ trans('em.fullName') }}</label>
                    <input type="text" class="form-control" id="exampleFormControlInput1"
                           :placeholder="trans('em.fullName')"
                           name="user_name" v-model="inputs.fullName" required>
                </div>
                <div class="mb-2">
                    <label for="exampleFormControlInput2" class="form-label">{{ trans('em.subject') }}</label>
                    <input type="text" class="form-control" id="exampleFormControlInput2"
                           :placeholder="trans('em.subject')"
                           name="subject" v-model="inputs.subject" required>
                </div>
                <div class="mb-2">
                    <label for="exampleFormControlInput2" class="form-label">{{ trans('em.message') }}</label>
                    <input type="text" class="form-control" id="exampleFormControlInput4"
                           :placeholder="trans('em.message')"
                           name="message" v-model="inputs.message" required>
                </div>
                <div class="mb-3">
                    <label for="exampleFormControlInput3" class="form-label">{{ trans('em.email') }}</label>
                    <input type="email" class="form-control" id="exampleFormControlInput3"
                           :placeholder="trans('em.email')"
                           name="user_email" v-model="inputs.email" required>
                </div>
                <button type="submit" class="btn btn-primary">{{trans('em.submit')}}</button>
            </form>

</template>


<script>
import emailjs from 'emailjs-com'


export default {
    props: ['events', 'currency'],
    data() {
        return {
            inputs: {
                fullName: "",
                subject: "",
                message: "",
                email: ""
            }
        };
    },

    methods: {
        // receives a place object via the autocomplete component
        nullForms() {
            this.inputs = {
                fullName: "",
                subject: "",
                message: "",
                email: ""
            }
        },
        sendEmail(e) {
            emailjs.sendForm('service_gbgeeaq', 'template_0y0vl9t', e.target, 'user_J2gXccQ1wBaOqUvfqdXKr')
                .then((result) => {
                    console.log('SUCCESS!', result.status, result.text);
                    Vue.helpers.showToast('success', trans('em.message_sent'));
                }, (error) => {
                    console.log('FAILED...', error);
                    Vue.helpers.showToast('error', trans('em.message_sent_fail'));
                });
            this.inputs = {
                fullName: "",
                subject: "",
                message: "",
                email: ""
            }
        }
    }
}
</script>
